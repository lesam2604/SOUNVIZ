<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 17, 2);
            $table->decimal('balance', 17, 2);
            $table->timestamps();
        });

        Permission::create(['name' => 'view statement'])->assignRole('partner-master');
        Permission::create(['name' => 'export-excel statement'])->assignRole('partner-master');
        Permission::create(['name' => 'export-pdf statement'])->assignRole('partner-master');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::findByName('view statement')->delete();
        Permission::findByName('export-excel statement')->delete();
        Permission::findByName('export-pdf statement')->delete();

        Schema::dropIfExists('statements');
    }
};
