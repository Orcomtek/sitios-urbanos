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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('species', ['dog', 'cat', 'other']);
            $table->string('breed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
