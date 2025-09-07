<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvOrder;
use App\Models\InvProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class InvOrderController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = InvOrder::with('products.category')->findOrFail($id);
        return response()->json($obj);
    }

    public function fetchAll(Request $request)
    {
        return response()->json(InvOrder::with('products.category')->get());
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
                $toAddProducts[] = $product;
            } else {
                $toUpdateProducts[] = $product;
            }
        }

        foreach ($obj->products as $product) {
            if ($products->where('id', $product->id)->isEmpty()) {
                $toRemoveProducts[] = $product;
            }
        }

        return [$toAddProducts, $toUpdateProducts, $toRemoveProducts];
    }

    protected function addProduct($obj, $product)
    {
        if (!isset($product->id) || !($pro = InvProduct::find($product->id))) {
            throw new Exception("Ce produit n'existe pas");
        }

        if (!isset($product->quantity) || $product->quantity < 1 || $product->quantity > 4294967295) {
            throw new Exception("La quantité fournie n'est pas valide");
        }

        $unitPrice = isset($product->unit_price) && is_numeric($product->unit_price)
            ? $product->unit_price
            : $pro->unit_price;

        $obj->products()->attach($pro->id, [
            'quantity' => $product->quantity,
            'unit_price' => $unitPrice
        ]);
    }

    protected function updateProduct($obj, $product)
    {
        if (!isset($product->id) || !($pro = InvProduct::find($product->id))) {
            throw new Exception("Ce produit n'existe pas");
        }

        if (!isset($product->quantity) || $product->quantity < 1 || $product->quantity > 4294967295) {
            throw new Exception("La quantité fournie n'est pas valide");
        }

        $deliveredQuantity = DB::table('inv_delivery_product')
            ->join('inv_deliveries', 'delivery_id', 'inv_deliveries.id')
            ->where([
                ['order_id', $obj->id],
                ['product_id', $pro->id]
            ])
            ->sum('quantity');

        $deliveredQuantity = intval($deliveredQuantity);

        if ($deliveredQuantity > $product->quantity) {
            throw new Exception("La quantité déjà livrée pour ce produit est supérieure a sa nouvelle quantité commandée");
        }

        $payload = [
            'quantity' => $product->quantity
        ];
        if (isset($product->unit_price) && is_numeric($product->unit_price)) {
            $payload['unit_price'] = $product->unit_price;
        }

        $obj->products()->updateExistingPivot($product->id, $payload);
    }

    protected function removeProduct($obj, $product)
    {
        if (!isset($product->id) || !($pro = InvProduct::find($product->id))) {
            throw new Exception("Ce produit n'existe pas");
        }

        if ($obj->deliveries()->whereRelation('products', 'product_id', $product->id)->exists()) {
            throw new Exception("Ce produit a déjà fait l'objet de livraison");
        }

        $obj->products()->detach($product->id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_first_name' => 'required|string|max:191',
            'client_last_name' => 'required|string|max:191',
            'products' => 'required|string',

        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = InvOrder::create([
                'code' => generateUniqueCode('inv_orders', 'code', 'COM'),
                'client_first_name' => $request->client_first_name,
                'client_last_name' => $request->client_last_name,
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
                'title' => "Commande {$obj->code} enregistrée.",
                'content' => "Vous avez enregistré une nouvelle commande {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande {$obj->code} enregistrée."]);
    }

    public function update(Request $request, $id)
    {
        $obj = InvOrder::with('products')->findOrFail($id);

        $reviewer = $request->user();

        $request->validate([
            'client_first_name' => 'required|string|max:191',
            'client_last_name' => 'required|string|max:191',
            'products' => 'required|string',

        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
        ]);

        DB::beginTransaction();

        try {
            $obj->update([
                'client_first_name' =>  $request->client_first_name,
                'client_last_name' =>  $request->client_last_name,
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
                $this->updateProduct($obj, $product);
            }

            foreach ($toRemoveProducts as $product) {
                $this->removeProduct($obj, $product);
            }

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Mise a jour de la commande {$obj->code}.",
                'content' => "Vous avez mis a jour les informations de la commande {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande {$obj->code} mise a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = InvOrder::findOrfail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            if ($obj->deliveries()->exists()) {
                return response()->json(['message' => "Cette commande a deja des livraisons"], 405);
            }

            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de la commande {$obj->code}.",
                'content' => "Vous avez supprime la commande {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande {$obj->code} supprimée"]);
    }

    public function list(Request $request)
    {
        $params = new stdClass;

        $subQuery = DB::table('inv_orders')
            ->join('users AS creator_user', 'inv_orders.creator_id', 'creator_user.id')
            ->selectRaw('
                inv_orders.id,
                inv_orders.code,
                client_first_name,
                client_last_name,
                inv_orders.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    /**
     * Approuver/valider une commande (facture), même si non payée.
     * Body: is_paid=true|false
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'is_paid' => 'nullable|in:true,false,0,1'
        ]);

        $order = InvOrder::with('products')->findOrFail($id);

        // Recalculer total pour fiabilité
        $total = 0;
        foreach ($order->products as $p) {
            $total += ((float)$p->pivot->unit_price) * ((int)$p->pivot->quantity);
        }

        $order->status = 'approved';
        if ($request->has('is_paid')) {
            $val = $request->input('is_paid');
            $order->is_paid = in_array($val, ['true', '1', 1, true], true);
        }
        $order->total_amount = $total;
        $order->save();

        History::create([
            'user_id' => $request->user()->id,
            'title' => "Validation de la commande {$order->code}.",
            'content' => "La commande {$order->code} a été validée" . ($order->is_paid ? ' (payée).' : ' (non payée).'),
        ]);

        return response()->json(['message' => "Commande {$order->code} validée."]); 
    }
}
