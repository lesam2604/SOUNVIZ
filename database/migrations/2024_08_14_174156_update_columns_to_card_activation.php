<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $opType = OperationType::firstWhere('code', 'card_activation');
        $fields = $opType->fields;
        $fields->card_four_digits->required = false;
        $opType->fields = $fields;
        $opType->save();

        Permission::findByName('view card_category')->assignRole('partner');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::findByName('view card_category')->removeRole('partner');

        $opType = OperationType::firstWhere('code', 'card_activation');
        $fields = $opType->fields;
        $fields->card_four_digits->required = true;
        $opType->fields = $fields;
        $opType->save();
    }
};
