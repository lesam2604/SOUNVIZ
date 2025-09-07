<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardCategory;
use App\Models\History;
use App\Models\OperationType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class CardController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $card = Card::with([
            'category',
            'order.partner.user',
            'order.partner.company',
            'order.extra_client'
        ])->findOrFail($id);

        return response()->json($card);
    }

    public function fetchByCardId(Request $request, $cardId)
    {
        $card = Card::with('category')->firstWhere('card_id', $cardId);
        return response()->json($card);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'card_category_id' => 'required|numeric|exists:card_categories,id',
            'type' => 'required|string|in:one,many,range',
            'card_id' => 'nullable|required_if:type,one|string|size:10',
            'card_ids' => 'nullable|required_if:type,many|array',
            'card_ids.*' => 'nullable|required_if:type,many|string|size:10',
            'card_id_from' => 'nullable|required_if:type,range|string|size:10',
            'card_id_to' => 'nullable|required_if:type,range|string|size:10'
        ], [
            '*.required' => 'Ce champs est requis',
            'card_ids.required_if' => "Aucune carte n'a ete fournie",
            '*.required_if' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            '*.size' => 'Ce champs doit avoir exactement 10 caractères',
        ]);

        if ($data['type'] === 'one') {
            $cardIds = [$data['card_id']];
        } else if ($data['type'] === 'many') {
            $cardIds = array_unique($data['card_ids']);
        } else if ($data['type'] === 'range') {
            $cardIdFrom = intval($data['card_id_from']);
            $cardIdTo = intval($data['card_id_to']);

            if ($cardIdFrom > $cardIdTo) {
                [$cardIdFrom, $cardIdTo] = [$cardIdTo, $cardIdFrom];
            }

            for ($cardId = $cardIdFrom; $cardId <= $cardIdTo; $cardId++) {
                $cardIds[] = str_pad($cardId, 10, '0', STR_PAD_LEFT);
            }
        }

        $existingCard = Card::whereIn('card_id', $cardIds)->first();

        if ($existingCard) {
            return response()->json(['message' => $existingCard->card_id . ' existe deja'], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            // Insert cards

            foreach ($cardIds as $cardId) {
                $cards[] = [
                    'card_id' => $cardId,
                    'card_category_id' => $data['card_category_id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            Card::insert($cards);

            $countCards = count($cards);

            // Update stock

            CardCategory::where('id', $data['card_category_id'])
                ->update(['stock_quantity' => DB::raw("stock_quantity + $countCards")]);

            // Add reviewer's history

            History::create([
                'user_id' => $reviewer->id,
                'title' => "$countCards carte(s) ajoutée(s).",
                'content' => "Vous avez ajoute $countCards nouvelles cartes."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => "$countCards carte(s) ajoutée(s)."]);
    }

    public function update(Request $request, $id)
    {
        $card = Card::findOrFail($id);

        $reviewer = $request->user();

        $data = $request->validate([
            'card_id' => "required|string|size:10|unique:cards,card_id,$card->id",
        ]);

        DB::beginTransaction();

        try {
            $card->update(['card_id' => $data['card_id']]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Carte {$card->card_id} mise a jour.",
                'content' => "Vous avez mis a jour la carte {$card->card_id}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => "Carte mise a jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = Card::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            // Update stock
            CardCategory::where('id', $obj->card_category_id)
                ->update(['stock_quantity' => DB::raw('stock_quantity - 1')]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Carte {$obj->card_id} supprimée.",
                'content' => "Vous avez supprimé la carte {$obj->card_id}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => "Carte {$obj->card_id} supprimée"]);
    }

    public function destroyRange(Request $request)
    {
        $data = $request->validate([
            'card_id_from' => 'required|string|size:10',
            'card_id_to' => 'required|string|size:10'
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.size' => 'Ce champs doit avoir exactement 10 caractères',
        ]);

        $cardIdFrom = intval($data['card_id_from']);
        $cardIdTo = intval($data['card_id_to']);

        if ($cardIdFrom > $cardIdTo) {
            [$cardIdFrom, $cardIdTo] = [$cardIdTo, $cardIdFrom];
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            Card::query()
                ->whereBetween('card_id', [$cardIdFrom, $cardIdTo])
                ->groupBy('card_category_id')
                ->select('card_category_id', DB::raw('COUNT(*) as total'))
                ->get()
                ->each(function ($card) {
                    CardCategory::query()
                        ->where('id', $card->card_category_id)
                        ->update(['stock_quantity' => DB::raw("stock_quantity - $card->total")]);
                });

            Card::query()
                ->whereBetween('card_id', [$cardIdFrom, $cardIdTo])
                ->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Cartes supprimées.",
                'content' => "Vous avez supprimé les cartes de {$cardIdFrom} à {$cardIdTo}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => 'Cartes supprimées']);
    }

    public function list(Request $request)
    {
        $request->validate([
            'card_order_id' => 'nullable|integer|exists:card_orders,id'
        ]);

        $authUser = $request->user();

        $params = new stdClass;

        $subQuery = DB::table('cards')
            ->leftJoin('card_categories', 'card_category_id', 'card_categories.id')
            ->leftJoin('card_orders', 'card_order_id', 'card_orders.id')
            ->leftJoin('partners', 'partner_id', 'partners.id')
            ->when(
                $authUser->hasRole('reviewer'),
                function ($q) use ($request) {
                    if ($request->card_order_id) {
                        $q->where('card_order_id', $request->card_order_id);
                    }
                }
            )
            ->when(
                $authUser->hasRole('partner-master'),
                function ($q) use ($authUser, $request) {
                    $q->where('company_id', $authUser->company_id)
                        ->where('card_order_id', $request->card_order_id);
                }
            )
            ->selectRaw('
                cards.id,
                card_id,
                card_categories.name AS category,
                IF(card_order_id IS NULL, "Non", "Oui") AS sold
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function listStock(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|numeric|exists:companies,id',
            'status' => 'nullable|string|in:activated,not_activated',
        ]);

        $authUser = $request->user();

        $params = new stdClass;

        $opTypeId = OperationType::firstWhere('code', 'card_activation')->id;

        $subQuery = DB::table('cards')
            ->join('card_orders', 'card_order_id', 'card_orders.id')
            ->leftJoin('card_categories', 'card_category_id', 'card_categories.id')
            ->leftJoin('partners', 'card_orders.partner_id', 'partners.id')
            ->leftJoin('companies', 'partners.company_id', 'companies.id')
            ->leftJoin('users', 'user_id', 'users.id')
            ->leftJoin('operations', function ($j) use ($opTypeId) {
                $j->on('cards.card_id', 'operations.card_id')
                    ->where('operation_type_id', $opTypeId)
                    ->where('operations.status', 'approved');
            })
            ->when(
                $authUser->hasRole('reviewer'),
                function ($q) use ($request) {
                    if ($request->company_id) {
                        $q->where('partners.company_id', $request->company_id);
                    }
                }
            )
            ->when(
                $authUser->hasRole('partner-master'),
                function ($q) use ($authUser) {
                    $q->where('partners.company_id', $authUser->company_id);
                }
            )
            ->when($request->status, function ($q, $status) {
                if ($status === 'activated') {
                    $q->whereNotNull('operations.card_id');
                } else {
                    $q->whereNull('operations.card_id');
                }
            })
            ->select(
                'cards.id',
                'cards.card_id',
                'card_category_id',
                'card_categories.name AS category',
                'card_order_id',
                'card_orders.code AS order',
                'card_orders.partner_id',
                'companies.name AS company',
                DB::raw("CONCAT(last_name, ' ', first_name) AS partner"),
                DB::raw("IF(operations.card_id IS NULL, 'Non', 'Oui') AS activated"),
                'operations.id AS operation_id',
                'operations.code AS operation_code',
            );

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function totalStock(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|numeric|exists:companies,id',
        ]);

        $authUser = $request->user();

        $opTypeId = OperationType::firstWhere('code', 'card_activation')->id;

        $totals = DB::table('cards')
            ->join('card_orders', 'card_order_id', 'card_orders.id')
            ->leftJoin('partners', 'card_orders.partner_id', 'partners.id')
            ->leftJoin('operations', function ($j) use ($opTypeId) {
                $j->on('cards.card_id', 'operations.card_id')
                    ->where('operation_type_id', $opTypeId)
                    ->where('operations.status', 'approved');
            })
            ->when(
                $authUser->hasRole('reviewer'),
                function ($q) use ($request) {
                    if ($request->company_id) {
                        $q->where('partners.company_id', $request->company_id);
                    }
                }
            )
            ->when(
                $authUser->hasRole('partner-master'),
                function ($q) use ($authUser) {
                    $q->where('partners.company_id', $authUser->company_id);
                }
            )
            ->select(
                DB::raw("COUNT(*) AS total"),
                DB::raw("SUM(IF(operations.card_id IS NULL, 0, 1)) AS activated"),
                DB::raw("SUM(IF(operations.card_id IS NULL, 1, 0)) AS not_activated")
            )
            ->first();

        return response()->json($totals);
    }
}
