<?php

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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_status')->nullable()->after('status');
            $table->boolean('signature_verified')->default(false)->after('gateway_status');
            $table->jsonb('gateway_payload')->nullable()->after('signature_verified');
            $table->integer('net_amount')->nullable()->after('amount'); // In COP
            $table->timestamp('paid_at')->nullable()->after('platform_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_status',
                'signature_verified',
                'gateway_payload',
                'net_amount',
                'paid_at'
            ]);
        });
    }
};
