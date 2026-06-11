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
        Schema::create('financial_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('base_budget', 12, 2)->default(0);
            $table->decimal('late_fee_interest_rate', 5, 2)->default(0);
            $table->integer('billing_day')->default(1);
            $table->integer('due_day')->default(10);
            $table->json('bank_account_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_settings');
    }
};
