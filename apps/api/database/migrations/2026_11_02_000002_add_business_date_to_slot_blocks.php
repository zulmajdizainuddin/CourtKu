<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Like bookings: the venue's day a block belongs to, so the day schedule finds it,
        // e.g. 00:30 to 01:30 on Saturday morning is Friday night at a late-night venue (ADR-015).
        // The table has no rows yet (blocks start with this change), so no default is needed.
        Schema::table('slot_blocks', function (Blueprint $table) {
            $table->date('business_date')->after('ends_at');
            $table->index(['venue_id', 'business_date']);
        });
    }

    public function down(): void
    {
        Schema::table('slot_blocks', function (Blueprint $table) {
            $table->dropIndex(['venue_id', 'business_date']);
            $table->dropColumn('business_date');
        });
    }
};
