<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Where the player pays (the gateway's own page), so "back to the payment page" reuses it
            // instead of opening a second one.
            $table->text('checkout_url')->nullable()->after('checkout_id');
            // When CourtKu last asked the gateway how this payment stands (on the player's return, and the
            // reconciliation in M4 PR 6), so it never asks more often than needed.
            $table->dateTime('checked_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['checkout_url', 'checked_at']);
        });
    }
};
