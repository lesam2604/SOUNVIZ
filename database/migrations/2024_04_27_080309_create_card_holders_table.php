<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('card_holders', function (Blueprint $table) {
            $table->string('card_id')->primary();
            $table->string('card_type');
            $table->string('uba_type');
            $table->string('card_four_digits');
            $table->string('client_first_name');
            $table->string('client_last_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_holders');
    }
};
