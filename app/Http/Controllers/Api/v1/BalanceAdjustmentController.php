<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceAdjustement\FetchBalanceAdjustmentRequest;
use App\Http\Requests\BalanceAdjustement\StoreBalanceAdjustmentRequest;
use App\Models\BalanceAdjustment;
use App\Models\History;
use App\Models\Partner;
use App\Services\StatementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class BalanceAdjustmentController extends Controller
{
    public function fetch(FetchBalanceAdjustmentRequest $request, $id)
    {
        return response()->json($request->obj);
    }

    public function store(StoreBalanceAdjustmentRequest $request)
    {
        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            $oldBalance = ($partner = Partner::with('user')->find($request->partner_id))->balance;

            $obj = BalanceAdjustment::create([
                'code' => generateUniqueCode('balance_adjustments', 'code', 'AJU'),
                'partner_id' => $request->partner_id,
                'old_balance' => $oldBalance,
                'balance' => $oldBalance - floatval($request->amount_to_withdraw),
                'reason' => $request->reason
            ]);

            app(StatementService::class)->createBalanceAdjustmentStatement($obj);

            $partner->update(['balance' => $obj->balance]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => "Ajustement du solde {$obj->code} du partenaire {$partner->user->code} {$partner->user->full_name}.",
                'content' => "Vous avez ajuste le solde {$obj->code} du partenaire {$partner->user->code} {$partner->user->full_name}."
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Ajustement de solde {$obj->code} effectué."]);
    }

    public function list(Request $request)
    {
        $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id'
        ]);

        $params = new stdClass;

        $subQuery = DB::table('balance_adjustments')
            ->join('partners', 'balance_adjustments.partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->when($request->user()->hasRole('partner'), function ($q) use ($request) {
                $q->where('partners.user_id', $request->user()->id);
            })
            ->when(
                $request->user()->hasRole('reviewer') && $request->partner_id,
                function ($q) use ($request) {
                    $q->where('partners.id', $request->partner_id);
                }
            )
            ->selectRaw('
                balance_adjustments.id,
                balance_adjustments.code,
                old_balance,
                balance_adjustments.balance,
                old_balance - balance_adjustments.balance AS amount_to_withdraw,
                reason,
                CONCAT(last_name, " ", first_name) AS partner,
                users.code AS partner_code,
                balance_adjustments.created_at
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }
}
