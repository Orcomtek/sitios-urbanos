<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the dunning_policies JSONB column to financial_settings.
     * Structure:
     * {
     *   "enabled": false,
     *   "grace_period_days": 0,
     *   "restrictions": {
     *     "restrict_ecosystem": false,
     *     "restrict_pqrs": false,
     *     "restrict_moving_permits": false,
     *     "restrict_amenities": false
     *   }
     * }
     *
     * Defaults to null — fail-open. No restrictions apply until an admin explicitly enables them.
     */
    public function up(): void
    {
        Schema::table('financial_settings', function (Blueprint $table) {
            $table->jsonb('dunning_policies')->nullable()->after('commission_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_settings', function (Blueprint $table) {
            $table->dropColumn('dunning_policies');
        });
    }
};
