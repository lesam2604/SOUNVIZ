<?php

namespace App\Services;

use App\Models\CollaboratorBalance;
use App\Models\History;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use App\Services\OperationService; // juste pour l'utiliser, pas redéclarer

class CollaboratorBalanceService
{
    protected $operationService;

    public function __construct()
    {
        // Injection du service d'opérations
        $this->operationService = new OperationService();
    }

    /**
     * Débite le solde d’un collaborateur ou lève une exception si insuffisant.
     * Retourne le nouveau solde (int).
     */
    public function debitOrFail(int $userId, int $amount, string $reason): int
    {
        return DB::transaction(function () use ($userId, $amount, $reason) {
            $bal = CollaboratorBalance::lockForUpdate()->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'FCFA', 'updated_by' => Auth::id() ?: $userId]
            );

            if ($amount <= 0) {
                return $bal->balance;
            }

            // Debug log before checking balance
            Log::info('collab_balance.check', [
                'user_id' => $userId,
                'balance' => (int) $bal->balance,
                'amount_requested' => (int) $amount,
                'reason' => $reason,
            ]);

            if ($bal->balance < $amount) {
                \Log::info("Solde insuffisant — UserID: {$userId}, Solde actuel: {$bal->balance}, Montant demandé: {$amount}");
                throw new RuntimeException('Solde collaborateur insuffisant pour valider cette opération.');
            }

            $bal->balance -= $amount;
            $bal->updated_by = Auth::id() ?: $userId;
            $bal->save();

            Log::info('collab_balance.debited', [
                'user_id' => $userId,
                'amount' => (int) $amount,
                'new_balance' => (int) $bal->balance,
            ]);

            History::create([
                'user_id'    => $userId,
                'title'      => 'Débit lors de la validation d’une opération',
                'content'    => "Montant: {$amount} — Motif: {$reason}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info("Débit effectué — UserID: {$userId}, Montant: {$amount}, Nouveau solde: {$bal->balance}");

            return (int) $bal->balance;
        });
    }

    /**
     * Créditer le solde du collaborateur (utile pour remboursement ou annulation)
     */
    public function credit(int $userId, int $amount, string $reason): int
    {
        return DB::transaction(function () use ($userId, $amount, $reason) {
            $bal = CollaboratorBalance::lockForUpdate()->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'FCFA', 'updated_by' => Auth::id() ?: $userId]
            );

            $bal->balance += $amount;
            $bal->updated_by = Auth::id() ?: $userId;
            $bal->save();

            History::create([
                'user_id'    => $userId,
                'title'      => 'Crédit du solde',
                'content'    => "Montant: {$amount} — Motif: {$reason}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info("Crédit effectué — UserID: {$userId}, Montant: {$amount}, Nouveau solde: {$bal->balance}");

            return (int) $bal->balance;
        });
    }

    /**
     * Récupère le solde actuel d’un collaborateur
     */
    public function getBalance(int $userId): float
    {
        $bal = CollaboratorBalance::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => 'FCFA', 'updated_by' => Auth::id() ?: $userId]
        );

        return (float) $bal->balance;
    }

    /**
     * Liste des opérations d’un collaborateur via OperationService
     */
    public function listOperations(int $userId, int $limit = 10)
    {
        // Utilisation du service OperationService existant
        return $this->operationService->listOperations($userId, $limit);
    }
}
