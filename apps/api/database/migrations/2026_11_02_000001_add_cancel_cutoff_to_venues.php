<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-24: players may cancel their own booking online until this many hours before it starts
        // (2, 6, 12, 24 or 48). After that they call the venue; staff can always cancel.
        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedSmallInteger('cancel_cutoff_hours')->default(24)->after('min_notice_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('cancel_cutoff_hours');
        });
    }
};
