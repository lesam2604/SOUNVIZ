<?php

use App\Models\CardCategory;
use App\Models\User;
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
        Schema::create('card_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->decimal('unit_price', 17, 2);
            $table->unsignedBigInteger('stock_quantity')->default(0);
            $table->unsignedBigInteger('stock_quantity_min');
            $table->string('picture')->nullable();
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });

        // $this->fill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_categories');
    }

    private function fill()
    {
        $reviewer = User::role('admin')->first();

        $categories = [
            [
                'name' => 'Paycard UBA Low',
                'unit_price' => 1100,
                'stock_quantity_min' => 10,
                'picture' => null,
                'creator_id' => $reviewer->id,
            ],
            [
                'name' => 'Ecobank Low',
                'unit_price' => 1250,
                'stock_quantity_min' => 10,
                'picture' => null,
                'creator_id' => $reviewer->id,
            ],
            [
                'name' => 'Paycard UBA Mid',
                'unit_price' => 2200,
                'stock_quantity_min' => 10,
                'picture' => null,
                'creator_id' => $reviewer->id,
            ],
            [
                'name' => 'Discountcard High',
                'unit_price' => 8000,
                'stock_quantity_min' => 10,
                'picture' => null,
                'creator_id' => $reviewer->id,
            ],
            [
                'name' => 'Ecobank High',
                'unit_price' => 6500,
                'stock_quantity_min' => 10,
                'picture' => null,
                'creator_id' => $reviewer->id,
            ]
        ];

        foreach ($categories as $category) {
            $category['code'] = generateUniqueCode('card_categories', 'code', 'CCT');
            CardCategory::create($category);
        }
    }
};
