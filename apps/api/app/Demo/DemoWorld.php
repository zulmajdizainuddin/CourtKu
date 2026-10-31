<?php

namespace App\Demo;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\MalaysianState;
use App\Enums\PaymentMethod;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\VenueStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Services\AvailabilityCache;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\BookingStateMachine;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Keeps the demo world looking like a busy CourtKu (ADR-030), a little at a time from the minute tick:
 *
 *   1. the 20 demo venues and 40 demo players exist (DemoVenues);
 *   2. days older than the history window are removed;
 *   3. games that have ended are finished: most completed (and paid), a few no-shows;
 *   4. between 3 and 4 am, bookings visitors made at demo venues are removed;
 *   5. every day from 30 days ago to 13 days ahead has its bookings, made through the real booking
 *      engine (BookingService) so every rule applies. Past days are booked "the day before", by
 *      moving the clock back while making them.
 *
 * Each step is safe to run again: a day is filled once (demo_days), and nothing outside the demo venues
 * and demo players is ever touched. Nobody is emailed (BookingEmails, Reminders skip demo players).
 */
class DemoWorld
{
    /** Share of a court's open time players book, through the day (hour => weight), before the venue's own busyness. */
    private const DEMAND = [7 => 0.3, 8 => 0.3, 9 => 0.25, 10 => 0.15, 11 => 0.15, 12 => 0.15, 13 => 0.15, 14 => 0.15, 15 => 0.2, 16 => 0.3,
        17 => 0.5, 18 => 0.9, 19 => 1.0, 20 => 1.0, 21 => 0.9, 22 => 0.5, 23 => 0.3, 0 => 0.2, 1 => 0.1];

    public function __construct(
        private readonly BookingService $bookings = new BookingService,
        private readonly BookingStateMachine $machine = new BookingStateMachine,
    ) {}

    public static function isDemoPlayer(?User $user): bool
    {
        return $user !== null && str_ends_with(strtolower($user->email), '@'.DemoVenues::PLAYER_DOMAIN);
    }

    /**
     * Does what's needed, for at most $seconds (config demo.seconds_per_tick).
     *
     * @return array{bookings: int, finished: int, cleared: int, dropped: int, days_left: int}
     */
    public function step(?float $seconds = null): array
    {
        $done = ['bookings' => 0, 'finished' => 0, 'cleared' => 0, 'dropped' => 0, 'days_left' => 0];
        if (! config('demo.enabled')) {
            return $done;
        }

        // Two ticks at once (a slow one and the next) must not fill the same day twice.
        $lock = Cache::lock('demo-world', 300);
        if (! $lock->get()) {
            return $done;
        }

        $clock = Carbon::getTestNow();
        try {
            $deadline = microtime(true) + ($seconds ?? (float) config('demo.seconds_per_tick'));
            $venues = $this->ensureVenues();
            $players = $this->ensurePlayers();
            $done['dropped'] = $this->dropOldDays($venues);
            $done['finished'] = $this->finishEndedGames($venues);
            $done['cleared'] = $this->clearVisitorBookings($venues, $players);
            [$done['bookings'], $done['days_left']] = $this->fillDays($venues, $players, $deadline);
        } finally {
            Carbon::setTestNow($clock); // the real clock again, whatever happened
            $lock->release();
        }

        return $done;
    }

    /** @return Collection<int, Venue> the demo venues, created when missing */
    private function ensureVenues(): Collection
    {
        $definitions = array_slice(DemoVenues::all(), 0, config('demo.venue_limit'));
        $existing = Venue::whereIn('slug', array_column($definitions, 'slug'))->get()->keyBy('slug');

        foreach ($definitions as $definition) {
            $venue = $existing[$definition['slug']] ?? $this->createVenue($definition);
            $existing[$definition['slug']] = $venue;
            if ($definition['online']) {
                $this->connectStripe($venue);
            }
        }

        return new Collection(array_values($existing->all()));
    }

