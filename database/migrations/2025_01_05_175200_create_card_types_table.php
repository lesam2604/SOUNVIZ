<?php

use App\Models\CardCategory;
use App\Models\CardType;
use App\Models\OperationType;
use App\Models\User;
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
        Schema::create('card_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });

        Permission::create(['name' => 'view card_type'])->assignRole('reviewer');
        Permission::create(['name' => 'add card_type'])->assignRole('reviewer');
        Permission::create(['name' => 'edit card_type'])->assignRole('reviewer');
        Permission::create(['name' => 'delete card_type'])->assignRole('reviewer');

        $this->fill();

        foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
            $opType = OperationType::firstWhere('code', $opTypeCode);
            $fields = $opType->fields;

            $fields->card_type->options = CardType::orderBy('id')->pluck('name')->toArray();

            $fields->uba_type->type = 'select';
            $fields->uba_type->options = CardCategory::orderBy('name')->pluck('name')->toArray();

            $opType->fields = $fields;
            $opType->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['card_activation', 'card_deactivation', 'card_recharge'] as $opTypeCode) {
            $opType = OperationType::firstWhere('code', $opTypeCode);
            $fields = $opType->fields;

            $fields->card_type->options = ["ECOBANK", "UBA", "Orabank", "BSIC", "Autres"];

            $fields->uba_type->type = 'text';
            $fields->uba_type->options = null;

            $opType->fields = $fields;
            $opType->save();
        }

        Permission::findByName('view card_type')->delete();
        Permission::findByName('add card_type')->delete();
        Permission::findByName('edit card_type')->delete();
        Permission::findByName('delete card_type')->delete();

        Schema::dropIfExists('card_types');
    }

    private function fill()
    {
        $admin = User::role('admin')->first();

        CardType::insert([
            [
                'name' => 'ECOBANK',
                'creator_id' => $admin->id,
                'created_at' => now()
            ],
            [
                'name' => 'UBA',
                'creator_id' => $admin->id,
                'created_at' => now()
            ],
            [
                'name' => 'Orabank',
                'creator_id' => $admin->id,
                'created_at' => now()
            ],
            [
                'name' => 'BSIC',
                'creator_id' => $admin->id,
                'created_at' => now()
            ],
            [
                'name' => 'Autres',
                'creator_id' => $admin->id,
                'created_at' => now()
            ],
        ]);
    }
};
