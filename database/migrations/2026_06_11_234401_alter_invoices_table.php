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
        Schema::table('invoices', function (Blueprint $table) {
            // Drop old columns and foreign keys
            $table->dropForeign(['resident_id']);
            $table->dropIndex(['community_id', 'status']);
            $table->dropIndex(['community_id', 'unit_id']);
            $table->dropColumn(['resident_id', 'type', 'amount', 'issued_at', 'description']);

            // Add new columns
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Format: strict integer or string scoped to community
            $table->string('invoice_number');

            $table->date('issue_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // For idempotency check
            $table->string('billing_period');

            // Unique constraints
            $table->unique(['community_id', 'invoice_number']);
            $table->unique(['community_id', 'unit_id', 'billing_period']);
            $table->index(['community_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['community_id', 'invoice_number']);
            $table->dropUnique(['community_id', 'unit_id', 'billing_period']);
            $table->dropIndex(['community_id', 'status']);
            $table->dropForeign(['user_id']);

            $table->dropColumn(['user_id', 'invoice_number', 'issue_date', 'subtotal', 'total', 'billing_period']);

            $table->foreignId('resident_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->string('type')->default('admin_fee');
            $table->integer('amount')->default(0); // In COP
            $table->date('issued_at')->nullable();
            $table->string('description')->nullable();

            $table->index(['community_id', 'status']);
            $table->index(['community_id', 'unit_id']);
        });
    }
};
