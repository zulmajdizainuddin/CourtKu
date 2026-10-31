<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ADR-030: a big demo day (8 courts) took longer than one minute's tick on the live site, so a
        // day is now filled court by court across ticks: how many courts are done, and when it was finished.
        Schema::table('demo_days', function (Blueprint $table) {
            $table->unsignedSmallInteger('courts_done')->default(0)->after('business_date');
            $table->timestamp('finished_at')->nullable()->after('courts_done');
        });

        // Days filled before this change were always filled in one go.
        DB::table('demo_days')->update(['finished_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('demo_days', function (Blueprint $table) {
            $table->dropColumn(['courts_done', 'finished_at']);
        });
    }
};
