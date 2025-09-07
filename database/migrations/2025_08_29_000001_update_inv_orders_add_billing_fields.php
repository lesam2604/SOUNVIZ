<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('partner_id')->nullable()->after('id')->index();
            $table->unsignedBigInteger('extra_client_id')->nullable()->after('partner_id')->index();
            $table->string('status')->default('draft')->after('updator_id');
            $table->boolean('is_paid')->default(false)->after('status');
            $table->decimal('total_amount', 17, 2)->default(0)->after('is_paid');

            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
            $table->foreign('extra_client_id')->references('id')->on('extra_clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inv_orders', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['extra_client_id']);
            $table->dropColumn(['partner_id','extra_client_id','status','is_paid','total_amount']);
        });
    }
};

