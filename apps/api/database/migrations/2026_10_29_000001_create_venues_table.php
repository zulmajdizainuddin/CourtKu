<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            // The public address, e.g. /v/arena-sg-buloh.
            $table->string('slug', 60)->unique();
            // pending until an admin approves it; only active venues are public (FR-04).
            $table->string('status', 20)->default('pending')->index();
            $table->string('state', 30);

            // The rest of the profile is filled in on the profile page (FR-06).
            $table->text('description')->nullable();
            $table->string('address_line')->nullable();
            $table->string('city', 100)->nullable();
            $table->char('postcode', 5)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('phone', 20)->nullable();
            $table->json('facilities')->nullable();

            // Fictional venues for the live demo (reset every night from M5).
            $table->boolean('is_demo')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
        });

        // Who works at each venue, and as what. A user has at most one role per venue.
        Schema::create('venue_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->timestamps();

            $table->unique(['venue_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_members');
        Schema::dropIfExists('venues');
    }
};
