<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::findByName('edit partner')->removeRole('partner-master');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::findByName('edit partner')->assignRole('partner-master');
    }
};
