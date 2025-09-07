<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardOrder;
use App\Models\History;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class CardOrderController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $authUser = $request->user();

        $cardOrder = CardOrder::with(['partner.user', 'partner.company', 'extra_client'])->findOrFail($id);

        if ($authUser->hasRole('partner-master')) {
            if ($cardOrder->partner->company_id !== $authUser->company_id) {
                return response()->json(['message' => 'Non autorisé'], 405);
            }
        }

        return response()->json($cardOrder);
    }

    public function store(Request $request)
    {
        $rules = [
            'client_type' => 'required|string|in:partner,extra_client',
            'type' => 'required|string|in:one,many,range',
            'card_id' => 'nullable|required_if:type,one|string|size:10',
            'card_ids' => 'nullable|required_if:type,many|array',
            'card_ids.*' => 'nullable|required_if:type,many|string|size:10',
            'card_id_from' => 'nullable|required_if:type,range|string|size:10',
            'card_id_to' => 'nullable|required_if:type,range|string|size:10',
        ];

        if ($request->client_type === 'partner') {
            $rules['partner_id'] = 'required|numeric|exists:partners,id';
        } else {
            $rules['extra_client_id'] = 'required|numeric|exists:extra_clients,id';
        }

        $messages = [
            '*.required' => 'Ce champs est requis',
            'card_ids.required_if' => "Aucune carte n'a ete fournie",
            '*.required_if' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            '*.size' => 'Ce champs doit avoir exactement 10 caractères',
        ];

        $data = $request->validate($rules, $messages);

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

        $existingCardIds = Card::whereIn('card_id', $cardIds)->get();

        $diff = array_diff($cardIds, $existingCardIds->pluck('card_id')->all());

        $diff = array_values($diff);

        if ($diff) {
            return response()->json(['message' => $diff[0] . " n'est pas une carte reconnue"], 405);
        }

        $card = $existingCardIds
            ->whereNotNull('card_order_id')
            ->first();

        if ($card) {
            return response()->json(['message' => $card->card_id . " a deja ete vendu"], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = CardOrder::create([
                'code' => generateUniqueCode('card_orders', 'code', 'CMC'),
                'nbcards' => count($cardIds),
                'partner_id' => $data['client_type'] === 'partner' ? $data['partner_id'] : null,
                'extra_client_id' => $data['client_type'] === 'extra_client' ? $data['extra_client_id'] : null,
                'creator_id' => $reviewer->id
            ]);

            Card::whereIn('card_id', $cardIds)
                ->update(['card_order_id' => $obj->id]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Commande de carte {$obj->code} enregistrée.",
                'content' => "Vous avez enregistré une nouvelle commande de carte {$obj->code}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande de carte {$obj->code} enregistrée."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = CardOrder::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            Card::where('card_order_id', $obj->id)->update(['card_order_id' => null]);

            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de la commande de carte {$obj->code}.",
                'content' => "Vous avez supprime la commande de carte {$obj->code}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande de carte {$obj->code} supprimée"]);
    }

    public function list(Request $request)
    {
        $authUser = $request->user();

        $params = new stdClass;

        $subQuery = DB::table('card_orders')
            ->leftJoin('partners', 'partner_id', 'partners.id')
            ->leftJoin('users AS partner_user', 'user_id', 'partner_user.id')
            ->leftJoin('extra_clients', 'extra_client_id', 'extra_clients.id')
            ->when(
                $authUser->hasRole('reviewer'),
                function ($q) use ($request) {
                    if ($request->partner_id) {
                        $q->where('partners.id', $request->partner_id);
                    }
                }
            )
            ->when(
                $authUser->hasRole('partner-master'),
                function ($q) use ($authUser) {
                    $q->where('partners.company_id', $authUser->company_id);
                }
            )
            ->selectRaw('
                card_orders.id,
                card_orders.code,
                nbcards,
                partner_id,
                extra_client_id,
                IF(
                    extra_client_id IS NULL,
                    CONCAT(partner_user.last_name, " ", partner_user.first_name),
                    CONCAT(extra_clients.last_name, " ", extra_clients.first_name)
                ) AS client,
                IF(
                    extra_client_id IS NULL,
                    partner_user.code,
                    extra_clients.code
                ) AS client_code,
                card_orders.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function generateBill(Request $request, $id)
    {
        $obj = CardOrder::with('partner')->findOrFail($id);

        $categories = DB::table('cards')
            ->join('card_categories', 'card_category_id', 'card_categories.id')
            ->where('card_order_id', $obj->id)
            ->select('card_categories.*')
            ->distinct()
            ->get();

        foreach ($categories as $category) {
            $category->cards =  DB::table('cards')
                ->where([
                    'card_order_id' => $obj->id,
                    'card_category_id' => $category->id
                ])
                ->get();
        }

        $options = new Options();
        $options->set('defaultFont', 'sans-serif');

        $pdf = new Dompdf($options);

        $html = view('card-orders.generate-bill', [
            'obj' => $obj,
            'categories' => $categories,

            'full_name' => $obj->partner_id
                ? $obj->partner->user->full_name
                : $obj->extra_client->full_name,

            'phone_number' => $obj->partner_id
                ? $obj->partner->user->phone_number
                : $obj->extra_client->phone_number,

            'tin' => $obj->partner_id
                ? $obj->partner->company->tin
                : $obj->extra_client->tin
        ])->render();

        $pdf->loadHtml($html);

        $pdf->setPaper('A4');

        $pdf->render();

        $pdf->stream(date('Y_m_d_H_i_s') . '_bill.pdf');

        exit;
    }
}
