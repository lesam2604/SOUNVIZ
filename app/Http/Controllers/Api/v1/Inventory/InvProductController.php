<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class InvProductController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = InvProduct::with('category')->findOrFail($id);
        return response()->json($obj);
    }

    public function fetchAll(Request $request)
    {
        return response()->json(InvProduct::with('category')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'unit_price' => 'required|numeric|gt:0|max:9999999999999.99',
            'category_id' => 'required|numeric|exists:inv_categories,id',
            'stock_quantity' => 'required|integer|min:0|max:4294967295',
            'stock_quantity_min' => 'required|integer|min:0|max:4294967295',
            'picture' => 'nullable|image'
        ],  [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.integer' => 'Ce champs doit être un nombrer entier',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.image' => 'Ce champs doit être une image',
            '*.min' => 'Ce champs doit être un nombrer entier positif ou zero',
            'name.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            'unit_price.gt' => 'Ce champs doit être un nombrer entier strictement positif',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $data['picture'] = saveFile($data['picture'] ?? null, true);

            $obj = InvProduct::create([
                'code' => generateUniqueCode('inv_products', 'code', 'PRO'),
                'name' => $data['name'],
                'unit_price' => $data['unit_price'],
                'category_id' => $data['category_id'],
                'stock_quantity' => $data['stock_quantity'],
                'stock_quantity_min' => $data['stock_quantity_min'],
                'picture' => $data['picture'],
                'creator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Produit {$obj->name} {$obj->code} enregistre.",
                'content' => "Vous avez enregistre un nouveau produit {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            removeFile($data['picture'], true);

            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Produit {$obj->name} {$obj->code} enregistre."]);
    }

    public function update(Request $request, $id)
    {
        $obj = InvProduct::findOrFail($id);

        $reviewer = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'unit_price' => 'required|numeric|gt:0|max:9999999999999.99',
            'stock_quantity_min' => 'required|integer|min:0|max:4294967295',
            'picture' => 'nullable|image'
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.integer' => 'Ce champs doit être un nombrer entier',
            '*.image' => 'Ce champs doit être une image',
            '*.min' => 'Ce champs doit être un nombrer entier positif ou zero',
            'name.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            'unit_price.gt' => 'Ce champs doit être un nombrer entier strictement positif',
        ]);

        $oldPics = [];

        DB::beginTransaction();

        try {
            if (isset($data['picture'])) {
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

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour du produit {$obj->name} {$obj->code}.",
                'content' => "Vous avez mis a jour les informations du produit {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['picture'])) {
                removeFile($data['picture'], true);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }

        foreach ($oldPics as $oldPic) {
            removeFile($oldPic, true);
        }

        return response()->json(['message' => "Produit {$obj->name} {$obj->code} mis a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = InvProduct::findOrFail($id);

        if ($obj->orders()->exists()) {
            return response()->json(['message' => "Ce produit est deja sur des commandes"], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression du produit {$obj->name} {$obj->code}.",
                'content' => "Vous avez supprime le produit {$obj->name} {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Produit {$obj->name} {$obj->code} supprime"]);
    }

    public function list(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|numeric|exists:inv_categories,id',
        ]);

        $params = new stdClass;

        $subQuery = DB::table('inv_products')
            ->join('inv_categories', 'category_id', 'inv_categories.id')
            ->when($request->category_id, function ($q, $categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($request->to_supply, function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'stock_quantity_min');
            })
            ->selectRaw('
                inv_products.id,
                inv_products.code,
                inv_products.name,
                unit_price,
                category_id,
                inv_categories.name AS category_name,
                stock_quantity,
                stock_quantity_min,
                picture,
                inv_products.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
