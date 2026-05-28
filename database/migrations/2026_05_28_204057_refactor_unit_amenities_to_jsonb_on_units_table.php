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
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn([
                'has_parking',
                'parking_count',
                'parking_identifiers',
                'has_storage',
                'storage_count',
                'storage_identifiers',
            ]);
            $table->jsonb('amenities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('amenities');
            $table->boolean('has_parking')->default(false);
            $table->integer('parking_count')->nullable();
            $table->json('parking_identifiers')->nullable();
            $table->boolean('has_storage')->default(false);
            $table->integer('storage_count')->nullable();
            $table->json('storage_identifiers')->nullable();
        });
    }
};
