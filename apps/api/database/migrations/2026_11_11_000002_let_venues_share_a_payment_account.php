<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ADR-030: the online demo venue uses the Stripe test account CourtKu's own Test Venue connected,
        // so an account may now belong to more than one venue. Nothing looks a venue up by its account;
        // each owner still connects through Stripe's own sign-up (ADR-024), which makes a new account.
        Schema::table('venue_payment_accounts', function (Blueprint $table) {
            $table->dropUnique(['account_id']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::table('venue_payment_accounts', function (Blueprint $table) {
            $table->dropIndex(['account_id']);
            $table->unique('account_id');
        });
    }
};
