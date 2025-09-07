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
        Schema::create('extra_clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('company_name');
            $table->string('tin');
            $table->string('phone_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });

        Permission::create(['name' => 'add extra-client'])->assignRole('reviewer');
        Permission::create(['name' => 'edit extra-client'])->assignRole('reviewer');
        Permission::create(['name' => 'view extra-client'])->assignRole('reviewer');
        Permission::create(['name' => 'delete extra-client'])->assignRole('reviewer');

        Schema::table('card_orders', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->foreignId('partner_id')->nullable()->change()->constrained()->cascadeOnDelete();
            $table->foreignId('extra_client_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_orders', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->foreignId('partner_id')->change()->constrained()->cascadeOnDelete();
            $table->dropConstrainedForeignId('extra_client_id');
        });

        Permission::findByName('add extra-client')->delete();
        Permission::findByName('edit extra-client')->delete();
        Permission::findByName('view extra-client')->delete();
        Permission::findByName('delete extra-client')->delete();

        Schema::dropIfExists('extra_clients');
    }
};
