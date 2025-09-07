<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class InvCategoryController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = InvCategory::findOrFail($id);
        return response()->json($obj);
    }

    public function fetchAll(Request $request)
    {
        return response()->json(InvCategory::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:inv_categories',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.unique' => 'La valeur fournie pour ce champs existe deja',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = InvCategory::create([
                'code' => generateUniqueCode('inv_categories', 'code', 'CAT'),
                'name' => $request->name,
                'creator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Catégorie {$obj->name} {$obj->code} enregistrée.",
                'content' => "Vous avez enregistré une nouvelle catégorie {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Catégorie {$obj->name} {$obj->code} enregistrée."]);
    }

    public function update(Request $request, $id)
    {
        $obj = InvCategory::findOrFail($id);

        $reviewer = $request->user();

        $request->validate([
            'name' => "required|string|max:191|unique:inv_categories,name,{$obj->id}",
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.unique' => 'La valeur fournie pour ce champs existe deja',
        ]);

        DB::beginTransaction();

        try {
            $obj->update([
                'name' => $request->name,
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de la catégorie {$obj->name} {$obj->code}.",
                'content' => "Vous avez mis a jour les informations de la catégorie {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Catégorie {$obj->name} {$obj->code} mise a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = InvCategory::findOrFail($id);

        if ($obj->products()->exists()) {
            return response()->json(['message' => "Cette catégorie contient des produits"], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de la catégorie {$obj->name} {$obj->code}.",
                'content' => "Vous avez supprimé la catégorie {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Catégorie {$obj->name} {$obj->code} supprimée"]);
    }

    public function list(Request $request)
    {
        $params = new stdClass;

        $subQuery = DB::table('inv_categories')
            ->join('users AS creator_user', 'inv_categories.creator_id', 'creator_user.id')
            ->selectRaw('
                inv_categories.id,
                inv_categories.code,
                name,
                inv_categories.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
