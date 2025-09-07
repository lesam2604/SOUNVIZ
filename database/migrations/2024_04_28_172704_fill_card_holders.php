<?php

use App\Models\CardHolder;
use App\Models\Operation;
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
        $ops = Operation::whereHas('operationType', function ($q) {
            $q->whereIn('code', ['card_activation', 'card_recharge', 'card_deactivation']);
        })
            ->where('status', 'approved')
            ->get();

        foreach ($ops as $obj) {
            CardHolder::updateOrCreate([
                'card_id' => $obj->data->card_id
            ], [
                'card_type' => $obj->data->card_type,
                'uba_type' => $obj->data->uba_type ?? '',
                'card_four_digits' => $obj->data->card_four_digits ?? '',
                'client_first_name' => $obj->data->client_first_name,
                'client_last_name' => $obj->data->client_last_name
            ]);
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
