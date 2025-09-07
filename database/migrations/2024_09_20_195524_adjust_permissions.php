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
        foreach (
            [
                'view partner_commission',
                'view withdrawal',
                'add withdrawal',
                'add money_transfer',
                'view money_transfer',
                'view balance_adjustment',
            ] as $permission
        ) {
            $permission = Permission::findByName($permission);

            $permission->removeRole('partner');
            $permission->assignRole('partner-master');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (
            [
                'view partner_commission',
                'view withdrawal',
                'add withdrawal',
                'add money_transfer',
                'view money_transfer',
                'view balance_adjustment',
            ] as $permission
        ) {
            $permission = Permission::findByName($permission);

            $permission->removeRole('partner-master');
            $permission->assignRole('partner');
        }
    }
};
