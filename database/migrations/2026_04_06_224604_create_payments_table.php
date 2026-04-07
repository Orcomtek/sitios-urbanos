<?php

use App\Models\Community;
use App\Models\Unit;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Community::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Unit::class)->nullable()->constrained()->nullOnDelete();

            // For MVP, 1 payment -> 1 invoice
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->string('method');
            $table->string('status')->default('pending');
            $table->integer('amount'); // In COP
            $table->integer('platform_commission')->default(0); // In COP

            $table->string('external_reference')->nullable();

            // Idempotency constraint per community
            $table->string('idempotency_key')->nullable();
            $table->unique(['community_id', 'idempotency_key']);

            $table->timestamps();

            $table->index(['community_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
