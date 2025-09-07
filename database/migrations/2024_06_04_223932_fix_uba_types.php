<?php

use App\Models\Card;
use App\Models\Operation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $operations = Operation::query()
            ->join('operation_types', 'operation_type_id', 'operation_types.id')
            ->whereIn('operation_types.code', ['card_activation', 'card_recharge', 'card_deactivation'])
            ->select('operations.*')
            ->orderBy('operations.id')
            ->lazy(5000);

        foreach ($operations as $op) {
            $data = $op->data;

            if ($data->card_type === 'UBA') {
                $card = Card::firstWhere('card_id', $data->card_id);

                if ($card) {
                    $data->uba_type = $card->category->name;
                    $op->data = $data;
                    $op->save();
                }
            }
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
