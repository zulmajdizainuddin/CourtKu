<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff invites (FR-05, ADR-019). The invited person may not have an account yet,
        // so the invite has its own row; it becomes a venue_members row when accepted.
        Schema::create('venue_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role', 20);
            // Only a SHA-256 hash of the secret code in the link, never the code itself.
            $table->char('token_hash', 64)->unique();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['venue_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_invites');
    }
};
