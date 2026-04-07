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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            // Core operational anchors (admin/guard who receives and delivers)
            $table->foreignId('received_by')->constrained('users');
            $table->foreignId('delivered_by')->nullable()->constrained('users');

            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();

            $table->string('sender_name')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('description')->nullable();

            // 'received', 'delivered', 'returned'
            $table->string('status')->default('received');

            $table->timestamp('received_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
