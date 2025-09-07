<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\MoneyTransfer;
use App\Models\History;
use App\Models\Partner;
use App\Services\StatementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use stdClass;

class MoneyTransferController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = MoneyTransfer::with(['sender.user', 'recipient.user'])->findOrFail($id);

        $user = $request->user();

        if ($user->hasRole('partner') && !in_array($user->id, [$obj->sender->user_id, $obj->recipient->user_id])) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        return response()->json($obj);
    }

    public function store(Request $request)
    {
        $partner = $request->user()->partner;

        $data = $request->validate([
            'recipient_id' => [
                'required',
                'numeric',
                'exists:partners,id',
                function ($attribute, $value, $fail) use ($partner) {
                    if (
                        intval($value) === $partner->id ||
                        Partner::find($value)->user->hasRole('partner-pos')
                    ) {
                        $fail('Non autorisé');
                    }
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999999.99',
                function ($attribute, $value, $fail) use ($partner) {
                    if ($partner->balance < intval($value)) {
                        $fail('Votre solde est insuffisant pour cette transaction');
                    }
                },
            ]
        ], [
            '*.required' => 'Ce champs est requis',
            '*.numeric' => 'Ce champs doit être une valeur numérique',
            '*.exists' => "La valeur fournie pour ce champs n'est pas valide",
            'amount.gt' => 'Ce champs doit être une valeur positive valide',
        ]);

        DB::beginTransaction();

        try {
            $obj = MoneyTransfer::create([
                'code' => generateUniqueCode('money_transfers', 'code', 'TRA'),
                'sender_id' => $partner->id,
                'recipient_id' => $data['recipient_id'],
                'amount' => $data['amount']
            ]);

            app(StatementService::class)->createMoneyTransferStatement($obj);

            // Update balances
            $partner->update(['balance' => $partner->balance - $obj->amount]);

            Partner::where('id', $data['recipient_id'])->update([
                'balance' => DB::raw("balance + {$obj->amount}")
            ]);

            History::create([
                'user_id' => $partner->user_id,
                'title' => "Transfert {$obj->code} effectue.",
                'content' => "Vous avez effectue un nouveau transfert {$obj->code}."
            ]);

            Mail::to($obj->recipient->user->email)->send(new \App\Mail\MoneyTransferReceived($obj));

            DB::commit();

            return response()->json(['message' => "Transfert {$obj->code} effectue."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'sender_id' => 'nullable|numeric|exists:partners,id',
            'recipient_id' => 'nullable|numeric|exists:partners,id'
        ]);

        $params = new stdClass;

        $subQuery = DB::table('money_transfers')
            ->join('partners AS sender', 'money_transfers.sender_id', 'sender.id')
            ->join('users AS sender_user', 'sender.user_id', 'sender_user.id')
            ->join('partners AS recipient', 'money_transfers.recipient_id', 'recipient.id')
            ->join('users AS recipient_user', 'recipient.user_id', 'recipient_user.id')
            ->when($data['sender_id'] ?? null, function ($q, $senderId) {
                $q->where('sender_id', $senderId);
            })
            ->when($data['recipient_id'] ?? null, function ($q, $recipientId) {
                $q->where('recipient_id', $recipientId);
            })
            ->when($request->user()->hasRole('partner'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('sender.user_id', $request->user()->id)
                        ->orWhere('recipient.user_id', $request->user()->id);
                });
            })
            ->selectRaw('
                money_transfers.id,
                money_transfers.code,
                CONCAT(sender_user.first_name, " ", sender_user.last_name) AS sender_name,
                sender_user.code AS sender_code,
                CONCAT(recipient_user.first_name, " ", recipient_user.last_name) AS recipient_name,
                recipient_user.code AS recipient_code,
                amount
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
