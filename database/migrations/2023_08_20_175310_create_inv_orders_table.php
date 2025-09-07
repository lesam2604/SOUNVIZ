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
        Schema::create('inv_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('client_first_name');
            $table->string('client_last_name');
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_orders');
    }
};
