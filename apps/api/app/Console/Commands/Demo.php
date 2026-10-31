<?php

namespace App\Console\Commands;

use App\Demo\DemoWorld;
use Illuminate\Console\Command;

/**
 * php artisan courtku:demo [--until-done]
 *
 * Brings the demo world up to date now (ADR-030), instead of waiting for the minute tick to do it a
 * few seconds at a time. Needs DEMO_WORLD=true. --until-done keeps going until every day is filled,
 * e.g. on a laptop after `migrate:fresh`.
 */
class Demo extends Command
{
    protected $signature = 'courtku:demo {--until-done : Keep going until every day of every demo venue has its bookings}';

    protected $description = 'Bring the demo venues and their bookings up to date (ADR-030)';

    public function handle(DemoWorld $demo): int
    {
        if (! config('demo.enabled')) {
            $this->warn('The demo world is off. Set DEMO_WORLD=true in .env to switch it on.');

            return self::SUCCESS;
        }

        $total = 0;
        do {
            $done = $demo->step(30);
            $total += $done['bookings'];
            $this->info("Bookings made: {$done['bookings']}. Finished: {$done['finished']}. Days still to fill: {$done['days_left']}.");
        } while ($this->option('until-done') && $done['days_left'] > 0);

        $this->info("Done: {$total} demo bookings made.");

        return self::SUCCESS;
    }
}
