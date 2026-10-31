<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            // FR-27: the venue's owners and staff get an email for each booking a player makes and each one
            // a player cancels. On by default; the owner can switch it off on the Settings tab.
            $table->boolean('booking_emails')->default(true)->after('cancel_cutoff_hours');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('booking_emails');
        });
    }
};
