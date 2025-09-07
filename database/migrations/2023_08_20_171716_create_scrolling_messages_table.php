<?php

use App\Models\ScrollingMessage;
use App\Models\User;
use Carbon\Carbon;
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
        Schema::create('scrolling_messages', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->string('content', 1000);
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->integer('time');
            $table->enum('size', ['small', 'medium', 'large']);
            $table->enum('color', ['black', 'blue', 'red', 'yellow', 'green']);
            $table->enum('status', ['enabled', 'disabled'])->default('disabled');
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('updator_id')->nullable()->index();
            $table->timestamps();
        });

        // $this->fill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrolling_messages');
    }

    private function fill()
    {
        $reviewer = User::role('collab')->first();

        ScrollingMessage::insert([
            [
                'label' => 'Partenariat',
                'content' => 'En tant que partenaire, vous jouez un rôle crucial dans notre écosystème. Votre expertise, votre engagement et votre passion sont les piliers de notre partenariat, et nous sommes convaincus que, ensemble, nous pouvons réaliser de grandes choses.',
                'from' => '2024-02-15',
                'to' => '2024-12-30',
                'time' => 120,
                'size' => 'large',
                'color' => 'green',
                'status' => 'enabled',
                'creator_id' => $reviewer->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => 'Salut',
                'content' => "Salut a tous les utilisateurs",
                'from' => '2024-03-01',
                'to' => '2025-01-01',
                'time' => 30,
                'size' => 'medium',
                'color' => 'blue',
                'status' => 'enabled',
                'creator_id' => $reviewer->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
};
