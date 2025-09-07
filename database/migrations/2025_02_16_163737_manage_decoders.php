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
        Schema::create('decoders', function (Blueprint $table) {
            $table->id();
            $table->string('decoder_number')->unique();
            $table->unsignedBigInteger('decoder_order_id')->nullable()->index();
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('decoder_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('nbdecoders');
            $table->foreignId('partner_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('extra_client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });

        $permissions = [
            'view decoder' => 'reviewer',
            'add decoder' => 'reviewer',
            'edit decoder' => 'reviewer',
            'delete decoder' => 'reviewer',
            'view decoder stock' => ['partner-master', 'reviewer'],
            'view decoder_order' => ['partner-master', 'reviewer'],
            'add decoder_order' => 'reviewer',
            'delete decoder_order' => 'reviewer',
            'generate-bill decoder_order' => 'reviewer',
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission])->assignRole($roles);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'view decoder',
            'add decoder',
            'edit decoder',
            'delete decoder',
            'view decoder stock',
            'view decoder_order',
            'add decoder_order',
            'delete decoder_order',
            'generate-bill decoder_order',
        ];

        foreach ($permissions as $permission) {
            Permission::findByName($permission)->delete();
        }

        Schema::dropIfExists('decoder_orders');
        Schema::dropIfExists('decoders');
    }
};
