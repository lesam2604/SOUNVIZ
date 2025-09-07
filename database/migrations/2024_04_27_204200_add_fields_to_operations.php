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
        DB::statement('
            ALTER TABLE `operations`
            ADD `card_type` VARCHAR(191) AS (JSON_UNQUOTE(JSON_EXTRACT(`data`, "$.card_type"))) STORED,
            ADD INDEX `idx_card_type` (`card_type`);
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn('card_type');
        });
    }
};
