<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaboratorBalance;
use App\Models\History;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollaboratorBalanceController extends Controller
{
    /**
     * Résout le user_id à partir de l'ID collaborateur
     */
    protected function resolveUserIdFromCollabId($collabId): ?int
    {
        $exists = DB::table('users')->where('id', $collabId)->exists();
        return $exists ? (int) $collabId : null;
    }

    /**
     * GET /admin/collabs/{collab}/balance
     */
    public function showByCollab($collabId)
    {
        $userId = $this->resolveUserIdFromCollabId($collabId);
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'Collaborateur / utilisateur introuvable.'], 404);
        }

        $actorId = Auth::id(); // peut être null en local
        $bal = CollaboratorBalance::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => 'XOF', 'updated_by' => $actorId ?? $userId]
        );

        return response()->json([
            'ok'        => true,
            'collab_id' => (int) $collabId,
            'user_id'   => (int) $userId,
            'balance'   => (int) $bal->balance,
            'currency'  => $bal->currency,
        ]);
    }

    /**
     * POST /admin/collabs/{collab}/balance/adjust
     * Body: amount, direction (credit|debit), reason
     */
    public function adjustByCollab(Request $request, $collabId)
    {
        $validated = $request->validate([
            'amount'    => ['required', 'numeric', 'min:1'],
            'direction' => ['required', 'in:credit,debit'],
            'reason'    => ['required', 'string', 'max:500'],
        ], [], [
            'amount'    => 'montant',
            'direction' => 'type d\'opération',
            'reason'    => 'motif',
        ]);

        $userId = $this->resolveUserIdFromCollabId($collabId);
        if (!$userId) {
            return response()->json(['ok' => false, 'message' => 'Collaborateur / utilisateur introuvable.'], 404);
        }

        $actor = Auth::user(); // peut être null en local
        $actorId = $actor->id ?? null;

        DB::beginTransaction();
        try {
            $bal = CollaboratorBalance::lockForUpdate()->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'XOF', 'updated_by' => $actorId ?? $userId]
            );

            $amount = (int) $validated['amount'];

            if ($validated['direction'] === 'debit') {
                if ($bal->balance < $amount) {
                    DB::rollBack();
                    return response()->json([
                        'ok' => false,
                        'message' => "Solde insuffisant pour débiter ce montant.",
                    ], 422);
                }
                $bal->balance -= $amount;
            } else {
                $bal->balance += $amount;
            }

            $bal->updated_by = $actorId ?? $userId;
            $bal->save();

            // Historique (admin) seulement si acteur connecté
            if ($actorId) {
                History::create([
                    'user_id'    => $actorId,
                    'title'      => ($validated['direction'] === 'credit' ? 'Crédit' : 'Débit') . " du solde collaborateur",
                    'content'    => "Collaborateur #{$collabId} (user_id: {$userId}) — Montant: {$amount} — Motif: {$validated['reason']}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Historique (collaborateur)
            History::create([
                'user_id'    => $userId,
                'title'      => ($validated['direction'] === 'credit' ? 'Votre solde a été crédité' : 'Votre solde a été débité'),
                'content'    => "Montant: {$amount} — Motif: {$validated['reason']}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Notification (collaborateur)
            Notification::create([
                'recipient_id' => $userId,
                'subject'      => ($validated['direction'] === 'credit' ? 'Crédit de solde' : 'Débit de solde'),
                'body'         => "Montant: {$amount}. Motif: {$validated['reason']}.",
                'icon_class'   => $validated['direction'] === 'credit' ? 'fas fa-plus-circle' : 'fas fa-minus-circle',
                'link'         => '/dashboard',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            return response()->json([
                'ok'       => true,
                'message'  => "Solde mis à jour avec succès.",
                'balance'  => (int) $bal->balance,
                'currency' => $bal->currency,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'ok' => false,
                'message' => "Erreur lors de la mise à jour du solde.",
            ], 500);
        }
    }
}
