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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('type', ['peticion', 'queja', 'reclamo', 'sugerencia']);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
