<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Decoder;
use App\Models\DecoderOrder;
use App\Models\History;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class DecoderOrderController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $authUser = $request->user();

        $decoderOrder = DecoderOrder::with(['partner.user', 'partner.company', 'extra_client'])->findOrFail($id);

        if ($authUser->hasRole('partner-master')) {
            if ($decoderOrder->partner->company_id !== $authUser->company_id) {
                return response()->json(['message' => 'Non autorisé'], 405);
            }
        }

        return response()->json($decoderOrder);
    }

    public function store(Request $request)
    {
        $rules = [
            'client_type' => 'required|string|in:partner,extra_client',
            'type' => 'required|string|in:one,many,range',
            'decoder_number' => 'nullable|required_if:type,one|string|size:14',
            'decoder_numbers' => 'nullable|required_if:type,many|array',
            'decoder_numbers.*' => 'nullable|required_if:type,many|string|size:14',
            'decoder_number_from' => 'nullable|required_if:type,range|string|size:14',
            'decoder_number_to' => 'nullable|required_if:type,range|string|size:14',
        ];

        if ($request->client_type === 'partner') {
            $rules['partner_id'] = 'required|numeric|exists:partners,id';
        } else {
            $rules['extra_client_id'] = 'required|numeric|exists:extra_clients,id';
        }

        $messages = [
            '*.required' => 'Ce champs est requis',
            'decoder_numbers.required_if' => "Aucun décodeur n'a été fourni",
            '*.required_if' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            '*.size' => 'Ce champs doit avoir exactement 14 caractères',
        ];

        $data = $request->validate($rules, $messages);

        if ($data['type'] === 'one') {
            $decoderNumbers = [$data['decoder_number']];
        } else if ($data['type'] === 'many') {
            $decoderNumbers = array_unique($data['decoder_numbers']);
        } else if ($data['type'] === 'range') {
            $decoderNumberFrom = intval($data['decoder_number_from']);
            $decoderNumberTo = intval($data['decoder_number_to']);

            if ($decoderNumberFrom > $decoderNumberTo) {
                [$decoderNumberFrom, $decoderNumberTo] = [$decoderNumberTo, $decoderNumberFrom];
            }

            for (
                $decoderNumber = $decoderNumberFrom;
                $decoderNumber <= $decoderNumberTo;
                $decoderNumber++
            ) {
                $decoderNumbers[] = str_pad($decoderNumber, 14, '0', STR_PAD_LEFT);
            }
        }

        $existingDecoderNumbers = Decoder::whereIn('decoder_number', $decoderNumbers)->get();

        $diff = array_diff($decoderNumbers, $existingDecoderNumbers->pluck('decoder_number')->all());

        $diff = array_values($diff);

        if ($diff) {
            return response()->json(['message' => $diff[0] . " n'est pas un décodeur reconnu"], 405);
        }

        $decoder = $existingDecoderNumbers
            ->whereNotNull('decoder_order_id')
            ->first();

        if ($decoder) {
            return response()->json(['message' => $decoder->decoder_number . " a déjà été vendu"], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj = DecoderOrder::create([
                'code' => generateUniqueCode('decoder_orders', 'code', 'CMD'),
                'nbdecoders' => count($decoderNumbers),
                'partner_id' => $data['client_type'] === 'partner' ? $data['partner_id'] : null,
                'extra_client_id' => $data['client_type'] === 'extra_client' ? $data['extra_client_id'] : null,
                'creator_id' => $reviewer->id
            ]);

            Decoder::whereIn('decoder_number', $decoderNumbers)
                ->update(['decoder_order_id' => $obj->id]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Commande de décodeur {$obj->code} enregistrée.",
                'content' => "Vous avez enregistré une nouvelle commande de décodeur {$obj->code}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande de décodeur {$obj->code} enregistrée."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = DecoderOrder::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            Decoder::where('decoder_order_id', $obj->id)
                ->update(['decoder_order_id' => null]);

            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Suppression de la commande de décodeur {$obj->code}.",
                'content' => "Vous avez supprimé la commande de décodeur {$obj->code}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Commande de décodeur {$obj->code} supprimée"]);
    }

    public function list(Request $request)
    {
        $authUser = $request->user();

        $params = new stdClass;

        $subQuery = DB::table('decoder_orders')
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
                decoder_orders.id,
                decoder_orders.code,
                nbdecoders,
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
                decoder_orders.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function generateBill(Request $request, $id)
    {
        $obj = DecoderOrder::with('partner', 'extra_client', 'decoders')->findOrFail($id);

        $options = new Options();
        $options->set('defaultFont', 'sans-serif');

        $pdf = new Dompdf($options);

        $html = view('decoder-orders.generate-bill', [
            'obj' => $obj,

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
