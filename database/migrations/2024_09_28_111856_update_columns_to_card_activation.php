<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
            $opType = OperationType::firstWhere('code', $opTypeCode);
            $fields = $opType->fields;
            $fields->card_type->options = [
                'ECOBANK',
                'UBA',
                'Orabank',
                'BSIC',
                'Autres'
            ];
            $opType->fields = $fields;
            $opType->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
            $opType = OperationType::firstWhere('code', $opTypeCode);
            $fields = $opType->fields;
            $fields->card_type->options = [
                'ECOBANK',
                'UBA',
            ];
            $opType->fields = $fields;
            $opType->save();
        }
    }
};
