<?php

use App\Models\Community;
use App\Models\Resident;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Community::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Unit::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Resident::class)->nullable()->constrained()->nullOnDelete();

            $table->string('type')->default('admin_fee');
            $table->string('status')->default('pending');
            $table->integer('amount'); // In COP

            $table->date('issued_at');
            $table->date('due_date');
            $table->string('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique per community? No, one unit can have multiple admin_fees per month.
            // Indexing for common tenant queries:
            $table->index(['community_id', 'status']);
            $table->index(['community_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
