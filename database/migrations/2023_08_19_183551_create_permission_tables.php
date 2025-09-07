<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        $this->fill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }

    private function fill()
    {
        Role::create(['name' => 'reviewer']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'collab']);
        Role::create(['name' => 'partner']);
        Role::create(['name' => 'partner-master']);
        Role::create(['name' => 'partner-pos']);

        Permission::create(['name' => 'add collab'])->assignRole('admin');
        Permission::create(['name' => 'edit collab'])->assignRole('admin');
        Permission::create(['name' => 'view collab'])->assignRole('admin');
        Permission::create(['name' => 'delete collab'])->assignRole('admin');

        Permission::create(['name' => 'add partner'])->assignRole('partner-master');
        Permission::create(['name' => 'edit partner'])->assignRole('reviewer', 'partner-master');
        Permission::create(['name' => 'view partner'])->assignRole('reviewer', 'partner-master');
        Permission::create(['name' => 'review partner'])->assignRole('reviewer');
        Permission::create(['name' => 'delete partner'])->assignRole('reviewer', 'partner-master');

        Permission::create(['name' => 'view partner_commission'])->assignRole('admin', 'partner');
        Permission::create(['name' => 'view platform_commission'])->assignRole('admin');

        Permission::create(['name' => 'add operation'])->assignRole('partner');
        Permission::create(['name' => 'edit operation'])->assignRole('partner');
        Permission::create(['name' => 'view operation'])->assignRole('reviewer', 'partner');
        Permission::create(['name' => 'review operation'])->assignRole('reviewer');
        Permission::create(['name' => 'delete operation'])->assignRole('partner');
        Permission::create(['name' => 'export-excel operation'])->assignRole('reviewer', 'partner');
        Permission::create(['name' => 'export-pdf operation'])->assignRole('reviewer', 'partner');

        Permission::create(['name' => 'add money_transfer'])->assignRole('partner');
        Permission::create(['name' => 'view money_transfer'])->assignRole('reviewer', 'partner');

        Permission::create(['name' => 'add withdrawal'])->assignRole('partner');
        Permission::create(['name' => 'view withdrawal'])->assignRole('admin', 'partner');

        Permission::create(['name' => 'add inv_category'])->assignRole('reviewer');
        Permission::create(['name' => 'edit inv_category'])->assignRole('reviewer');
        Permission::create(['name' => 'view inv_category'])->assignRole('reviewer');
        Permission::create(['name' => 'delete inv_category'])->assignRole('reviewer');

        Permission::create(['name' => 'add inv_product'])->assignRole('reviewer');
        Permission::create(['name' => 'edit inv_product'])->assignRole('reviewer');
        Permission::create(['name' => 'view inv_product'])->assignRole('reviewer');
        Permission::create(['name' => 'delete inv_product'])->assignRole('reviewer');

        Permission::create(['name' => 'add inv_supply'])->assignRole('reviewer');
        Permission::create(['name' => 'edit inv_supply'])->assignRole('reviewer');
        Permission::create(['name' => 'view inv_supply'])->assignRole('reviewer');
        Permission::create(['name' => 'delete inv_supply'])->assignRole('reviewer');

        Permission::create(['name' => 'add inv_order'])->assignRole('reviewer');
        Permission::create(['name' => 'edit inv_order'])->assignRole('reviewer');
        Permission::create(['name' => 'view inv_order'])->assignRole('reviewer');
        Permission::create(['name' => 'delete inv_order'])->assignRole('reviewer');

        Permission::create(['name' => 'add inv_delivery'])->assignRole('reviewer');
        Permission::create(['name' => 'edit inv_delivery'])->assignRole('reviewer');
        Permission::create(['name' => 'view inv_delivery'])->assignRole('reviewer');
        Permission::create(['name' => 'delete inv_delivery'])->assignRole('reviewer');

        Permission::create(['name' => 'get setting'])->assignRole('reviewer', 'partner');
        Permission::create(['name' => 'set setting'])->assignRole('reviewer');

        Permission::create(['name' => 'add scrolling_message'])->assignRole('reviewer');
        Permission::create(['name' => 'edit scrolling_message'])->assignRole('reviewer');
        Permission::create(['name' => 'view scrolling_message'])->assignRole('reviewer');
        Permission::create(['name' => 'delete scrolling_message'])->assignRole('reviewer');

        Permission::create(['name' => 'add card_category'])->assignRole('reviewer');
        Permission::create(['name' => 'edit card_category'])->assignRole('reviewer');
        Permission::create(['name' => 'view card_category'])->assignRole('reviewer');
        Permission::create(['name' => 'delete card_category'])->assignRole('reviewer');

        Permission::create(['name' => 'add card'])->assignRole('reviewer');
        Permission::create(['name' => 'edit card'])->assignRole('reviewer');
        Permission::create(['name' => 'view card'])->assignRole('reviewer');
        Permission::create(['name' => 'delete card'])->assignRole('reviewer');

        Permission::create(['name' => 'add card_order'])->assignRole('reviewer');
        Permission::create(['name' => 'edit card_order'])->assignRole('reviewer');
        Permission::create(['name' => 'view card_order'])->assignRole('reviewer');
        Permission::create(['name' => 'delete card_order'])->assignRole('reviewer');
        Permission::create(['name' => 'generate-bill card_order'])->assignRole('reviewer');

        Permission::create(['name' => 'add ticket'])->assignRole('partner');
        Permission::create(['name' => 'edit ticket'])->assignRole('partner');
        Permission::create(['name' => 'respond ticket'])->assignRole('reviewer');
        Permission::create(['name' => 'view ticket'])->assignRole('reviewer', 'partner');
        Permission::create(['name' => 'delete ticket'])->assignRole('partner');

        Permission::create(['name' => 'add balance_adjustment'])->assignRole('reviewer');
        Permission::create(['name' => 'view balance_adjustment'])->assignRole('reviewer', 'partner');

        Permission::create(['name' => 'add broadcast_message'])->assignRole('admin');
        Permission::create(['name' => 'edit broadcast_message'])->assignRole('admin');
        Permission::create(['name' => 'view broadcast_message'])->assignRole('reviewer', 'partner');
        Permission::create(['name' => 'delete broadcast_message'])->assignRole('admin');
    }
};
