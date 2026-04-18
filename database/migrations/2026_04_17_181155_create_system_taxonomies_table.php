<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_taxonomies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('community_id')->nullable()->constrained('communities')->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('label');
            $table->string('value');
            $table->jsonb('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_taxonomies');
    }
};
