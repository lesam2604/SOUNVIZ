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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('code')->index();
            $table->json('data');
            $table->decimal('amount', 17, 2)->nullable();
            $table->decimal('fee', 17, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('feedback', 1000)->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->decimal('commission', 17, 2)->nullable();
            $table->boolean('withdrawn')->nullable();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
