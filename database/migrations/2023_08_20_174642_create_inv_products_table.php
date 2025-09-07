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
        Schema::create('inv_products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('unit_price', 17, 2);
            $table->foreignId('category_id')->constrained('inv_categories');
            $table->unsignedBigInteger('stock_quantity');
            $table->unsignedBigInteger('stock_quantity_min');
            $table->string('picture')->nullable();
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
        Schema::dropIfExists('inv_products');
    }
};
