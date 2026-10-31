<?php

namespace App\Console\Commands;

use App\Payments\CheckoutRequest;
use App\Payments\StripeGateway;
use App\Support\MalaysiaTime;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * php artisan courtku:stripe-try            opens a RM 10.00 test payment page and prints its link
 * php artisan courtku:stripe-try --show=cs_test_...    how that page stands now (open, paid, expired)
 * php artisan courtku:stripe-try --expire=cs_test_...  closes it, like when a hold runs out
 *
 * Tries StripeGateway against the real Stripe test mode with STRIPE_SECRET_KEY from .env (ADR-023).
 * Test mode only: a live key (sk_live_) is refused, so no real money can move.
 */
class StripeTry extends Command
{
    protected $signature = 'courtku:stripe-try {--account= : A connected venue account (acct_...); none = CourtKu\'s own} {--show= : A payment page id to look up} {--expire= : A payment page id to close}';

    protected $description = 'Try the Stripe gateway in test mode: open, look up or close a payment page';

    public function handle(): int
    {
        $key = (string) config('services.stripe.secret');
        if (! str_starts_with($key, 'sk_test_')) {
            $this->error('Set STRIPE_SECRET_KEY in apps/api/.env to your Stripe test key (it starts with sk_test_).');

            return self::FAILURE;
        }
        $stripe = new StripeGateway($key);
        $account = is_string($this->option('account')) ? $this->option('account') : null;

        $checkout = match (true) {
            is_string($this->option('show')) => $stripe->findCheckout($account, $this->option('show')),
            is_string($this->option('expire')) => $stripe->expireCheckout($account, $this->option('expire')),
            default => $stripe->createCheckout($account, new CheckoutRequest(
                bookingCode: 'CK-TEST1',
                amountSen: 1000,
                description: 'CourtKu test payment (no real money)',
                customerEmail: 'customer1@example.com',
                returnUrl: rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/').'/',
                expiresAt: CarbonImmutable::now()->addMinutes(15),
                idempotencyKey: 'try-'.Str::uuid(),
            )),
        };

        $this->table(['', ''], [
            ['Id', $checkout->id],
            ['Status', $checkout->status->value],
            ['Paid', $checkout->paid ? 'yes' : 'no'],
            ['Amount', Money::format($checkout->amountSen).' ('.strtoupper($checkout->currency).')'],
            ['Booking code', $checkout->bookingCode ?? '-'],
            ['Closes at', $checkout->expiresAt?->setTimezone(MalaysiaTime::TIMEZONE)->format('j M Y, g:i a') ?? '-'],
        ]);
        if ($checkout->url !== null) {
            $this->line('Pay here (test card 4242 4242 4242 4242, any future date, any CVC):');
            $this->line($checkout->url);
        }

        return self::SUCCESS;
    }
}
