<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $opType = OperationType::firstWhere('code', 'card_activation');
        $fields = [
            "card_type" => [
                "type" => "select",
                "label" => "Type de carte",
                "attributes" => [],
                "options" => [
                    "ECOBANK",
                    "UBA",
                    "Orabank",
                    "BSIC",
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
            "uba_type" => [
                "type" => "text",
                "label" => "Groupe de carte",
                "attributes" => [],
                "options" => null,
                "required" => [
                    "card_type",
                    "UBA"
                ],
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 2
            ],
            "card_id" => [
                "type" => "card",
                "label" => "Id de la carte",
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
            "card_four_digits" => [
                "type" => "text",
                "label" => "4 derniers chiffres",
                "attributes" => [
                    "minlength" => 4,
                    "maxlength" => 4
                ],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 4
            ],
            "client_first_name" => [
                "type" => "text",
                "label" => "Prénom du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 5
            ],
            "client_last_name" => [
                "type" => "text",
                "label" => "Nom du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 6
            ],
            "client_phone_number" => [
                "type" => "text",
                "label" => "Numéro de telephone du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 7
            ],
            "client_email" => [
                "type" => "email",
                "label" => "Email du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 8
            ],
            "client_tin" => [
                "type" => "text",
                "label" => "Numéro IFU",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 9
            ],
            "client_address" => [
                "type" => "text",
                "label" => "Adresse",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 10
            ],
            "client_country_id" => [
                "type" => "country",
                "label" => "Pays",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 11
            ],
            "birth_date" => [
                "type" => "date",
                "label" => "Date de naissance",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => true,
                "position" => 12
            ],
            "client_idcard_number" => [
                "type" => "text",
                "label" => "Numero de la carte d'identité du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 13
            ],
            "client_expiration_date" => [
                "type" => "date",
                "label" => "Date d'expiration de la carte d'identité",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 14
            ],
            "client_idcard_picture" => [
                "type" => "file",
                "label" => "Fichier de la carte d'identité (recto)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 15
            ],
            "client_idcard_picture_verso" => [
                "type" => "file",
                "label" => "Fichier de la carte d'identité (verso)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 16
            ],
            "form_picture1" => [
                "type" => "file",
                "label" => "Fichier du formulaire (recto)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 17
            ],
            "form_picture1_verso" => [
                "type" => "file",
                "label" => "Fichier du formulaire (verso)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 18
            ],
            "form_picture2" => [
                "type" => "file",
                "label" => "Fichier de la carte a activer",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 19
            ],
            "kyc_form" => [
                "type" => "file",
                "label" => "Fiche KYC",
                "attributes" => [],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 20
            ],
        ];
        $opType->fields = $fields;
        $opType->save();

        DB::statement('
            ALTER TABLE `operations`
            
            ADD `client_first_name` VARCHAR(191) AS (JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.client_first_name"))) STORED,
            ADD `client_last_name` VARCHAR(191) AS (JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.client_last_name"))) STORED,
            ADD `birth_date` VARCHAR(191) AS (JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.birth_date"))) STORED,

            ADD INDEX `idx_client_first_name` (`client_first_name`),
            ADD INDEX `idx_client_last_name` (`client_last_name`),
            ADD INDEX `idx_birth_date` (`birth_date`),
            ADD INDEX `idx_client_unique` (`client_first_name`, `client_last_name`, `birth_date`)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropIndex('idx_client_unique');
            $table->dropColumn('client_first_name');
            $table->dropColumn('client_last_name');
            $table->dropColumn('birth_date');
        });

        $opType = OperationType::firstWhere('code', 'card_activation');
        $fields = [
            "card_type" => [
                "type" => "select",
                "label" => "Type de carte",
                "attributes" => [],
                "options" => [
                    "ECOBANK",
                    "UBA",
                    "Orabank",
                    "BSIC",
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
            "uba_type" => [
                "type" => "text",
                "label" => "Groupe de carte",
                "attributes" => [],
                "options" => null,
                "required" => [
                    "card_type",
                    "UBA"
                ],
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 2
            ],
            "card_id" => [
                "type" => "card",
                "label" => "Id de la carte",
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
            "card_four_digits" => [
                "type" => "text",
                "label" => "4 derniers chiffres",
                "attributes" => [
                    "minlength" => 4,
                    "maxlength" => 4
                ],
                "options" => null,
                "required" => false,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 4
            ],
            "client_first_name" => [
                "type" => "text",
                "label" => "Prénom du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 5
            ],
            "client_last_name" => [
                "type" => "text",
                "label" => "Nom du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 6
            ],
            "client_idcard_number" => [
                "type" => "text",
                "label" => "Numero de la carte d'identité du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 7
            ],
            "client_phone_number" => [
                "type" => "text",
                "label" => "Numéro de telephone du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 8
            ],
            "client_email" => [
                "type" => "email",
                "label" => "Email du client",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 9
            ],
            "client_tin" => [
                "type" => "text",
                "label" => "Numéro IFU",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 10
            ],
            "client_address" => [
                "type" => "text",
                "label" => "Adresse",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => true,
                "lte_today" => false,
                "position" => 11
            ],
            "client_country_id" => [
                "type" => "country",
                "label" => "Pays",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 12
            ],
            "client_idcard_picture" => [
                "type" => "file",
                "label" => "Fichier de la carte d'identité (recto)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 13
            ],
            "client_idcard_picture_verso" => [
                "type" => "file",
                "label" => "Fichier de la carte d'identité (verso)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 14
            ],
            "form_picture1" => [
                "type" => "file",
                "label" => "Fichier du formulaire (recto)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 15
            ],
            "form_picture1_verso" => [
                "type" => "file",
                "label" => "Fichier du formulaire (verso)",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 16
            ],
            "form_picture2" => [
                "type" => "file",
                "label" => "Fichier de la carte a activer",
                "attributes" => [],
                "options" => null,
                "required" => true,
                "unique" => false,
                "is_amount" => false,
                "stored" => true,
                "updated" => true,
                "listed" => false,
                "lte_today" => false,
                "position" => 17
            ]
        ];
        $opType->fields = $fields;
        $opType->save();
    }
};
