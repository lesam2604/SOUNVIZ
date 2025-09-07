<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    /**
     * Retourne les performances par collaborateur (cartes vendues et montant total)
     * Filters: from_date, to_date (YYYY-MM-DD)
     */
    public function collabSales(Request $request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date',
        ]);

        $from = $request->input('from_date');
        $to   = $request->input('to_date');

        // Agrège toutes les opérations approuvées, avec total spécifique selon le type
        $ops = Operation::query()
            ->from('operations')
            ->join('operation_types as ot', 'ot.id', '=', 'operations.operation_type_id')
            ->select([
                'operations.reviewer_id',
                DB::raw('COUNT(*) as ops_count'),
                DB::raw("SUM(CASE 
                    WHEN ot.code = 'account_recharge' THEN COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(operations.data,'$.trans_amount')) AS DECIMAL(18,2)),0)
                    WHEN ot.code = 'balance_withdrawal' THEN COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(operations.data,'$.amount')) AS DECIMAL(18,2)) * 1.02,0)
                    ELSE COALESCE(operations.amount,0)+COALESCE(operations.fee,0)
                END) as ops_amount")
            ])
            ->where('operations.status', 'approved')
            ->when($from, fn($q) => $q->whereDate('operations.reviewed_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('operations.reviewed_at', '<=', $to))
            ->groupBy('operations.reviewer_id')
            ->get()
            ->keyBy('reviewer_id');

        $collabs = User::role('collab')
            ->select('id','first_name','last_name','email')
            ->get()
            ->map(function ($u) use ($ops) {
                $m = $ops->get($u->id);
                return [
                    'id' => $u->id,
                    'name' => trim(($u->first_name ?? '').' '.($u->last_name ?? '')),
                    'email' => $u->email,
                    'ops_count' => (int) ($m->ops_count ?? 0),
                    'ops_amount' => (int) ($m->ops_amount ?? 0),
                ];
            });

        return response()->json([ 'data' => $collabs ]);
    }

    /**
     * Détail par collaborateur et par type d'opération
     */
    public function collabSalesByType(Request $request)
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date',
        ]);

        $from = $request->input('from_date');
        $to   = $request->input('to_date');

        $rows = Operation::query()
            ->from('operations')
            ->join('operation_types as ot', 'ot.id', '=', 'operations.operation_type_id')
            ->select([
                'operations.reviewer_id', 'ot.id as op_type_id', 'ot.code as op_type_code', 'ot.name as op_type_name',
                \DB::raw('COUNT(*) as ops_count'),
                \DB::raw("SUM(CASE 
                    WHEN ot.code = 'account_recharge' THEN COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(operations.data,'$.trans_amount')) AS DECIMAL(18,2)),0)
                    WHEN ot.code = 'balance_withdrawal' THEN COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(operations.data,'$.amount')) AS DECIMAL(18,2)) * 1.02,0)
                    ELSE COALESCE(operations.amount,0)+COALESCE(operations.fee,0)
                END) as ops_amount")
            ])
            ->where('operations.status', 'approved')
            ->when($from, fn($q) => $q->whereDate('operations.reviewed_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('operations.reviewed_at', '<=', $to))
            ->groupBy('operations.reviewer_id', 'ot.id', 'ot.code', 'ot.name')
            ->get();

        // enrichir avec infos collaborateur
        $userIds = $rows->pluck('reviewer_id')->unique()->filter()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $data = $rows->map(function ($r) use ($users) {
            $u = $users->get($r->reviewer_id);
            return [
                'reviewer_id'   => (int) $r->reviewer_id,
                'reviewer_name' => $u ? trim(($u->first_name ?? '').' '.($u->last_name ?? '')) : '',
                'reviewer_email'=> $u->email ?? '',
                'op_type_code'  => $r->op_type_code,
                'op_type_name'  => $r->op_type_name,
                'ops_count'     => (int) $r->ops_count,
                'ops_amount'     => (int) $r->ops_amount,
            ];
        });

        return response()->json([ 'data' => $data ]);
    }
}
