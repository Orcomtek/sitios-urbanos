<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('type', 'property_type');
            $table->boolean('has_parking')->default(false);
            $table->integer('parking_count')->nullable();
            $table->jsonb('parking_identifiers')->nullable();
            $table->boolean('has_storage')->default(false);
            $table->integer('storage_count')->nullable();
            $table->jsonb('storage_identifiers')->nullable();
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->renameColumn('type', 'resident_type');
            $table->boolean('pays_administration')->default(false);
            $table->string('full_name')->nullable();
            $table->boolean('is_active')->default(true);
        });

        // Copy data over (SQLite uses || for concat, PG supports it too, MySQL uses CONCAT but wait, we can just use PHP layer if it's early dev, or ignore the data migration for now). Since it's dev, data loss on this rename is probably fine or we can just do:
        $residents = DB::table('residents')->get();
        foreach ($residents as $r) {
            DB::table('residents')->where('id', $r->id)->update([
                'full_name' => trim(($r->first_name ?? '').' '.($r->last_name ?? '')),
                'is_active' => ($r->status ?? 'active') === 'active',
            ]);
        }

        Schema::table('residents', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name', 'status']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("CREATE UNIQUE INDEX one_active_tenant_per_unit ON residents (unit_id) WHERE resident_type = 'tenant' AND is_active = true;");
        } else {
            // For sqlite testing in memory, we can use a standard unique index if we ensure tests handle it or just skip the partial index since sqlite supports partial unique indexes in newer versions:
            DB::statement("CREATE UNIQUE INDEX one_active_tenant_per_unit ON residents (unit_id) WHERE resident_type = 'tenant' AND is_active = true;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS one_active_tenant_per_unit;');

        Schema::table('residents', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('status')->default('active');
        });

        $residents = DB::table('residents')->get();
        foreach ($residents as $r) {
            DB::table('residents')->where('id', $r->id)->update([
                'first_name' => $r->full_name,
                'last_name' => '',
                'status' => $r->is_active ? 'active' : 'inactive',
            ]);
        }

        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'is_active', 'pays_administration']);
            $table->renameColumn('resident_type', 'type');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('property_type', 'type');
            $table->dropColumn([
                'has_parking', 'parking_count', 'parking_identifiers',
                'has_storage', 'storage_count', 'storage_identifiers',
            ]);
        });
    }
};
