<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How players pay (FR-17): counter (the default), online or both.
        Schema::table('venues', function (Blueprint $table) {
            $table->string('payment_mode', 10)->default('counter')->after('hold_minutes');
        });

        // Each venue's own Stripe account, connected through Stripe Connect (ADR-009, ADR-024).
        // No secrets here at all: only the account's id, which isn't one. CourtKu makes payments on
        // the venue's behalf with its own platform key, and the money lands in the venue's account.
        Schema::create('venue_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gateway', 20); // stripe
            $table->string('account_id', 100)->unique(); // e.g. acct_1Abc...
            $table->string('account_name', 200)->nullable(); // shown to the owner
            $table->boolean('charges_enabled')->default(false); // Stripe says it can take payments
            $table->boolean('is_live')->default(false); // test mode only, for now (ADR-023)
            $table->dateTime('checked_at')->nullable(); // when CourtKu last asked Stripe how it stands
            $table->foreignId('connected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_payment_accounts');
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
};
