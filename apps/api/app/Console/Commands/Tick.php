<?php

namespace App\Console\Commands;

use App\Services\Housekeeping;
use Illuminate\Console\Command;

/**
 * php artisan courtku:tick
 *
 * The every-minute jobs (ADR-022): expire unpaid holds, complete finished bookings, check payments
 * and send queued emails (ADR-027).
 * The scheduler runs it every minute (`php artisan schedule:work` locally); on Render's free plan
 * a cron service calls POST /api/tick instead, which does the same.
 */
class Tick extends Command
{
    protected $signature = 'courtku:tick';

    protected $description = 'Expire unpaid holds and complete finished bookings (runs every minute)';

    public function handle(Housekeeping $housekeeping): int
    {
        $done = $housekeeping->run();
        $this->info("Expired holds: {$done['expired_holds']}. Completed bookings: {$done['completed_bookings']}. Retried webhooks: {$done['retried_webhooks']}. Reconciled payments: {$done['reconciled_payments']}. Emails sent: {$done['emails_sent']}.");

        return self::SUCCESS;
    }
}
