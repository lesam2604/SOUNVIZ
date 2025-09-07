<?php

use App\Models\OperationType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $opType = OperationType::firstWhere('code', 'canal_resub');
        $fields = $opType->fields;
        $fields->formula->options = [
            "ACCESS (5000)",
            "ACCESSPLUS (15000)",
            "EVASION (10000)",
            "EVASIONPLUS (20000)",
            "TOUTCANAL (40000)",
            "Complément (0)",
            "CHARME (6000)",
            "ENGLISH BASIC DD (5000)",
            "ENGLISH PLUS DD (13000)",
        ];
        $opType->fields = $fields;
        $opType->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $opType = OperationType::firstWhere('code', 'canal_resub');
        $fields = $opType->fields;
        $fields->formula->options = [
            "ACCESS (5000)",
            "ACCESSPLUS (15000)",
            "EVASION (10000)",
            "EVASIONPLUS (20000)",
            "TOUTCANAL (40000)",
            "Zero formule (0)",
            "CHARME (6000)",
            "ENGLISH BASIC DD (5000)",
            "ENGLISH PLUS DD (13000)",
        ];
        $opType->fields = $fields;
        $opType->save();
    }
};
