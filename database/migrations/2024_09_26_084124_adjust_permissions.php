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
        $permissions = ['set setting', 'edit partner', 'add balance_adjustment'];
        foreach ($permissions as $permission) {
            Permission::findByName($permission)
                ->removeRole('reviewer')
                ->assignRole('admin');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = ['set setting', 'edit partner', 'add balance_adjustment'];
        foreach ($permissions as $permission) {
            Permission::findByName($permission)
                ->removeRole('admin')
                ->assignRole('reviewer');
        }
    }
};
