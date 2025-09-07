<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Operation;
use App\Models\Withdrawal;
use App\Services\StatementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class WithdrawalController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $obj = Withdrawal::with('partner')->findOrFail($id);

        $user = $request->user();

        if ($user->hasRole('partner') && $user->id !== $obj->partner->user_id) {
            return response()->json(['message' => 'Non autorisé'], 405);
        }

        return response()->json($obj);
    }

    public function sendOtpCode(Request $request)
    {
        if ($request->user()->amountToWithdraw() === 0) {
            return response()->json(['message' => 'Aucune commission a retirer pour le moment'], 500);
        }

        sendOtpCode($request->user()->email);

        return response()->json(['message' => "Un code a 6 chiffres a ete envoyé a votre adresse mail."]);
    }

    public function store(Request $request)
    {
        $partner = $request->user()->partner;

        $request->validate([
            'otp_code' => [
                'required',
                'integer',
                'digits:6',
                function ($attribute, $value, $fail) {
                    if (($feedback = compareOtpCode(Auth::user()->email, $value)) !== true) {
                        return $fail($feedback);
                    }
                }
            ]
        ]);

        $amountToWithdraw = $request->user()->amountToWithdraw();

        if ($amountToWithdraw === 0) {
            return response()->json(['message' => 'Aucune commission a retirer pour le moment'], 500);
        }

        DB::beginTransaction();

        try {
            $obj = Withdrawal::create([
                'partner_id' => $partner->id,
                'code'  => generateUniqueCode('withdrawals', 'code', 'RET'),
                'amount'  => $amountToWithdraw,
                'company_id' => $partner->company_id
            ]);

            app(StatementService::class)->createWithdrawalStatement($obj);

            $partner->update(['balance' => DB::raw("balance + {$amountToWithdraw}")]);

            Operation::query()
                ->where('company_id', $partner->company_id)
                ->where('withdrawn', false)
                ->update([
                    'withdrawn' => true,
                    'withdrawal_id' => $obj->id
                ]);

            History::create([
                'user_id' => $partner->user_id,
                'title' => "Retrait {$obj->code} effectue.",
                'content' => "Vous avez effectue un nouveau retrait {$obj->code}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Retrait {$obj->code} effectué."]);
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id'
        ]);

        $user = $request->user();

        $params = new stdClass;

        $subQuery = DB::table('withdrawals')
            ->join('partners', 'withdrawals.partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->when($user->hasRole('partner'), function ($q) {
                $q->where('partners.user_id', Auth::user()->id);
            })
            ->when($user->hasRole('reviewer') && isset($data['partner_id']), function ($q) use ($data) {
                $q->where('partners.id', $data['partner_id']);
            })
            ->selectRaw('
                withdrawals.id,
                withdrawals.code,
                amount,
                withdrawals.created_at,
                CONCAT(last_name, " ", first_name) AS partner,
                users.code AS partner_code
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
