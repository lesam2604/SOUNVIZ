<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationCancellationRequest;
use App\Models\CollaboratorBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationCancelController extends Controller
{
    public function index()
    {
        $requests = OperationCancellationRequest::with([
                'requester',
                'operation.partner.user',
                'operation.operationType',
            ])
            ->orderBy('created_at','desc')
            ->get();

        return view('admin.operations-cancel.index', compact('requests'));
    }

    public function list()
    {
        $requests = OperationCancellationRequest::with([
                'requester',
                'operation.partner.user',
                'operation.operationType',
            ])
            ->orderBy('created_at','desc')
            ->get();

        return response()->json(['ok'=>true,'requests'=>$requests]);
    }

    public function approve($id)
    {
        $req = OperationCancellationRequest::with('operation.partner.user')->findOrFail($id);

        if ($req->status !== 'pending') {
            return response()->json(['ok'=>false,'message'=>'Demande déjà traitée.']);
        }

        DB::transaction(function () use ($req) {
            $operation = $req->operation;
            // Montant (tous types)
            $amount    = (float) ($operation->amount
                ?? data_get($operation->data, 'amount')
                ?? data_get($operation->data, 'trans_amount')
                ?? 0);

            $collabBal = CollaboratorBalance::firstOrCreate(
                ['user_id' => $req->requested_by],
                ['balance' => 0, 'currency' => 'FCFA', 'updated_by' => Auth::id()]
            );
            $collabBal->balance += $amount;
            $collabBal->updated_by = Auth::id();
            $collabBal->save();

            $operation->status = 'pending';
            // Nettoyer les marqueurs JSON de demande
            $data = (array) ($operation->data ?? []);
            unset($data['cancel_requested_at'], $data['cancel_requested_by'], $data['cancel_reason']);
            $operation->data = (object) $data;
            $operation->save();

            $req->status      = 'approved';
            $req->approved_by = Auth::id();
            $req->approved_at = now();
            $req->save();
        });

        return response()->json(['ok'=>true,'message'=>'Demande approuvée.']);
    }

    public function reject($id)
    {
        $req = OperationCancellationRequest::with('operation.partner.user')->findOrFail($id);

        if ($req->status !== 'pending') {
            return response()->json(['ok'=>false,'message'=>'Demande déjà traitée.']);
        }

        DB::transaction(function () use ($req) {
            $operation = $req->operation;
            $amount    = (int) round((float) ($operation->amount
                ?? data_get($operation->data, 'amount')
                ?? data_get($operation->data, 'trans_amount')
                ?? 0));

            // Retour des fonds au partenaire (levée de la réserve)
            $master = optional($operation->partner)->getMaster();
            if ($master && $amount > 0) {
                $master->balance = (float) $master->balance + $amount;
                $master->save();
            }

            // Nettoyer les marqueurs JSON de demande
            $data = (array) ($operation->data ?? []);
            unset($data['cancel_requested_at'], $data['cancel_requested_by'], $data['cancel_reason']);
            $operation->data = (object) $data;
            $operation->save();

            $req->status      = 'rejected';
            $req->approved_by = Auth::id();
            $req->approved_at = now();
            $req->save();
        });

        return response()->json(['ok'=>true,'message'=>'Demande rejetée.']);
    }
}
