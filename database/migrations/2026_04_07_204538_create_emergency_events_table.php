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
        Schema::create('emergency_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('triggered_by')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->default('panic'); // panic, medical, security, other
            $table->string('status', 50)->default('active'); // active, acknowledged, resolved
            $table->text('description')->nullable();
            
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();

            // To avoid rapid duplicates per unit and type (only one active per type per unit)
            // But we'll enforce this mostly at application level since there are multiple active states potentially (though unlikely).
            // Actually, a unique index on community+unit+type+status="active" could be complex in postgres directly without partial index.
            // We'll trust the application-level validation.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_events');
    }
};
