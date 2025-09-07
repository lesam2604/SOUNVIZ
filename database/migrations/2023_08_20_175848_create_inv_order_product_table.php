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
        Schema::create('inv_order_product', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('quantity');
            $table->decimal('unit_price', 17, 2);
            $table->timestamps();

            $table->primary(['order_id', 'product_id']);

            $table->foreign('order_id')->references('id')->on('inv_orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('inv_products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_order_product');
    }
};
