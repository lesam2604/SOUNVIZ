<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('prefix')->unique();
            $table->string('icon_class');
            $table->string('amount_field')->nullable();
            $table->unsignedBigInteger('position');
            $table->json('fields');
            $table->json('fees')->nullable();
            $table->json('commissions')->nullable();
            $table->timestamps();
        });

        $this->fill();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_types');
    }

    private function fill()
    {
        foreach (Storage::files("data/operations") as $file) {
            $content = json_decode(Storage::get($file));

            OperationType::create([
                'name' => $content->name,
                'code' => $content->code,
                'prefix' => $content->prefix,
                'icon_class' => $content->icon_class,
                'amount_field' => $content->amount_field,
                'position' => $content->position,
                'fields' => $content->fields,
                'fees' => $content->fees,
                'commissions' => $content->commissions,
            ]);
        }
    }
};
