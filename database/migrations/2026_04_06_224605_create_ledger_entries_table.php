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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Community::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Unit::class)->constrained()->cascadeOnDelete();

            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignUuid('payment_id')->nullable()->constrained('payments')->nullOnDelete();

            $table->string('type');
            $table->integer('amount'); // Positive = charge/debit, Negative = payment applied/credit
            $table->string('description')->nullable();

            $table->timestamps();

            // Optimize querying balance per unit
            $table->index(['community_id', 'unit_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
