<?php

namespace App\Http\Controllers\Api\v1\HR;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\InvProduct;
use App\Models\Operation;
use App\Models\OperationType;
use App\Models\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboardData(Request $request)
    {
        $data = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $r = [];

        foreach (OperationType::all() as $operationType) {
            foreach (['pending', 'approved', 'rejected'] as $status) {
                $r["{$operationType->code}_{$status}"] = Operation::where('operation_type_id', $operationType->id)
                    ->where('status', $status)
                    ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                        $q->where('created_at', '>=', $fromDate);
                    })->when($data['to_date'] ?? null, function ($q, $toDate) {
                        $q->where('created_at', '<', $toDate);
                    })
                    ->count();
            }
        }

        foreach (['withdrawals', 'money_transfers'] as $table) {
            $r[$table] = DB::table($table)
                ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                })->when($data['to_date'] ?? null, function ($q, $toDate) {
                    $q->where('created_at', '<', $toDate);
                })->count();
        }

        foreach (['collab', 'partner'] as $userType) {
            foreach (['pending', 'enabled', 'disabled', 'rejected'] as $status) {
                $r["{$userType}_{$status}"] = User::role($userType)->where('status', $status)->count();
            }
        }

        foreach (
            [
                'card_categories',
                'cards',
                'inv_categories',
                'inv_products',
                'inv_supplies',
                'inv_orders',
                'inv_deliveries'
            ] as $table
        ) {
            $r[$table] = DB::table($table)->count();
        }

        $r['to_supply_products'] = InvProduct::whereColumn('stock_quantity', '<=', 'stock_quantity_min')->get();

        $r['histories'] = History::where('user_id', $request->user()->id)->latest()->limit(5)->get();
        $r['recent_partners'] = Partner::with('user')->latest()->limit(5)->get();

        return response()->json($r);
    }
}
