<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class CommissionController extends Controller
{
    public function listPartners(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $authUser = $request->user();

        $params = new stdClass;

        $subQuery = DB::table('operations')
            ->join('operation_types', 'operation_type_id', 'operation_types.id')
            ->join('partners', 'partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->where('operations.status', 'approved')
            ->where('commission', '>', 0)
            ->whereNull('withdrawal_id')
            ->when($authUser->hasRole('partner-master'), function ($q) use ($authUser) {
                $q->where('operations.company_id', $authUser->company_id);
            })
            ->when($authUser->hasRole('reviewer') && isset($data['partner_id']), function ($q) use ($data) {
                $q->where('partners.id', $data['partner_id']);
            })
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('operations.created_at', '>=', $fromDate);
            })
            ->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('operations.created_at', '<', $toDate);
            })
            ->latest('operations.reviewed_at')
            ->selectRaw('
                operations.id,
                operations.code,
                operation_types.name,
                commission,
                withdrawn,
                operations.reviewed_at,
                CONCAT(last_name, " ", first_name) AS partner,
                users.code AS partner_code
            ');

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function totalPartners(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $authUser = $request->user();

        $totalCommissions = DB::table('operations')
            ->where('status', 'approved')
            ->where('commission', '>', 0)
            ->whereNull('withdrawal_id')
            ->when($authUser->hasRole('partner-master'), function ($q) use ($authUser) {
                $q->where('operations.company_id', $authUser->company_id);
            })
            ->when($authUser->hasRole('reviewer') && isset($data['partner_id']), function ($q) use ($data) {
                $q->where('partner_id', $data['partner_id']);
            })
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('created_at', '>=', $fromDate);
            })
            ->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('created_at', '<', $toDate);
            })
            ->latest('reviewed_at')
            ->sum('commission');

        return response()->json($totalCommissions);
    }

    public function listPlatform(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $params = new stdClass;

        $subQuery = DB::table('operations')
            ->join('operation_types', 'operation_type_id', 'operation_types.id')
            ->join('partners', 'partner_id', 'partners.id')
            ->join('users', 'user_id', 'users.id')
            ->where('operations.status', 'approved')
            ->when($data['partner_id'] ?? null, function ($q, $partner_id) {
                $q->where('partners.id', $partner_id);
            })
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('operations.created_at', '>=', $fromDate);
            })
            ->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('operations.created_at', '<', $toDate);
            })
            ->latest('operations.reviewed_at')
            ->selectRaw("
                operations.id,
                operations.code,
                operation_types.name,
                CAST(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(operations.data, '$.platform_commission')), ''), GREATEST(fee - commission, 0)) AS UNSIGNED) AS commission_platform,
                withdrawn,
                operations.reviewed_at,
                CONCAT(last_name, ' ', first_name) AS partner,
                users.code AS partner_code
            ")
            ->having('commission_platform', '>', 0);

        $params->builder = DB::query()->fromSub($subQuery, 'sub');

        return fetchListData($request, $params);
    }

    public function totalPlatform(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'nullable|numeric|exists:partners,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date'
        ]);

        if ($data['to_date'] ?? null) {
            $data['to_date'] = Carbon::parse($data['to_date'])->addDay()->format('Y-m-d');
        }

        $totalCommissions = DB::table('operations')
            ->where('status', 'approved')
            ->when($data['partner_id'] ?? null, function ($q, $partner_id) {
                $q->where('partner_id', $partner_id);
            })
            ->when($data['from_date'] ?? null, function ($q, $fromDate) {
                $q->where('created_at', '>=', $fromDate);
            })
            ->when($data['to_date'] ?? null, function ($q, $toDate) {
                $q->where('created_at', '<', $toDate);
            })
            ->sum(DB::raw("CAST(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(operations.data, '$.platform_commission')), ''), GREATEST(fee - commission, 0)) AS UNSIGNED)"));

        return response()->json($totalCommissions);
    }
}
