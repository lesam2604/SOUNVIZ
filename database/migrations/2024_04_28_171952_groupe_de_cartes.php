<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $opTypes = OperationType::whereIn('code', ['card_activation', 'card_recharge', 'card_deactivation'])->get();

        foreach ($opTypes as $opType) {
            $fields = $opType->fields;
            $fields->uba_type->label = 'Groupe de carte';
            $opType->fields = $fields;
            $opType->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
