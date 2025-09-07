<?php

use App\Models\CardType;
use App\Models\OperationType;
use App\Models\OperationTypePartner;
use App\Models\User;
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
        Schema::create('operation_type_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_type_id')->constrained()->cascadeOnDelete();
            $table->string('card_type')->index()->nullable();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_commissions');
            $table->timestamps();
        });

        $opTypes = OperationType::whereNotIn('code', ['account_recharge', 'balance_withdrawal'])
            ->orderBy('position')
            ->get();

        $users = User::role('partner-master')
            ->whereIn('status', ['enabled', 'disabled'])
            ->get();

        foreach ($users as $masterUser) {
            $master = $masterUser->partner;

            foreach ($opTypes as $opType) {
                $cardTypes = in_array($opType->code, ['card_activation', 'card_deactivation', 'card_recharge'])
                    ? CardType::orderBy('id')->pluck('name')->toArray()
                    : [null];

                foreach ($cardTypes as $cardType) {
                    OperationTypePartner::create([
                        'operation_type_id' => $opType->id,
                        'card_type' => $cardType,
                        'partner_id' => $master->id,
                        'has_commissions' => $opType->code === 'card_recharge' &&
                            !$master->has_commissions ? false : true,
                    ]);
                }
            }
        }

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('has_commissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->boolean('has_commissions');
        });

        $users = User::role('partner-master')
            ->whereIn('status', ['enabled', 'disabled'])
            ->get();

        foreach ($users as $masterUser) {
            $master = $masterUser->partner;
            $master->has_commissions = $master->hasCommissions('card_recharge', 'ECOBANK');
            $master->save();
        }

        Schema::dropIfExists('operation_type_partners');
    }
};
