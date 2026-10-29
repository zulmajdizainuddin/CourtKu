<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->timestamps();
        });

        // Added here, not in a seeder: the live database only ever runs migrations,
        // and every venue needs these three sports.
        DB::table('sports')->insert([
            ['name' => 'Badminton', 'slug' => 'badminton', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Futsal', 'slug' => 'futsal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pickleball', 'slug' => 'pickleball', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // FR-08: a venue's courts. One court is one floor with one booking timeline,
        // even when it offers several sports (ADR-014).
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->boolean('is_indoor')->default(true);
            // Inactive courts stay in the dashboard but players can't see or book them.
            $table->boolean('is_active')->default(true);
            // The order courts appear in, on the dashboard and the public page.
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['venue_id', 'name']);
            $table->index(['venue_id', 'sort_order']);
        });

        // Which sports each court offers (ADR-014), e.g. a hall for futsal and badminton.
        Schema::create('court_sports', function (Blueprint $table) {
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            // A sport can't be removed while courts still offer it.
            $table->foreignId('sport_id')->constrained()->restrictOnDelete();

            $table->primary(['court_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_sports');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('sports');
    }
};
