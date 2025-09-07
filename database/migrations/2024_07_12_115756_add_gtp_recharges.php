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
        $opType = new OperationType;
        $opType->name = 'Recharge de GTP';
        $opType->code = 'gtp_recharge';
        $opType->prefix = 'GTP';
        $opType->icon_class = 'fas fa-wallet';
        $opType->amount_field = 'amount';
        $opType->position = 14;
        $opType->fields = [
            "id" => [
                "type" => "text",
                "label" => "Id",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 1
            ],
            "name" => [
                "type" => "text",
                "label" => "Nom",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 2
            ],
            "amount" => [
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
                "position" => 3
            ]
        ];
        $opType->fees = [
            [
                "breakpoint" => "",
                "value" => "0"
            ]
        ];
        $opType->commissions = [
            [
                "breakpoint" => "",
                "value" => "0"
            ]
        ];
        $opType->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        OperationType::where('code', 'gtp_recharge')->delete();
    }
};
