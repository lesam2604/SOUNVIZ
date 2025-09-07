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
            "client_last_name" => [
                "type" => "text",
                "label" => "Nom",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 1
            ],
            "client_first_name" => [
                "type" => "text",
                "label" => "Prénom",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 2
            ],
            "client_phone_number" => [
                "type" => "text",
                "label" => "Téléphone",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 3
            ],
            "decoder_number" => [
                "type" => "text",
                "label" => "Numéro du décodeur",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 4
            ],
            "subscriber_number" => [
                "type" => "text",
                "label" => "Numéro abonné",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 5
            ],
            "formula" => [
                "type" => "select",
                "label" => "Formule",
                "attributes" => [],
                "options" => [
                    "KWABO (2500)",
                    "ACCESS (5000)",
                    "ACCESSPLUS (15000)",
                    "EVASION (10000)",
                    "EVASIONPLUS (20000)",
                    "TOUTCANAL (40000)",
                    "Complément (0)",
                    "CHARME (6000)",
                    "ENGLISH BASIC DD (5000)",
                    "ENGLISH PLUS DD (13000)"
                ],
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 6
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
                "position" => 7
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
                "label" => "Numéro du décodeur",
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
                    "KWABO (2500)",
                    "ACCESS (5000)",
                    "ACCESSPLUS (15000)",
                    "EVASION (10000)",
                    "EVASIONPLUS (20000)",
                    "TOUTCANAL (40000)",
                    "Complément (0)",
                    "CHARME (6000)",
                    "ENGLISH BASIC DD (5000)",
                    "ENGLISH PLUS DD (13000)"
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
