<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            // FR-40: the owner's own words added to the cancellation and refund policy players see before
            // they pay, e.g. "Rained out? We move your booking." The rule itself comes from cancel_cutoff_hours.
            $table->string('refund_note', 300)->nullable()->after('cancel_cutoff_hours');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('refund_note');
        });
    }
};
