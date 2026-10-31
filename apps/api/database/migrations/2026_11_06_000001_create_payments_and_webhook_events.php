<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One online payment attempt for a booking (plan section 6): a booking can have several, e.g. the
        // player's card was declined and they tried again. Amounts in sen (NFR-13).
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('venue_id')->constrained();
            $table->string('gateway', 20); // stripe
            $table->string('account_id', 100); // the venue's Stripe account the money goes to (ADR-024)
            $table->string('checkout_id', 100)->unique(); // the gateway's payment page, e.g. cs_test_...
            $table->unsignedInteger('amount_sen');
            $table->string('currency', 3)->default('myr');
            $table->string('status', 20); // pending, paid, failed, expired
            // Set when the money came in but the booking couldn't simply be confirmed: the amount was wrong,
            // or the booking had already expired or been cancelled. The owner sorts it out (FR-25).
            $table->string('problem', 30)->nullable();
            $table->unsignedInteger('paid_amount_sen')->nullable(); // what the gateway says was paid
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']); // reconciliation: payments still pending after 20 minutes
        });

        // Every signed webhook, saved before it is acted on. The unique event_key (the gateway's event id)
        // means the same webhook sent twice is only acted on once (plan section 9, idempotency).
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 20);
            $table->string('event_key', 150)->unique();
            $table->string('type', 30); // paid, failed, expired
            $table->string('account_id', 100)->nullable();
            $table->json('payload');
            $table->dateTime('processed_at')->nullable(); // null = saved but not acted on yet (it failed half way)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payments');
    }
};
