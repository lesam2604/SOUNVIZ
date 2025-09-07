<?php

// app/Http/Controllers/Collab/CollabOperationCancelController.php
namespace App\Http\Controllers\Collab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationCancelRequest;
use App\Models\Operation;
use App\Models\User;
use App\Models\Notification;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\Auth;

class CollabOperationCancelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'operation_id' => 'required|exists:operations,id',
            'reason'       => 'nullable|string|max:255'
        ]);

        $user = Auth::user();

        $cancelRequest = OperationCancelRequest::create([
            'operation_id' => $request->operation_id,
            'collab_id'    => $user->id,
            'status'       => 'pending',
            'amount'       => $request->amount ?? 0,
            'partner_id'   => $request->partner_id ?? null,
            'reason'       => $request->reason,
        ]);

        // Notifier les admins + défalquer chez le partenaire
        try {
            $operation = Operation::with('partner.user')->find($request->operation_id);
            if ($operation) {
                // Notifications à tous les admins
                $admins = User::role('admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'recipient_id' => $admin->id,
                        'subject'      => "Demande d'annulation {$operation->code}",
                        'body'         => "Un collaborateur #{$user->id} a demandé l'annulation de l'opération {$operation->code}.",
                        'icon_class'   => 'fas fa-exclamation-circle',
                        'link'         => url('/admin/operations-cancel'),
                    ]);
                }

                // Défalquer chez le partenaire (mise en réserve) — éviter les doublons
                $master = $operation->partner?->getMaster();
                $amount = (int) round((float)($operation->amount
                    ?? data_get($operation->data, 'amount')
                    ?? data_get($operation->data, 'trans_amount')
                    ?? 0));
                $alreadyHeld = BalanceTransaction::where([
                    'operation_id' => $operation->id,
                    'type' => 'hold_on_cancel_request',
                ])->exists();
                if ($master && $amount > 0 && !$alreadyHeld) {
                    $master->balance = (float) $master->balance - $amount;
                    $master->save();

                    BalanceTransaction::create([
                        'user_id'           => optional($master->user)->id,
                        'type'              => 'hold_on_cancel_request',
                        'amount'            => $amount,
                        'operation_id'      => $operation->id,
                        'cancel_request_id' => $cancelRequest->id,
                        'created_by'        => $user->id,
                        'description'       => "Mise en réserve suite à annulation {$operation->code}",
                        'meta'              => [
                            'partner_id' => $operation->partner_id,
                            'operation_code' => $operation->code,
                            'source' => 'collab.cancel.store',
                        ],
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'ok'      => true,
            'message' => 'Demande d’annulation envoyée avec succès.',
            'request' => $cancelRequest
        ]);
    }
}
