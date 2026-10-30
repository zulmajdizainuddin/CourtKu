<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A court booked by a player online, or by staff for a walk-in or phone customer (FR-15, FR-20).
        // Courts are never deleted while they have bookings (restrict), so booking history stays.
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // "CK-7F3K2" (FR-18)
            $table->foreignId('venue_id')->constrained();
            $table->foreignId('court_id')->constrained();
            $table->foreignId('sport_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained(); // null for walk-ins
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('source', 20); // online, walk_in, phone
            // Real times in UTC (NFR-12). business_date is the venue's day the booking belongs to:
            // Friday for 00:30 to 01:30 on Saturday morning at a late-night venue (ADR-015).
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->date('business_date');
            $table->string('status', 20);
            // The price when booked, in sen. Later price changes never alter it.
            $table->unsignedInteger('total_sen');
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason', 200)->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['court_id', 'starts_at']); // one court's day
            $table->index(['venue_id', 'business_date']); // the staff day schedule
            $table->index(['user_id', 'starts_at']); // a player's bookings
        });

        // Times staff close a court for repairs or a private event (FR-21).
        Schema::create('slot_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained();
            $table->foreignId('court_id')->constrained();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('reason', 100);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // One row per taken half hour of a court (ADR-010). The unique index on (court_id, slot_start)
        // is what makes double booking impossible: when two people book the same half hour at the
        // same moment, the database accepts one row and refuses the other.
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained();
            $table->dateTime('slot_start'); // UTC, always on :00 or :30
            // No "on delete cascade" here: MySQL doesn't allow it on columns used in the CHECK below.
            // The code removes these rows itself when a booking is cancelled or a block lifted.
            $table->foreignId('booking_id')->nullable()->constrained();
            $table->foreignId('slot_block_id')->nullable()->constrained();
            // This half hour's price when booked, in sen (null for blocks).
            $table->unsignedInteger('price_sen')->nullable();
            $table->timestamps();

            $table->unique(['court_id', 'slot_start']);
        });
        // Each row is taken by exactly one booking or one block, never both or neither.
        DB::statement('ALTER TABLE booking_slots ADD CONSTRAINT booking_slots_one_owner CHECK ((booking_id IS NULL) <> (slot_block_id IS NULL))');

        // Who did what to a booking and when (FR-34).
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained(); // null = CourtKu itself
            $table->string('action', 50); // "booking.created", "booking.cancelled"
            $table->morphs('subject');
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('booking_slots');
        Schema::dropIfExists('slot_blocks');
        Schema::dropIfExists('bookings');
    }
};
