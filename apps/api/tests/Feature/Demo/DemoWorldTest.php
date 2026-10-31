<?php

use App\Demo\DemoVenues;
use App\Demo\DemoWorld;
use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use App\Services\BookingService;
use App\Services\Housekeeping;
use App\Services\Reminders;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/*
| ADR-030: the demo world. Here with 2 venues and 2 days either side of today, to stay quick.
| Now is Monday 2 Nov 2026, 10:00 in Malaysia (02:00 UTC).
*/

beforeEach(function () {
    $this->travelTo('2026-11-02 02:00:00');
    config(['demo.enabled' => true, 'demo.venue_limit' => 2, 'demo.days_back' => 2, 'demo.days_ahead' => 2, 'demo.seconds_per_tick' => 120]);
});

function demoVenues()
{
    return Venue::where('is_demo', true)->orderBy('id')->get();
}

it('does nothing while switched off', function () {
    config(['demo.enabled' => false]);

    expect((new DemoWorld)->step()['bookings'])->toBe(0)
        ->and(Venue::count())->toBe(0);
});

it('ADR-030 builds labelled venues and fills every day of the window through the real engine', function () {
    Notification::fake();

    $done = (new DemoWorld)->step();

    $venues = demoVenues();
    expect($venues)->toHaveCount(2)
        ->and($venues->pluck('name')->all())->each->toEndWith('(Demo)')
        ->and($venues->every(fn (Venue $v) => $v->status->value === 'active' && $v->courts()->count() > 0 && $v->operatingHours()->count() > 0))->toBeTrue()
        ->and(User::where('email', 'like', '%@'.DemoVenues::PLAYER_DOMAIN)->count())->toBe(40)
        ->and(DB::table('demo_days')->count())->toBe(10) // 2 venues x 5 days
        ->and($done['bookings'])->toBeGreaterThan(20)
        ->and($done['days_left'])->toBe(0)
        ->and(Booking::count())->toBe($done['bookings']);

    // Every day has bookings, at prices from the price list, each holding its own half hours.
    foreach (['2026-10-31', '2026-11-01', '2026-11-02', '2026-11-03', '2026-11-04'] as $day) {
        expect(Booking::where('business_date', $day)->count())->toBeGreaterThan(0);
    }
    $kept = Booking::whereNotIn('status', [BookingStatus::Cancelled->value, BookingStatus::Expired->value])->get();
    expect(DB::table('booking_slots')->whereNotNull('booking_id')->count())->toBe((int) $kept->sum(fn (Booking $b) => $b->starts_at->diffInMinutes($b->ends_at) / 30))
        ->and($kept->every(fn (Booking $b) => $b->total_sen > 0))->toBeTrue();

    // Nobody is emailed, and nothing waits in the queue.
    Notification::assertNothingSent();
    expect(DB::table('jobs')->count())->toBe(0)
        ->and((new Reminders)->queueDue())->toBe(0);
});

it('finishes games that have ended: completed and paid, or a no-show', function () {
    $world = new DemoWorld;
    $world->step();
    $world->step(); // the next minute finishes what the first one booked in the past

    $ended = Booking::where('ends_at', '<=', now())->whereNot('status', BookingStatus::Cancelled->value)->get();
    expect($ended)->not->toBeEmpty()
        // About 1 in 20 is a no-show (it depends on the booking code), the rest completed: nothing is left waiting.
        ->and($ended->every(fn (Booking $b) => in_array($b->status, [BookingStatus::Completed, BookingStatus::NoShow], true)))->toBeTrue()
        ->and($ended->where('status', BookingStatus::Completed)->count())->toBeGreaterThan($ended->count() / 2)
        ->and($ended->where('status', BookingStatus::Completed)->every(fn (Booking $b) => $b->paid_at !== null && $b->checked_in_at !== null))->toBeTrue()
        ->and(Booking::where('ends_at', '>', now())->where('status', BookingStatus::Completed->value)->count())->toBe(0);
});

it('does each day once, and rolls the window forward each day', function () {
    $world = new DemoWorld;
    $world->step();
    expect($world->step()['bookings'])->toBe(0);

    $this->travelTo('2026-11-03 02:00:00'); // a day later
    $done = $world->step();

    expect($done['dropped'])->toBeGreaterThan(0)
        ->and(Booking::where('business_date', '2026-10-31')->count())->toBe(0) // older than 2 days: gone
        ->and(Booking::where('business_date', '2026-11-05')->count())->toBeGreaterThan(0) // the new day ahead
        ->and(DB::table('demo_days')->count())->toBe(10);
});

