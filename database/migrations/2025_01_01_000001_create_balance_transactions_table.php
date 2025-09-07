<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // Quelques types suggérés : admin_credit, admin_debit, operation_debit, cancel_refund…
            $table->string('type', 40);
            $table->bigInteger('amount'); // toujours POSITIF
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->unsignedBigInteger('cancel_request_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // admin ou système
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
    }
};
