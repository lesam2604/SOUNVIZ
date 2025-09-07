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
        Permission::findByName('view card_order')->assignRole('partner-master');
        Permission::create(['name' => 'view card stock'])->assignRole(['reviewer', 'partner-master']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::findByName('view card_order')->removeRole('partner-master');
        Permission::findByName('view card stock')->delete();
    }
};
