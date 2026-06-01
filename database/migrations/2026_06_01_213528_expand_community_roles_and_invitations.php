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
        // Add name column to user_invitations
        Schema::table('user_invitations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('email');
        });

        DB::statement('ALTER TABLE community_user DROP CONSTRAINT community_user_role_check');
        DB::table('community_user')->where('role', 'admin')->update(['role' => 'tenant_admin']);
        DB::statement("ALTER TABLE community_user ADD CONSTRAINT community_user_role_check CHECK (role::text = ANY (ARRAY['tenant_admin'::character varying, 'sub_admin'::character varying, 'accountant'::character varying, 'auditor'::character varying, 'resident'::character varying, 'guard'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_invitations', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        DB::statement('ALTER TABLE community_user DROP CONSTRAINT community_user_role_check');
        DB::table('community_user')->where('role', 'tenant_admin')->update(['role' => 'admin']);
        DB::statement("ALTER TABLE community_user ADD CONSTRAINT community_user_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'resident'::character varying, 'guard'::character varying]::text[]))");
    }
};
