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
        Schema::create('operation_cancel_requests', function (Blueprint $table) {
            $table->id();
            // ID de l'opération à annuler
            $table->foreignId('operation_id')
                ->constrained('operations')
                ->onDelete('cascade');

            // Collaborateur qui a fait la demande
            $table->foreignId('requested_by')
                ->constrained('users')
                ->onDelete('cascade');

            // Statut de la demande : pending, approved, rejected
            $table->string('status')->default('pending');
            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_cancel_requests');
    }
};
