<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $opType = OperationType::firstWhere('code', 'canal_activation');
        $fields = $opType->fields;
        $fields->amount = [
            "type" => "number",
            "label" => "Montant",
            "attributes" => [],
            "options" => null,
            "required" => true,
            "unique" => false,
            "is_amount" => true,
            "stored" => true,
            "updated" => true,
            "listed" => true,
            "lte_today" => false,
            "position" => 7
        ];
        $opType->fields = $fields;
        $opType->name = 'Activation décodeur';
        $opType->amount_field = 'amount';
        $opType->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $opType = OperationType::firstWhere('code', 'canal_activation');
        $fields = $opType->fields;
        unset($fields->amount);
        $opType->fields = $fields;
        $opType->name = 'Activation CANAL+';
        $opType->amount_field = null;
        $opType->save();
    }
};
