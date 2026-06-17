<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE billing_concepts DROP CONSTRAINT IF EXISTS billing_concepts_type_check');
        DB::statement("ALTER TABLE billing_concepts ADD CONSTRAINT billing_concepts_type_check CHECK (type::text = ANY (ARRAY['recurring_hoa'::character varying, 'extraordinary'::character varying, 'fine'::character varying, 'amenity_rental'::character varying, 'marketplace_subscription'::character varying, 'provider_membership'::character varying, 'credit_note'::character varying, 'debit_note'::character varying, 'discount'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE billing_concepts DROP CONSTRAINT IF EXISTS billing_concepts_type_check');
        DB::statement("ALTER TABLE billing_concepts ADD CONSTRAINT billing_concepts_type_check CHECK (type::text = ANY (ARRAY['recurring_hoa'::character varying, 'extraordinary'::character varying, 'fine'::character varying, 'amenity_rental'::character varying, 'marketplace_subscription'::character varying, 'provider_membership'::character varying, 'credit_note'::character varying, 'debit_note'::character varying]::text[]))");
    }
};
