<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-10: peak and off-peak prices, e.g. "Mon–Fri 6:00 pm – midnight, RM 30 an hour".
        // Rules for the same courts never overlap (ADR-020), so there is no priority number:
        // a rule for one court beats a rule for all courts, and otherwise the court's normal price applies.
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            // null = every court of the venue.
            $table->foreignId('court_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 50)->nullable();
            // Business days the rule covers, 0 = Sunday ... 6 = Saturday (ADR-015).
            $table->json('weekdays');
            $table->time('starts_at');
            $table->time('ends_at');
            // e.g. Friday 6:00 pm to 2:00 am: the hours after midnight still count as Friday (ADR-015).
            $table->boolean('ends_next_day')->default(false);
            // Per hour, in sen, in 10-sen steps (ADR-017).
            $table->unsignedInteger('price_sen');
            $table->timestamps();

            $table->index(['venue_id', 'court_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
