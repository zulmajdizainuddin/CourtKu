<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // FR-26: when the "your game is tomorrow" email was queued, so each booking gets it once.
            $table->dateTime('reminder_sent_at')->nullable()->after('checked_in_at');
            // The minute tick looks for bookings starting within a day that haven't had their reminder.
            $table->index(['status', 'reminder_sent_at', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'reminder_sent_at', 'starts_at']);
            $table->dropColumn('reminder_sent_at');
        });
    }
};
