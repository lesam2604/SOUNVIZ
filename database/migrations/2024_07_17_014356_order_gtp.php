<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $opType = OperationType::firstWhere('code', 'account_recharge');

        OperationType::where('position', '>', $opType->position)
            ->update(['position' => DB::raw('position + 1')]);

        $gtp = OperationType::firstWhere('code', 'gtp_recharge');
        $gtp->position = $opType->position + 1;
        $gtp->fields = [
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
            "bank_type" => [
                "type" => "text",
                "label" => "Type de banque",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 3
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
                "position" => 4
            ]
        ];
        $gtp->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $opType = OperationType::firstWhere('code', 'account_recharge');

        OperationType::where('position', '>', $opType->position)
            ->update(['position' => DB::raw('position - 1')]);

        $gtp = OperationType::firstWhere('code', 'gtp_recharge');
        $gtp->position = OperationType::count();
        $gtp->fields = [
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
        $gtp->save();
    }
};
