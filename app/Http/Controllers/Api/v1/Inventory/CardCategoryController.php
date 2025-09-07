<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\CardCategory;
use App\Models\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class CardCategoryController extends Controller
{
    public function fetch(Request $request, $id)
    {
        return response()->json(CardCategory::findOrFail($id));
    }

    public function fetchAll(Request $request)
    {
        return response()->json(CardCategory::orderBy('name')->get());
    }

    public static function cacheCardCategories()
    {
        foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
            $opType = OperationType::firstWhere('code', $opTypeCode);
            $fields = $opType->fields;

            $fields->uba_type->options = CardCategory::orderBy('name')->pluck('name')->toArray();

            $opType->fields = $fields;
            $opType->save();
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:card_categories',
            'unit_price' => 'required|numeric|gt:0|max:9999999999999.99',
            'stock_quantity_min' => 'required|integer|min:0|max:4294967295',
            'picture' => 'nullable|image'
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.integer' => 'Ce champs doit être un nombre entier',
            '*.image' => 'Ce champs doit être une image',
            '*.unique' => 'La valeur fournie pour ce champs existe déjà',
            'name.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            'unit_price.gt' => 'Ce champs doit être un nombrer entier strictement positif',
            'stock_quantity_min.min' => 'Ce champs doit être un nombrer entier positif ou zéro',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            if ($data['picture'] ?? null) {
                $data['picture'] = saveFile($data['picture'], true);
            }

            $obj = CardCategory::create([
                'code' => generateUniqueCode('card_categories', 'code', 'CCT'),
                'name' => $data['name'],
                'unit_price' => $data['unit_price'],
                'stock_quantity_min' => $data['stock_quantity_min'],
                'picture' => $data['picture'] ?? null,
                'creator_id' => $reviewer->id,
            ]);

            static::cacheCardCategories();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Catégorie de carte {$obj->name} {$obj->code} enregistrée.",
                'content' => "Vous avez enregistré une nouvelle catégorie de carte {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            if ($data['picture'] ?? null) {
                removeFile($data['picture'], true);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Catégorie de carte {$obj->name} {$obj->code} enregistrée."]);
    }

    public function update(Request $request, $id)
    {
        $obj = CardCategory::findOrFail($id);

        $reviewer = $request->user();

        $data = $request->validate([
            'name' => "required|string|max:191|unique:card_categories,name,{$obj->id}",
            'unit_price' => 'required|numeric|gt:0|max:9999999999999.99',
            'stock_quantity_min' => 'required|integer|min:0|max:4294967295',
            'picture' => 'nullable|image'
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.integer' => 'Ce champs doit être un nombrer entier',
            '*.image' => 'Ce champs doit être une image',
            '*.unique' => 'La valeur fournie pour ce champs existe deja',
            'name.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            'unit_price.gt' => 'Ce champs doit être un nombrer entier strictement positif',
            'stock_quantity_min.min' => 'Ce champs doit être un nombrer entier positif ou zero',
        ]);

        $oldPics = [];

        DB::beginTransaction();

        try {
            if ($data['picture'] ?? null) {
                $data['picture'] = saveFile($data['picture'], true);
                $oldPics[] = $obj->picture;
            }

            $obj->update([
                'name' => $data['name'],
                'unit_price' => $data['unit_price'],
                'stock_quantity_min' => $data['stock_quantity_min'],
                'picture' => $data['picture'] ?? $obj->picture,
                'updator_id' => $reviewer->id
            ]);

            static::cacheCardCategories();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de la catégorie de carte {$obj->name} {$obj->code}.",
                'content' => "Vous avez mis a jour les informations de la catégorie de carte {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            if ($data['picture'] ?? null) {
                removeFile($data['picture'], true);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }

        foreach ($oldPics as $oldPic) {
            removeFile($oldPic, true);
        }

        return response()->json(['message' => "Catégorie de carte {$obj->name} {$obj->code} mise a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = CardCategory::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            static::cacheCardCategories();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de la catégorie de carte {$obj->name} {$obj->code}.",
                'content' => "Vous avez supprimé la catégorie de carte {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        removeFile($obj->picture, true);

        return response()->json(['message' => "Catégorie de carte {$obj->name} {$obj->code} supprimée"]);
    }

    public function list(Request $request)
    {
        $params = new stdClass;

        $subQuery = DB::table('card_categories')
            ->join('users AS creator_user', 'card_categories.creator_id', 'creator_user.id')
            ->when($request->to_supply ?? null, function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'stock_quantity_min');
            })
            ->selectRaw('
                card_categories.id,
                card_categories.code,
                name,
                unit_price,
                stock_quantity,
                stock_quantity_min,
                card_categories.created_at,
                card_categories.picture
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
