<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvSupply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class InvSupplyController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = InvSupply::with('product.category')->findOrFail($id);
        return response()->json($obj);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|numeric|exists:inv_products,id',
            'quantity' => 'required|integer|gt:0|max:4294967295',
        ],  [
            '*.required' => 'Ce champs est requis',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.integer' => 'Ce champs doit être un nombrer entier',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            'quantity.gt' => 'Ce champs doit être un nombrer entier strictement positif',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = InvSupply::create([
                'code' => generateUniqueCode('inv_supplies', 'code', 'APP'),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'creator_id' => $reviewer->id
            ]);

            $obj->product->update(['stock_quantity' => $obj->product->stock_quantity + $obj->quantity]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Approvisionnement {$obj->code} enregistre.",
                'content' => "Vous avez enregistre un nouvel approvisionnement {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Approvisionnement {$obj->code} enregistre."]);
    }

    public function update(Request $request, $id)
    {
        $obj = InvSupply::with('product')->findOrFail($id);

        $reviewer = $request->user();

        $request->validate([
            'quantity' => [
                'required',
                'integer',
                'gt:0',
                'max:4294967295',
                function ($attribute, $value, $fail) use ($obj) {
                    if ($obj->product->stock_quantity - $obj->quantity + intval($value) < 0) {
                        $fail('Le stock deviendra négatif');
                    }
                },
            ]
        ],  [
            '*.required' => 'Ce champs est requis',
            '*.integer' => 'Ce champs doit être un nombrer entier',
            'quantity.gt' => 'Ce champs doit être un nombrer entier strictement positif'
        ]);

        DB::beginTransaction();

        try {
            $oldQuantity = $obj->quantity;

            $obj->update([
                'quantity' => $request->quantity,
                'updator_id' => $reviewer->id
            ]);

            $obj->product->update([
                'stock_quantity' => $obj->product->stock_quantity - $oldQuantity + $obj->quantity,
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de l'approvisionnement {$obj->code}.",
                'content' => "Vous avez mis a jour les informations de l'approvisionnement {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Approvisionnement {$obj->code} mis a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = InvSupply::with('product')->findOrFail($id);

        if ($obj->product->stock_quantity - $obj->quantity < 0) {
            return response()->json(['message' => 'Le stock deviendra négatif'], 500);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            $obj->product->update([
                'stock_quantity' => $obj->product->stock_quantity - $obj->quantity,
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de l'approvisionnement {$obj->code}.",
                'content' => "Vous avez supprime l'approvisionnement {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Approvisionnement {$obj->code} supprime"]);
    }

    public function list(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|numeric|exists:inv_products,id',
        ]);

        $params = new stdClass;

        $subQuery = DB::table('inv_supplies')
            ->join('inv_products', 'product_id', 'inv_products.id')
            ->join('inv_categories', 'category_id', 'inv_categories.id')
            ->when($request->product_id, function ($q, $productId) {
                $q->where('product_id', $productId);
            })
            ->selectRaw('
                inv_supplies.id,
                inv_supplies.code,
                inv_products.code AS product_code,
                inv_products.name AS product_name,
                inv_categories.name AS category_name,
                quantity,
                inv_supplies.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
