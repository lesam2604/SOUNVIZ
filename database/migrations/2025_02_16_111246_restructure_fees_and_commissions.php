<?php

use App\Models\CardType;
use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (OperationType::all() as $opType) {
            if ($opType->fees) {
                $fees = $opType->fees;
                $commissions = $opType->commissions;

                $cardTypes = in_array($opType->code, ['card_activation', 'card_deactivation', 'card_recharge'])
                    ? CardType::orderBy('id')->pluck('name')->toArray()
                    : [null];

                $newFees = [];
                $newCommissions = [];

                foreach ($cardTypes as $cardType) {
                    $newFees[$cardType] = $fees;
                    $newCommissions[$cardType] = $commissions;
                }

                $opType->fees = $newFees;
                $opType->commissions = $newCommissions;

                $opType->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (OperationType::all() as $opType) {
            if ($opType->fees) {
                $opType->fees = reset($opType->fees);
                $opType->commissions = reset($opType->commissions);
                $opType->save();
            }
        }
    }
};