    /** @param  array<string, mixed>  $definition */
    private function createVenue(array $definition): Venue
    {
        return DB::transaction(function () use ($definition) {
            $venue = new Venue([
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'state' => MalaysianState::from($definition['state']),
                'description' => "A made-up venue for trying out CourtKu: book a court to see how it works.\nIts bookings are fictional too, and anything you book here is cleared overnight.",
                'address_line' => $definition['street'],
                'city' => $definition['city'],
                'postcode' => $definition['postcode'],
                'phone' => $definition['phone'],
                'facilities' => $definition['facilities'],
                'slot_minutes' => $definition['slot'],
                'min_minutes' => 60,
                'max_minutes' => 120,
                'booking_window_days' => 14,
                'cancel_cutoff_hours' => 12,
                'booking_emails' => false,
            ]);
            // Not fillable on purpose (only admins approve), so set directly.
            $venue->forceFill(['status' => VenueStatus::Active, 'approved_at' => now(), 'is_demo' => true])->save();

            foreach ($this->openingHours($definition['hours']) as $period) {
                $venue->operatingHours()->create($period);
            }

            $sports = Sport::pluck('id', 'slug');
            foreach ($definition['courts'] as $order => [$name, $sport, $indoor, $price]) {
                /** @var Court $court */
                $court = $venue->courts()->create(['name' => $name, 'is_indoor' => $indoor, 'base_price_sen' => $price, 'sort_order' => $order]);
                $court->sports()->attach($sports[$sport]);
                // Evenings on weekdays cost a third more (FR-10), rounded to 10 sen (ADR-017).
                $venue->priceRules()->create([
                    'court_id' => $court->id,
                    'name' => 'Weekday evenings',
                    'weekdays' => [1, 2, 3, 4, 5],
                    'starts_at' => '18:00',
                    'ends_at' => '23:00',
                    'ends_next_day' => false,
                    'price_sen' => (int) (round($price * 4 / 3 / 10) * 10),
                ]);
            }

            return $venue;
        });
    }

    /** @return list<array{weekday: int, opens_at: string, closes_at: string, closes_next_day: bool}> */
    private function openingHours(string $profile): array
    {
        $day = fn (int $weekday, string $opens, string $closes, bool $nextDay) => ['weekday' => $weekday, 'opens_at' => $opens, 'closes_at' => $closes, 'closes_next_day' => $nextDay];

        return match ($profile) {
            'late' => [...array_map(fn ($d) => $day($d, '08:00', '00:00', true), [0, 1, 2, 3, 4]), $day(5, '08:00', '01:00', true), $day(6, '08:00', '01:00', true)],
            'friday_break' => [...array_map(fn ($d) => $day($d, '08:00', '00:00', true), [0, 1, 2, 3, 4, 6]), $day(5, '08:00', '12:30', false), $day(5, '14:30', '00:00', true)],
            'early' => array_map(fn ($d) => $day($d, '07:00', '23:00', false), range(0, 6)),
            default => array_map(fn ($d) => $day($d, '08:00', '00:00', true), range(0, 6)),
        };
    }

    /** The one online venue takes test-card payments into CourtKu's own Test Venue Stripe account (config demo.stripe_account). */
    private function connectStripe(Venue $venue): void
    {
        $account = (string) config('demo.stripe_account');
        if ($account === '' || $venue->paymentAccount()->exists()) {
            return;
        }

        $row = new VenuePaymentAccount;
        $row->forceFill([
            'venue_id' => $venue->id,
            'gateway' => 'stripe',
            'account_id' => $account,
            'account_name' => 'CourtKu demo',
            'charges_enabled' => true,
            'is_live' => false,
            'checked_at' => now(),
        ])->save();
        $venue->forceFill(['payment_mode' => PaymentMode::Both])->save();
    }

