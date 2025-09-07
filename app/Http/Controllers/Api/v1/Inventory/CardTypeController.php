<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\CardType;
use App\Models\History;
use App\Models\OperationType;
use App\Models\OperationTypePartner;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class CardTypeController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = CardType::findOrFail($id);
        return response()->json($obj);
    }

    public function fetchAll(Request $request)
    {
        $cardTypes = CardType::orderBy('id')->pluck('name')->toArray();
        return response()->json($cardTypes);
    }

    public static function cacheCardTypes()
    {
        foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
            $opType = OperationType::firstWhere('code', $opTypeCode);
            $fields = $opType->fields;

            $fields->card_type->options = CardType::orderBy('id')->pluck('name')->toArray();

            $opType->fields = $fields;
            $opType->save();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:card_types',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.unique' => 'La valeur fournie pour ce champs existe déjà',
            'name.max' => 'La longueur maximale pour ce champs est de 191 caractères',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = CardType::create([
                'name' => $request->name,
                'creator_id' => $reviewer->id
            ]);

            static::cacheCardTypes();

            $users = User::role('partner-master')
                ->whereIn('status', ['enabled', 'disabled'])
                ->get();

            foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
                $opType = OperationType::firstWhere('code', $opTypeCode);

                foreach ($users as $masterUser) {
                    OperationTypePartner::create([
                        'operation_type_id' => $opType->id,
                        'card_type' => $obj->name,
                        'partner_id' => $masterUser->partner->id,
                        'has_commissions' => true,
                    ]);
                }

                $fees = $opType->fees;
                $commissions = $opType->commissions;

                $fees->{$obj->name} = [['breakpoint' => '', 'value' => '0']];
                $commissions->{$obj->name} = [['breakpoint' => '', 'value' => '0']];

                $opType->fees = $fees;
                $opType->commissions = $commissions;
                $opType->save();
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => 'Type de carte enregistré.',
                'content' => "Vous avez enregistré un nouveau type de carte \"{$obj->name}\"."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Type de carte enregistré.']);
    }

    public function update(Request $request, $id)
    {
        $obj = CardType::findOrFail($id);

        $request->validate([
            'name' => "required|string|max:191|unique:card_types,name,{$obj->id}",
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.unique' => 'La valeur fournie pour ce champs existe déjà',
            'name.max' => 'La longueur maximale pour ce champs est de 191 caractères',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $oldName = $obj->name;

            $obj->update([
                'name' => $request->name,
                'updator_id' => $reviewer->id
            ]);

            static::cacheCardTypes();

            OperationTypePartner::where('card_type', $oldName)
                ->update(['card_type' => $obj->name]);

            foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
                $opType = OperationType::firstWhere('code', $opTypeCode);

                $fees = $opType->fees;
                $commissions = $opType->commissions;

                $newFees = [];
                $newCommissions = [];

                foreach ($fees as $key => $value) {
                    $newKey = $key === $oldName ? $obj->name : $key;
                    $newFees[$newKey] = $value;
                    $newCommissions[$newKey] = $commissions->$key;
                }

                $opType->fees = $newFees;
                $opType->commissions = $newCommissions;
                $opType->save();
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour du type de carte \"{$obj->name}\".",
                'content' => "Vous avez mis a jour le type de carte \"{$obj->name}\"."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Type de carte \"{$obj->name}\" mis a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = CardType::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            static::cacheCardTypes();

            OperationTypePartner::where('card_type', $obj->name)->delete();

            foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
                $opType = OperationType::firstWhere('code', $opTypeCode);

                $fees = $opType->fees;
                $commissions = $opType->commissions;

                unset($fees->{$obj->name});
                unset($commissions->{$obj->name});

                $opType->fees = $fees;
                $opType->commissions = $commissions;
                $opType->save();
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de type de carte \"{$obj->name}\".",
                'content' => "Vous avez supprimé le type de carte \"{$obj->name}\"."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Type de carte \"{$obj->name}\" supprimé."]);
    }

    public function list(Request $request)
    {
        $params = new stdClass;

        $subQuery = DB::table('card_types');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
