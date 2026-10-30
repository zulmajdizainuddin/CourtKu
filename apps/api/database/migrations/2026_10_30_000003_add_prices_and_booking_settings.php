<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-09: the court's normal price per hour, in sen (RM 25.00 = 2500), always a
        // multiple of 10 sen (ADR-017). Empty until the owner sets it; courts without a
        // price are hidden from players.
        Schema::table('courts', function (Blueprint $table) {
            $table->unsignedInteger('base_price_sen')->nullable()->after('is_indoor');
        });

        // FR-13: how players may book this venue.
        Schema::table('venues', function (Blueprint $table) {
            // The length of one slot players choose: 30 or 60 minutes.
            $table->unsignedSmallInteger('slot_minutes')->default(60)->after('facilities');
            // The shortest and longest booking, in minutes.
            $table->unsignedSmallInteger('min_minutes')->default(60)->after('slot_minutes');
            $table->unsignedSmallInteger('max_minutes')->default(180)->after('min_minutes');
            // How many days ahead players can book.
            $table->unsignedSmallInteger('booking_window_days')->default(14)->after('max_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['slot_minutes', 'min_minutes', 'max_minutes', 'booking_window_days']);
        });
        Schema::table('courts', function (Blueprint $table) {
            $table->dropColumn('base_price_sen');
        });
    }
};
