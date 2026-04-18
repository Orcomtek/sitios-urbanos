<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreignId('community_id')->after('id')->constrained()->cascadeOnDelete();
            $table->decimal('vote_weight', 5, 2)->default(1.00)->after('unit_id');
            
            // Drop old non-tenant-isolated unique constraint
            //$table->dropUnique(['poll_id', 'unit_id']);
            
            // Apply new RIGOR strict tenant-isolation uniqueness
            $table->unique(['community_id', 'poll_id', 'user_id']);
        });

        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('agreed_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Strict double-sign prevention
            $table->unique(['community_id', 'document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['community_id', 'poll_id', 'user_id']);
            $table->dropForeign(['community_id']);
            $table->dropColumn(['community_id', 'vote_weight']);
        });
    }
};
