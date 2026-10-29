<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // When a venue is open (FR-07). Up to two periods per weekday, e.g. a break for
        // Friday prayers (ADR-016). Times are the venue's own clock (Malaysia time).
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            // 0 = Sunday ... 6 = Saturday, the same numbers PHP and Carbon use.
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at');
            $table->time('closes_at');
            // e.g. opens 08:00, closes 02:00 the next morning (FR-37). The hours after midnight
            // still belong to this weekday's business day (ADR-015).
            $table->boolean('closes_next_day')->default(false);
            $table->timestamps();

            $table->index(['venue_id', 'weekday']);
        });

        // Days the venue is closed, e.g. Hari Raya or renovation (FR-07). Whole days, from and to inclusive.
        Schema::create('venue_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('reason', 100);
            $table->timestamps();

            $table->index(['venue_id', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_closures');
        Schema::dropIfExists('operating_hours');
    }
};
