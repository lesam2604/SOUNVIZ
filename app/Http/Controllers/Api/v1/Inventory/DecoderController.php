<?php

namespace App\Http\Controllers\Api\v1\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Decoder;
use App\Models\History;
use App\Models\OperationType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class DecoderController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $decoder = Decoder::with([
            'order.partner.user',
            'order.partner.company',
            'order.extra_client'
        ])->findOrFail($id);

        return response()->json($decoder);
    }

    public function fetchByDecoderNumber(Request $request, $decoderNumber)
    {
        $decoder = Decoder::firstWhere('decoder_number', $decoderNumber);
        return response()->json($decoder);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:one,many,range',
            'decoder_number' => 'nullable|required_if:type,one|string|size:14',
            'decoder_numbers' => 'nullable|required_if:type,many|array',
            'decoder_numbers.*' => 'nullable|required_if:type,many|string|size:14',
            'decoder_number_from' => 'nullable|required_if:type,range|string|size:14',
            'decoder_number_to' => 'nullable|required_if:type,range|string|size:14'
        ], [
            '*.required' => 'Ce champs est requis',
            'decoder_numbers.required_if' => "Aucun décodeur n'a été fourni",
            '*.required_if' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            '*.in' => "La valeur fournie pour ce champs n'est pas valide",
            '*.size' => 'Ce champs doit avoir exactement 14 caractères',
        ]);

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
                $decodeNumber = $decoderNumberFrom;
                $decodeNumber <= $decoderNumberTo;
                $decodeNumber++
            ) {
                $decoderNumbers[] = str_pad($decodeNumber, 14, '0', STR_PAD_LEFT);
            }
        }

        $existingDecoder = Decoder::whereIn('decoder_number', $decoderNumbers)->first();

        if ($existingDecoder) {
            return response()->json(['message' => $existingDecoder->decoder_number . ' existe deja'], 405);
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            // Insert decoders

            foreach ($decoderNumbers as $decoderNumber) {
                $decoders[] = [
                    'decoder_number' => $decoderNumber,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            Decoder::insert($decoders);

            $countDecoders = count($decoders);

            // Add reviewer's history

            History::create([
                'user_id' => $reviewer->id,
                'title' => "$countDecoders décodeur(s) ajouté(s).",
                'content' => "Vous avez ajouté $countDecoders nouveaux décodeurs."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => "$countDecoders décodeur(s) ajouté(s)."]);
    }

    public function update(Request $request, $id)
    {
        $decoder = Decoder::findOrFail($id);

        $reviewer = $request->user();

        $data = $request->validate([
            'decoder_number' => "required|string|size:14|unique:decoders,decoder_number,$decoder->id",
        ]);

        DB::beginTransaction();

        try {
            $decoder->update(['decoder_number' => $data['decoder_number']]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Décodeur {$decoder->decoder_number} mis à jour.",
                'content' => "Vous avez mis à jour le décodeur {$decoder->decoder_number}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => "Décodeur mis à jour."]);
    }

    public function destroy(Request $request, $id)
    {
        $obj = Decoder::findOrFail($id);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $obj->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Décodeur {$obj->decoder_number} supprimé.",
                'content' => "Vous avez supprimé le décodeur {$obj->decoder_number}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => "Décodeur {$obj->decoder_number} supprimé"]);
    }

    public function destroyRange(Request $request)
    {
        $data = $request->validate([
            'decoder_number_from' => 'required|string|size:14',
            'decoder_number_to' => 'required|string|size:14'
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.size' => 'Ce champs doit avoir exactement 14 caractères',
        ]);

        $decoderNumberFrom = intval($data['decoder_number_from']);
        $decoderNumberTo = intval($data['decoder_number_to']);

        if ($decoderNumberFrom > $decoderNumberTo) {
            [$decoderNumberFrom, $decoderNumberTo] = [$decoderNumberTo, $decoderNumberFrom];
        }

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            Decoder::query()
                ->whereBetween('decoder_number', [$decoderNumberFrom, $decoderNumberTo])
                ->delete();

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Décodeurs supprimés.",
                'content' => "Vous avez supprimé les décodeurs de {$decoderNumberFrom} à {$decoderNumberTo}."
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => 'Décodeurs supprimés']);
    }

    public function list(Request $request)
    {
        $request->validate([
            'decoder_order_id' => 'nullable|integer|exists:decoder_orders,id'
        ]);

        $authUser = $request->user();

        $params = new stdClass;

        $subQuery = DB::table('decoders')
            ->leftJoin('decoder_orders', 'decoder_order_id', 'decoder_orders.id')
            ->leftJoin('partners', 'partner_id', 'partners.id')
            ->when(
                $authUser->hasRole('reviewer'),
                function ($q) use ($request) {
                    if ($request->decoder_order_id) {
                        $q->where('decoder_order_id', $request->decoder_order_id);
                    }
                }
            )
            ->when(
                $authUser->hasRole('partner-master'),
                function ($q) use ($authUser, $request) {
                    $q->where('company_id', $authUser->company_id)
                        ->where('decoder_order_id', $request->decoder_order_id);
                }
            )
            ->selectRaw('
                decoders.id,
                decoder_number,
                IF(decoder_order_id IS NULL, "Non", "Oui") AS sold
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

        $opTypeId = OperationType::firstWhere('code', 'canal_activation')->id;

        $subQuery = DB::table('decoders')
            ->join('decoder_orders', 'decoder_order_id', 'decoder_orders.id')
            ->leftJoin('partners', 'decoder_orders.partner_id', 'partners.id')
            ->leftJoin('companies', 'partners.company_id', 'companies.id')
            ->leftJoin('users', 'user_id', 'users.id')
            ->leftJoin('operations', function ($j) use ($opTypeId) {
                $j->on('decoders.decoder_number', 'operations.decoder_number')
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
                    $q->whereNotNull('operations.decoder_number');
                } else {
                    $q->whereNull('operations.decoder_number');
                }
            })
            ->select(
                'decoders.id',
                'decoders.decoder_number',
                'decoder_order_id',
                'decoder_orders.code AS order',
                'decoder_orders.partner_id',
                'companies.name AS company',
                DB::raw("CONCAT(last_name, ' ', first_name) AS partner"),
                DB::raw("IF(operations.decoder_number IS NULL, 'Non', 'Oui') AS activated"),
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

        $opTypeId = OperationType::firstWhere('code', 'canal_activation')->id;

        $totals = DB::table('decoders')
            ->join('decoder_orders', 'decoder_order_id', 'decoder_orders.id')
            ->leftJoin('partners', 'decoder_orders.partner_id', 'partners.id')
            ->leftJoin('operations', function ($j) use ($opTypeId) {
                $j->on('decoders.decoder_number', 'operations.decoder_number')
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
                DB::raw("SUM(IF(operations.decoder_number IS NULL, 0, 1)) AS activated"),
                DB::raw("SUM(IF(operations.decoder_number IS NULL, 1, 0)) AS not_activated")
            )
            ->first();

        return response()->json($totals);
    }
}
