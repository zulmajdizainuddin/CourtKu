<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The demo world (ADR-030): which days of which demo venue already have their bookings,
        // so each day is filled once, a little at a time, by the minute tick.
        Schema::create('demo_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->date('business_date');
            $table->timestamp('created_at')->nullable();

            $table->unique(['venue_id', 'business_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_days');
    }
};
