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
        $opType = OperationType::firstWhere('code', 'account_recharge');
        $fields = [
            "sender_phone_number_type" => [
                "type" => "select",
                "label" => "Type de compte",
                "attributes" => [],
                "options" => [
                    "Marchand",
                    "MomoPay",
                    "Autres"
                ],
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 1
            ],
            "other_type" => [
                "type" => "text",
                "label" => "Autre mode de paiement",
                "attributes" => [],
                "options" => null,
                "required" => ['sender_phone_number_type', 'Autres'],
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 2
            ],
            "sender_phone_number" => [
                "type" => "text",
                "label" => "Numero de telephone qui a envoyé",
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
            "trans_id" => [
                "type" => "text",
                "label" => "Id Transaction",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => true,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 4
            ],
            "trans_date" => [
                "type" => "date",
                "label" => "Date Transaction",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => true,
                "position" => 5
            ],
            "trans_amount" => [
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
                "position" => 6
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
        $opType = OperationType::firstWhere('code', 'account_recharge');
        $fields = [
            "sender_phone_number_type" => [
                "type" => "select",
                "label" => "Type de compte",
                "attributes" => [],
                "options" => [
                    "Marchand",
                    "MomoPay"
                ],
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 1
            ],
            "sender_phone_number" => [
                "type" => "text",
                "label" => "Numero de telephone qui a envoyé",
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
            "trans_id" => [
                "type" => "text",
                "label" => "Id Transaction",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => true,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 3
            ],
            "trans_date" => [
                "type" => "date",
                "label" => "Date Transaction",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => true,
                "position" => 4
            ],
            "trans_amount" => [
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
                "position" => 5
            ]
        ];
        $opType->fields = $fields;
        $opType->save();
    }
};
