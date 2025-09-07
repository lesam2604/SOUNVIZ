<?php

use App\Models\ScrollingMessage;
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
        Schema::table('scrolling_messages', function (Blueprint $table) {
            $table->boolean('show_auth');
            $table->boolean('show_app');
        });

        ScrollingMessage::query()->update([
            'show_auth' => 1,
            'show_app' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrolling_messages', function (Blueprint $table) {
            $table->dropColumn('show_auth');
            $table->dropColumn('show_app');
        });
    }
};
