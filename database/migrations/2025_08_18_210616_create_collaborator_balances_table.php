<?php
// database/migrations/XXXX_XX_XX_XXXXXX_create_collaborator_balances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collaborator_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // 1 solde par collaborateur (users.id)
            $table->bigInteger('balance')->default(0);        // stocké en plus petite unité (ex: XOF centimes)
            $table->string('currency', 8)->default('XOF');
            $table->unsignedBigInteger('updated_by')->nullable(); // dernier acteur (admin/collab)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborator_balances');
    }
};
