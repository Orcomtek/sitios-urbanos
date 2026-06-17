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
        Schema::create('financial_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_concept_id')->constrained();
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['community_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_adjustments');
    }
};
