<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('operation_type_id');
            $table->enum('client_type', ['partner', 'external'])->default('partner');
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            $table->json('items')->nullable();
            $table->integer('total_amount')->default(0);
            $table->string('currency', 8)->default('FCFA');
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('operation_type_id')->references('id')->on('operation_types');
            $table->foreign('partner_id')->references('id')->on('partners');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

