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
        Schema::create('inv_delivery_product', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('quantity');
            $table->timestamps();

            $table->primary(['delivery_id', 'product_id']);

            $table->foreign('delivery_id')->references('id')->on('inv_deliveries')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('inv_products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_delivery_product');
    }
}; 
