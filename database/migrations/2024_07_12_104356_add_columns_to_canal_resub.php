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
        $opType = OperationType::firstWhere('code', 'canal_resub');
        $fields = [
            "decoder_number" => [
                "type" => "text",
                "label" => "Numero du décodeur",
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
            "formula" => [
                "type" => "select",
                "label" => "Formule",
                "attributes" => [],
                "options" => [
                    "ACCESS (5000)",
                    "ACCESSPLUS (15000)",
                    "EVASION (10000)",
                    "EVASIONPLUS (20000)",
                    "TOUTCANAL (40000)",
                    "Zero formule (0)",
                    "CHARME (6000)",
                    "ENGLISH BASIC DD (5000)",
                    "ENGLISH PLUS DD (13000)",
                ],
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
        $opType->fields = $fields;
        $opType->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $opType = OperationType::firstWhere('code', 'canal_resub');
        $fields = [
            "decoder_number" => [
                "type" => "text",
                "label" => "Numero du décodeur",
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
            "formula" => [
                "type" => "select",
                "label" => "Formule",
                "attributes" => [],
                "options" => [
                    "ACCESS",
                    "EVASION",
                    "ESSENTIEL+",
                    "ACCESS+",
                    "EVASION+",
                    "TOUT CANAL"
                ],
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
        $opType->fields = $fields;
        $opType->save();
    }
};