it('clears what visitors booked at demo venues between 3 and 4 am, and nothing else', function () {
    $world = new DemoWorld;
    $world->step();
    $visitor = User::factory()->create(['email' => 'visitor@gmail.com']);
    $court = demoVenues()->first()->courts()->bookable()->firstOrFail();
    $service = new BookingService;

    // A free hour on Wednesday morning.
    $booking = null;
    foreach (['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00'] as $time) {
        try {
            $booking = $service->store($service->prepare($court, CarbonImmutable::parse("2026-11-04T{$time}:00+08:00"), 60), BookingSource::Online, player: $visitor);
            break;
        } catch (ApiException|ValidationException) {
            // taken by a demo booking, or before opening: try the next hour
        }
    }
    expect($booking)->not->toBeNull();
    $demoBookings = Booking::where('user_id', '!=', $visitor->id)->count();

    $world->step(); // 10 am: kept
    expect(Booking::find($booking->id))->not->toBeNull();

    $this->travelTo(CarbonImmutable::parse('2026-11-03 03:10', 'Asia/Kuala_Lumpur')->utc());
    expect($world->step()['cleared'])->toBe(1)
        ->and(Booking::find($booking->id))->toBeNull()
        ->and(Booking::where('user_id', '!=', $visitor->id)->whereNotNull('user_id')->count())->toBeGreaterThan(0)
        ->and($demoBookings)->toBeGreaterThan(0);
});

it('lets the online demo venue take test-card payments into the Test Venue\'s Stripe account, which it shares', function () {
    config(['demo.stripe_account' => 'acct_demo_test']);
    // CourtKu's own Test Venue connected this account first, as on the live site.
    $testVenue = Venue::factory()->active()->create(['payment_mode' => 'both']);
    (new VenuePaymentAccount)->forceFill(['venue_id' => $testVenue->id, 'gateway' => 'stripe', 'account_id' => 'acct_demo_test', 'charges_enabled' => true, 'is_live' => false])->save();

    (new DemoWorld)->step();

    $online = Venue::where('slug', DemoVenues::all()[0]['slug'])->firstOrFail();
    expect($online->acceptsOnlinePayment())->toBeTrue()
        ->and($online->paymentAccount->account_id)->toBe('acct_demo_test')
        ->and(Payment::where('venue_id', $online->id)->where('gateway', 'demo')->where('status', 'paid')->count())->toBeGreaterThan(0)
        ->and(demoVenues()->last()->acceptsOnlinePayment())->toBeFalse();
});

it('never lets a broken demo step stop the real every-minute jobs', function () {
    $broken = new class extends DemoWorld
    {
        public function step(?float $seconds = null): array
        {
            throw new RuntimeException('demo world broke');
        }
    };
    Exceptions::fake();

    $done = (new Housekeeping(demo: $broken))->run();

    expect($done['demo_bookings'])->toBe(0)
        ->and($done)->toHaveKeys(['expired_holds', 'reminders', 'emails_sent']);
    Exceptions::assertReported(fn (RuntimeException $e) => $e->getMessage() === 'demo world broke');
});

it('takes no online payments when no Stripe account is configured', function () {
    (new DemoWorld)->step();

    expect(demoVenues()->contains(fn (Venue $v) => $v->acceptsOnlinePayment()))->toBeFalse()
        ->and(Payment::count())->toBe(0);
});

it('tells everyone on the public page that a demo venue is made up', function () {
    (new DemoWorld)->step();

    $this->getJson('/api/v1/venues/'.demoVenues()->first()->slug)->assertOk()->assertJsonPath('data.is_demo', true);
});

it('never emails example.com addresses on the live site', function () {
    $this->app['env'] = 'production';
    $note = new class extends BaseNotification
    {
        public function via(object $notifiable): array
        {
            return ['mail'];
        }

        public function toMail(object $notifiable): MailMessage
        {
            return (new MailMessage)->line('Hello');
        }
    };

    User::factory()->create(['email' => 'aina.rahman@demo.example.com'])->notify($note);
    User::factory()->create(['email' => 'player@example.com'])->notify($note);
    Notification::route('mail', 'someone@example.com')->notify($note);
    User::factory()->create(['email' => 'real.player@gmail.com'])->notify($note);

    // Only the real address got an email (the test mailer keeps what it "sent").
    $sent = Mail::getSymfonyTransport()->messages();
    expect($sent)->toHaveCount(1)
        ->and($sent[0]->getEnvelope()->getRecipients()[0]->getAddress())->toBe('real.player@gmail.com');
});
