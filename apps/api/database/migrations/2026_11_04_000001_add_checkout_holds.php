<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Checkout holds (FR-16, ADR-021): a booking paid online keeps its half hours until
        // hold_expires_at while the player pays. The index is for finding holds whose time is up.
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('hold_expires_at')->nullable()->after('status');
            $table->index(['status', 'hold_expires_at']);
        });

        // How long a player has to pay. 15 minutes by default, because FPX bank login plus the
        // OTP can take longer than 10 (plan section 7).
        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedTinyInteger('hold_minutes')->default(15)->after('cancel_cutoff_hours');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('hold_minutes');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'hold_expires_at']);
            $table->dropColumn('hold_expires_at');
        });
    }
};
