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
        Schema::table('financial_settings', function (Blueprint $table) {
            $table->string('epayco_allied_account_id')->nullable()->after('bank_account_details');
            $table->string('commission_type')->default('fixed')->after('epayco_allied_account_id');
            $table->integer('commission_value')->default(1500)->after('commission_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_settings', function (Blueprint $table) {
            $table->dropColumn([
                'epayco_allied_account_id',
                'commission_type',
                'commission_value',
            ]);
        });
    }
};