    /** @return Collection<int, User> */
    private function ensurePlayers(): Collection
    {
        $players = DemoVenues::players();
        $emails = array_map(fn (array $player) => "{$player[1]}@".DemoVenues::PLAYER_DOMAIN, $players);
        $existing = User::whereIn('email', $emails)->pluck('email')->all();

        foreach ($players as $i => [$name]) {
            if (! in_array($emails[$i], $existing, true)) {
                $user = new User(['name' => $name, 'email' => $emails[$i], 'phone' => '+6012000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT), 'password' => Str::random(40)]);
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        return User::whereIn('email', $emails)->orderBy('id')->get();
    }

    /**
     * The history window: older days go, bookings and all.
     *
     * @param  Collection<int, Venue>  $venues
     */
    private function dropOldDays(Collection $venues): int
    {
        $before = CarbonImmutable::parse(MalaysiaTime::today())->subDays((int) config('demo.days_back'))->toDateString();
        $ids = Booking::whereIn('venue_id', $venues->modelKeys())->where('business_date', '<', $before)->limit(2000)->pluck('id')->all();
        $this->deleteBookings($ids);
        DB::table('demo_days')->whereIn('venue_id', $venues->modelKeys())->where('business_date', '<', $before)->delete();
        if ($ids !== []) {
            $this->forgetGrids($venues);
        }

        return count($ids);
    }

    /**
     * Games that have ended: about 1 in 20 a no-show, the rest checked in and completed. Players who booked
     * to pay at the venue paid at the counter. Set in bulk: the state machine would add nothing here.
     *
     * @param  Collection<int, Venue>  $venues
     */
    private function finishEndedGames(Collection $venues): int
    {
        $ended = Booking::whereIn('venue_id', $venues->modelKeys())
            ->where('status', BookingStatus::Confirmed->value)
            ->where('ends_at', '<=', now())
            ->limit(1000)
            ->get(['id', 'code', 'starts_at', 'ends_at', 'paid_at']);

        foreach ($ended as $booking) {
            $roll = crc32($booking->code) % 100;
            if ($roll < 5) {
                Booking::whereKey($booking->id)->update(['status' => BookingStatus::NoShow->value]);

                continue;
            }
            Booking::whereKey($booking->id)->update(array_filter([
                'status' => BookingStatus::Completed->value,
                'checked_in_at' => $booking->starts_at->copy()->subMinutes(5 + $roll % 10),
                'paid_at' => $booking->paid_at === null ? $booking->starts_at->copy()->subMinutes(5) : null,
                'payment_method' => $booking->paid_at === null ? [PaymentMethod::Cash, PaymentMethod::Qr, PaymentMethod::Card][$roll % 3]->value : null,
            ]));
        }
        if ($ended->isNotEmpty()) {
            $this->forgetGrids($venues);
        }

        return $ended->count();
    }

    /**
     * Between 3 and 4 am in Malaysia: what visitors booked at demo venues before 3 am goes, so the demo starts clean each day.
     *
     * @param  Collection<int, Venue>  $venues
     * @param  Collection<int, User>  $players
     */
    private function clearVisitorBookings(Collection $venues, Collection $players): int
    {
        $now = CarbonImmutable::now(MalaysiaTime::TIMEZONE);
        if ($now->hour !== 3) {
            return 0;
        }

        $ids = Booking::whereIn('venue_id', $venues->modelKeys())
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', $players->modelKeys())
            ->where('created_at', '<', $now->setTime(3, 0))
            ->limit(2000)
            ->pluck('id')
            ->all();
        $this->deleteBookings($ids);
        if ($ids !== []) {
            $this->forgetGrids($venues);
        }

        return count($ids);
    }

    /**
     * Fills days that have no bookings yet, nearest to today first, until the deadline.
     *
     * @param  Collection<int, Venue>  $venues
     * @param  Collection<int, User>  $players
     * @return array{0: int, 1: int} bookings made, and days still to fill
     */
    private function fillDays(Collection $venues, Collection $players, float $deadline): array
    {
        $today = CarbonImmutable::parse(MalaysiaTime::today(), MalaysiaTime::TIMEZONE);
        $dates = [];
        for ($d = -(int) config('demo.days_back'); $d <= (int) config('demo.days_ahead'); $d++) {
            $dates[] = $today->addDays($d);
        }
        usort($dates, fn (CarbonImmutable $a, CarbonImmutable $b) => abs($a->diffInDays($today)) <=> abs($b->diffInDays($today)));

        $filled = DB::table('demo_days')->whereIn('venue_id', $venues->modelKeys())
            ->get(['venue_id', 'business_date'])
            ->map(fn ($row) => "{$row->venue_id}:".substr((string) $row->business_date, 0, 10))
            ->flip();

        $todo = [];
        foreach ($dates as $date) {
            foreach ($venues as $venue) {
                if (! isset($filled["{$venue->id}:{$date->toDateString()}"])) {
                    $todo[] = [$venue, $date];
                }
            }
        }

        $made = 0;
        $realNow = CarbonImmutable::now();
        foreach ($todo as $i => [$venue, $date]) {
            if (microtime(true) >= $deadline) {
                return [$made, count($todo) - $i];
            }
            $made += $this->fillDay($venue, $date, $players, $realNow);
            DB::table('demo_days')->insertOrIgnore(['venue_id' => $venue->id, 'business_date' => $date->toDateString(), 'created_at' => $realNow]);
        }

        return [$made, 0];
    }

    /**
     * One venue's bookings for one day, made through the real booking engine. Returns how many.
     *
     * @param  Collection<int, User>  $players
     */
    private function fillDay(Venue $venue, CarbonImmutable $date, Collection $players, CarbonImmutable $realNow): int
    {
        $availability = new AvailabilityService($venue);
        $open = $availability->openMinutes($date);
        if ($open === [] || $players->isEmpty()) {
            return 0;
        }

        // The same day always gets the same bookings: the dice are seeded with the venue and the date.
        $dice = new Randomizer(new Mt19937(crc32("{$venue->slug}:{$date->toDateString()}")));
        $busy = (float) collect(DemoVenues::all())->firstWhere('slug', $venue->slug)['busy'];
        $weekend = in_array($date->dayOfWeek, [0, 6], true) ? 1.25 : 1.0;
        $online = $venue->acceptsOnlinePayment();

        // Booked "the day before" (or now, for days ahead), so the engine accepts the time.
        $bookedAt = $date->subDay()->setTime(9, 0)->addMinutes($dice->getInt(0, 600))->utc();
        Carbon::setTestNow($bookedAt->lessThan($realNow) ? $bookedAt : $realNow);

        $openSet = array_flip($open);
        $made = 0;
        foreach ($venue->courts()->bookable()->with('sports')->get() as $court) {
            $minute = $open[0];
            $last = end($open);
            while ($minute <= $last) {
                if (! isset($openSet[$minute])) {
                    $minute += 30;

                    continue;
                }
                $chance = $busy * $weekend * (self::DEMAND[intdiv($minute, 60) % 24] ?? 0.1);
                $length = [60, 60, 90, 120][$dice->getInt(0, 3)];
                if ($length % $venue->slot_minutes !== 0 || $dice->getFloat(0, 1) >= $chance) {
                    $minute += 60;

                    continue;
                }
                if ($this->book($venue, $court, $date, $minute, $length, $players, $dice, $online)) {
                    $made++;
                    $minute += $length;
                } else {
                    $minute += 30;
                }
            }
        }

        return $made;
    }

    /**
     * @param  Collection<int, User>  $players
     */
    private function book(Venue $venue, Court $court, CarbonImmutable $date, int $minute, int $length, Collection $players, Randomizer $dice, bool $online): bool
    {
        try {
            $draft = $this->bookings->prepare($court, AvailabilityService::startsAt($date, $minute), $length);
        } catch (ApiException|ValidationException) {
            return false; // past closing, a break, too long: the engine said no, as it would to a player
        }

        $kind = $dice->getInt(1, 100);
        /** @var User $player */
        $player = $players[$dice->getInt(0, $players->count() - 1)];
        $walkIn = $kind > 85;
        $name = DemoVenues::walkIns()[$dice->getInt(0, count(DemoVenues::walkIns()) - 1)];

        try {
            $booking = $this->bookings->store(
                $draft,
                $walkIn ? ($kind > 95 ? BookingSource::Phone : BookingSource::WalkIn) : BookingSource::Online,
                player: $walkIn ? null : $player,
                createdBy: $walkIn ? null : $player,
                sportId: $court->sports->first()?->id,
                customerName: $walkIn ? $name : null,
                customerPhone: $walkIn ? '+60120000'.str_pad((string) $dice->getInt(0, 999), 3, '0', STR_PAD_LEFT) : null,
            );
        } catch (ApiException|ValidationException) {
            return false; // taken meanwhile, e.g. by a visitor
        }

        // A few players cancel; at the online venue, many pay online.
        if (! $walkIn && $kind <= 5) {
            $this->machine->transition($booking, BookingStatus::Cancelled, $player, 'Cancelled by the player');
        } elseif (! $walkIn && $online && $kind <= 45) {
            $this->payOnline($venue, $booking);
        }

        return true;
    }

    /** A demo online payment: recorded like a real one, without Stripe (gateway "demo"). */
    private function payOnline(Venue $venue, Booking $booking): void
    {
        $paidAt = now()->addMinutes(2);
        $booking->forceFill(['paid_at' => $paidAt, 'payment_method' => PaymentMethod::Online])->save();
        (new Payment)->forceFill([
            'booking_id' => $booking->id,
            'venue_id' => $venue->id,
            'gateway' => 'demo',
            'account_id' => (string) $venue->paymentAccount?->account_id,
            'checkout_id' => "demo_{$booking->code}",
            'amount_sen' => $booking->total_sen,
            'status' => PaymentStatus::Paid,
            'paid_amount_sen' => $booking->total_sen,
            'paid_at' => $paidAt,
        ])->save();
    }

    /** @param  list<int>  $ids  Removed with everything that points at them (no cascades on bookings). */
    private function deleteBookings(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        DB::transaction(function () use ($ids) {
            $payments = DB::table('payments')->whereIn('booking_id', $ids)->pluck('id');
            DB::table('refunds')->whereIn('payment_id', $payments)->delete();
            DB::table('payments')->whereIn('id', $payments)->delete();
            DB::table('booking_slots')->whereIn('booking_id', $ids)->delete();
            DB::table('audit_logs')->where('subject_type', (new Booking)->getMorphClass())->whereIn('subject_id', $ids)->delete();
            DB::table('bookings')->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Plain queries send no "changed" signal: tell the time grids (ADR-028).
     *
     * @param  Collection<int, Venue>  $venues
     */
    private function forgetGrids(Collection $venues): void
    {
        foreach ($venues as $venue) {
            AvailabilityCache::forget($venue->id);
        }
    }
}
