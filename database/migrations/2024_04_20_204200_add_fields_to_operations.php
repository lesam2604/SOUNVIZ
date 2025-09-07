<?php

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
        // Schema::table('operations', function (Blueprint $table) {
        //     $table->string('card_id')->storedAs('data ->> "$.card_id"')->index();
        //     $table->string('decoder_number')->storedAs('data ->> "$.decoder_number"')->index();
        // });
        DB::statement('
            ALTER TABLE `operations`
            ADD `card_id` VARCHAR(191) AS (JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.card_id"))) STORED,
            ADD `decoder_number` VARCHAR(191) AS (JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.decoder_number"))) STORED,
            ADD INDEX `idx_card_id` (`card_id`),
            ADD INDEX `idx_decoder_number` (`decoder_number`);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn('card_id');
            $table->dropColumn('decoder_number');
        });
    }
};
