<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvDelivery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class InvDeliveryController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = InvDelivery::with(['order.products', 'products.category'])->findOrFail($id);
        return response()->json($obj);
    }

    protected function getMergedListProducts($products)
    {
        $products = collect($products);

        $mergedProducts = collect();

        foreach ($products as $product) {
            if (($mPro = $mergedProducts->where('id', $product->id)->first())) {
                $mPro->quantity += intval($product->quantity);
            } else {
                $mergedProducts->push((object)[
                    'id' => $product->id,
                    'quantity' => intval($product->quantity)
                ]);
            }
        }

        return $mergedProducts;
    }

    protected function separateProducts($obj, $products)
    {
        $toAddProducts = collect();
        $toUpdateProducts = collect();
        $toRemoveProducts = collect();

        foreach ($products as $product) {
            if ($obj->products->where('id', $product->id)->isEmpty()) {
                $toAddProducts->push($product);
            } else {
                $toUpdateProducts->push($product);
            }
        }

        foreach ($obj->products as $product) {
            if ($products->where('id', $product->id)->isEmpty()) {
                $toRemoveProducts->push($product);
            }
        }

        return [$toAddProducts, $toUpdateProducts, $toRemoveProducts];
    }

    protected function addProduct($obj, $product)
    {
        if (!isset($product->quantity) || $product->quantity < 1 || $product->quantity > 4294967295) {
            throw new Exception("La quantité fournie n'est pas valide");
        }

        if (!($pro = $obj->order->products->firstWhere('id', $product->id))) {
            throw new Exception("Ce produit n'est pas sur la commande");
        }

        $deliveredQuantity = DB::table('inv_delivery_product')
            ->join('inv_deliveries', 'delivery_id', 'inv_deliveries.id')
            ->where([
                'order_id' => $obj->order_id,
                'product_id' => $pro->id
            ])
            ->sum('quantity');

        $deliveredQuantity = intval($deliveredQuantity);

        if ($deliveredQuantity === $pro->pivot->quantity) {
            throw new Exception("Ce produit a été entièrement livré pour cette commande");
        }

        if ($product->quantity > $pro->pivot->quantity - $deliveredQuantity) {
            throw new Exception("La quantité renseignée pour ce produit de cette commande est supérieure a la quantité restante a livrer");
        }

        if ($pro->stock_quantity < $product->quantity) {
            throw new Exception("Cette quantité n'est pas disponible en stock");
        }

        $obj->products()->attach($pro->id, [
            'quantity' => $product->quantity
        ]);

        $pro->stock_quantity -= $product->quantity;
        $pro->save();
    }

    protected function updateProduct($obj, $product, $reviewer)
    {
        if (!isset($product->quantity) || $product->quantity < 1 || $product->quantity > 4294967295) {
            throw new Exception("La quantité fournie n'est pas valide");
        }

        $pro = $obj->products->firstWhere('id', $product->id);

        $deliveredQuantity = DB::table('inv_delivery_product')
            ->join('inv_deliveries', 'delivery_id', 'inv_deliveries.id')
            ->where('delivery_id', '<>', $obj->id)
            ->where([
                'order_id' => $obj->order_id,
                'product_id' => $pro->id
            ])
            ->sum('quantity');

        $deliveredQuantity = intval($deliveredQuantity);

        $orderPro = $obj->order->products->firstWhere('id', $product->id);

        if ($product->quantity > $orderPro->pivot->quantity - $deliveredQuantity) {
            throw new Exception("La quantité renseignée pour ce produit de cette commande est supérieure a la quantité restante a livrer");
        }

        if ($pro->stock_quantity + $pro->pivot->quantity <= $product->quantity) {
            throw new Exception("Cette quantité n'est pas disponible en stock");
        }

        $obj->products()->updateExistingPivot($product->id, [
            'quantity' => $product->quantity
        ]);

        $pro->update([
            'stock_quantity' => $pro->stock_quantity + $pro->pivot->quantity - $product->quantity,
            'updator_id' => $reviewer->id
        ]);
    }

    protected function removeProduct($obj, $product)
    {
        $pro = $obj->products->firstWhere('id', $product->id);
        $pro->stock_quantity += $pro->pivot->quantity;
        $pro->save();

        $obj->products()->dettach($product->id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|numeric|exists:inv_orders,id',
            'products' => 'required|string',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = InvDelivery::create([
                'code' => generateUniqueCode('inv_deliveries', 'code', 'LIV'),
                'order_id' => $request->order_id,
                'creator_id' => $reviewer->id
            ]);

            $products = json_decode($request->products);

            if ($products === null) {
                throw new Exception('Liste de produits non valide');
            }

            $products = $this->getMergedListProducts($products);

            if ($products->isEmpty()) {
                throw new Exception('Veuillez ajouter des produits');
            }

            foreach ($products as $product) {
                $this->addProduct($obj, $product);
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Livraison {$obj->code} enregistrée.",
                'content' => "Vous avez enregistré une nouvelle livraison {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Livraison {$obj->code} enregistrée."]);
    }

    public function update(Request $request, $id)
    {
        $obj = InvDelivery::with('products')->findOrFail($id);

        $reviewer = $request->user();

        $request->validate([
            'products' => 'required|string',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
        ]);

        DB::beginTransaction();

        try {
            $obj->update([
                'updator_id' => $reviewer->id
            ]);

            $products = json_decode($request->products);

            if ($products === null) {
                throw new Exception('Liste de produits non valide');
            }

            [$toAddProducts, $toUpdateProducts, $toRemoveProducts] =
                $this->separateProducts($obj, $this->getMergedListProducts($products));

            foreach ($toAddProducts as $product) {
                $this->addProduct($obj, $product);
            }

            foreach ($toUpdateProducts as $product) {
                $this->updateProduct($obj, $product, $reviewer);
            }

            foreach ($toRemoveProducts as $product) {
                $this->removeProduct($obj, $product);
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de la livraison {$obj->code}.",
                'content' => "Vous avez mis a jour les informations de la livraison {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Livraison {$obj->code} mise a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = InvDelivery::with('products')->findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            foreach ($obj->products as $pro) {
                $pro->stock_quantity += $pro->pivot->quantity;
                $pro->save();
            }

            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de la livraison {$obj->code}.",
                'content' => "Vous avez supprime la livraison {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Livraison {$obj->code} supprimée"]);
    }

    public function list(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|numeric|exists:inv_orders,id',
        ]);

        $params = new stdClass;

        $subQuery = DB::table('inv_deliveries')
            ->join('inv_orders', 'order_id', 'inv_orders.id')
            ->when($request->order_id, function ($q, $orderId) {
                $q->where('order_id', $orderId);
            })
            ->selectRaw('
                inv_deliveries.id,
                inv_deliveries.code,
                client_first_name,
                client_last_name,
                inv_orders.code AS order_code,
                inv_deliveries.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
