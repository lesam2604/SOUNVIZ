<?php

use App\Models\Setting;
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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('dashboard_message')->nullable();
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
        Schema::dropIfExists('settings');
    }

    private function fill()
    {
        Setting::create([
            'dashboard_message' => 'Bienvenue sur votre tableau de bord personnalisé ! Votre tableau de bord est conçu pour répondre à vos besoins, vous offrant un point central pour toutes vos informations et outils importants, vous permettant ainsi de prendre des décisions éclairées et de gérer efficacement votre entreprise.'
        ]);
    }
};
