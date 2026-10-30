<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-13: how close to the start players may still book. 0 = right up to the start
        // time, e.g. book at 7:50 pm for 8:00 pm. Owners who need time to open up can ask
        // for notice instead (30 minutes up to 1 day).
        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_notice_minutes')->default(0)->after('booking_window_days');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('min_notice_minutes');
        });
    }
};
