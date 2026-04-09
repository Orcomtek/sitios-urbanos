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
        Schema::create('provider_service_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();

            $table->text('description');
            $table->string('status')->default('pending');
            $table->string('urgency')->default('low');
            $table->timestamp('scheduled_date')->nullable();

            $table->timestamps();

            $table->index(['community_id', 'resident_id']);
            $table->index(['community_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_service_requests');
    }
};
