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
        Permission::findByName('view performances')->assignRole('collab');

        $gtp_recharge = OperationType::firstWhere('code', 'gtp_recharge');
        $card_recharge = OperationType::firstWhere('code', 'card_recharge');

        [$gtp_recharge->position, $card_recharge->position] = [$card_recharge->position, $gtp_recharge->position];

        $gtp_recharge->save();
        $card_recharge->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::findByName('view performances')->removeRole('collab');

        $gtp_recharge = OperationType::firstWhere('code', 'gtp_recharge');
        $card_recharge = OperationType::firstWhere('code', 'card_recharge');

        [$gtp_recharge->position, $card_recharge->position] = [$card_recharge->position, $gtp_recharge->position];

        $gtp_recharge->save();
        $card_recharge->save();
    }
};
