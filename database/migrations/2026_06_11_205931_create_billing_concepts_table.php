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
        Schema::create('billing_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('type', [
                'recurring_hoa',
                'extraordinary',
                'fine',
                'amenity_rental',
                'marketplace_subscription',
                'provider_membership',
            ]);
            $table->boolean('is_commissionable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_concepts');
    }
};
