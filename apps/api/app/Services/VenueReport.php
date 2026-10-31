<?php

namespace App\Services;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Venue;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * A venue's report for a range of business days (FR-32): what was booked, what came in, which days
 * were busy and how full each court was. Dates are business days (ADR-015): a Friday-night game that
 * runs past midnight belongs to Friday.
 *
 * Which bookings count:
 *   booked     = confirmed, checked in, completed or no-show: the court was taken for them;
 *   cancelled  = counted on their own and never as booked;
 *   left out   = unpaid holds (still waiting, or ran out): nothing was booked.
 */
class VenueReport
{
    /** At most a year (and a day, for leap years) at once, so a report stays quick. */
    public const MAX_DAYS = 366;

    private const BOOKED = [BookingStatus::Confirmed, BookingStatus::CheckedIn, BookingStatus::Completed, BookingStatus::NoShow];

    /** @var Collection<int, Booking>|null */
    private ?Collection $bookings = null;

    public function __construct(
        private readonly Venue $venue,
        private readonly CarbonImmutable $from,
        private readonly CarbonImmutable $to,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $bookings = $this->bookings();
        $booked = $bookings->filter(fn (Booking $b) => in_array($b->status, self::BOOKED, true));

        // FR-22, FR-25: what came in online, and what the owner gave back, for these days' bookings.
        $online = Payment::query()
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->where('payments.venue_id', $this->venue->id)
            ->whereBetween('bookings.business_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->whereIn('payments.status', [PaymentStatus::Paid->value, PaymentStatus::Refunded->value])
            ->selectRaw('COALESCE(SUM(payments.paid_amount_sen), 0) AS paid, COALESCE(SUM(payments.refunded_sen), 0) AS refunded')
            ->toBase()
            ->first();
        $paidOnline = (int) ($online->paid ?? 0);
        $refunded = (int) ($online->refunded ?? 0);

        // FR-28: paid at the counter (cash, QR or card).
        $paidAtVenue = $booked
            ->filter(fn (Booking $b) => $b->paid_at !== null && $b->payment_method !== PaymentMethod::Online)
            ->sum('total_sen');

        // Still to be paid at the counter. A no-show that never paid isn't coming in.
        $toCollect = $booked
            ->filter(fn (Booking $b) => $b->paid_at === null && $b->status !== BookingStatus::NoShow)
            ->sum('total_sen');

        $openHours = $this->openHours();

        return [
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            'totals' => [
                'bookings' => $booked->count(),
                'hours_booked' => $this->hours($booked),
                'booked_sen' => (int) $booked->sum('total_sen'),
                'online_bookings' => $booked->where('source', BookingSource::Online)->count(),
                'counter_bookings' => $booked->where('source', '!==', BookingSource::Online)->count(),
                'cancelled' => $bookings->where('status', BookingStatus::Cancelled)->count(),
                'no_shows' => $booked->where('status', BookingStatus::NoShow)->count(),
                'paid_online_sen' => $paidOnline,
                'paid_at_venue_sen' => (int) $paidAtVenue,
                'refunded_sen' => $refunded,
                'collected_sen' => $paidOnline + (int) $paidAtVenue - $refunded,
                'to_collect_sen' => (int) $toCollect,
            ],
            'days' => $this->days($booked),
            'courts' => $this->courts($booked, $openHours),
            'open_hours_per_court' => $openHours,
        ];
    }

    /**
     * Every booking of these days except unpaid holds, oldest first: the CSV download's rows.
     *
     * @return Collection<int, Booking>
     */
    public function bookings(): Collection
    {
        return $this->bookings ??= $this->venue->bookings()
            ->whereBetween('business_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->whereNotIn('status', [BookingStatus::PendingPayment->value, BookingStatus::Expired->value])
            ->with(['court', 'sport', 'user', 'payments'])
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * One entry per day, days without bookings included, for the bar chart.
     *
     * @param  Collection<int, Booking>  $booked
     * @return list<array{date: string, bookings: int, booked_sen: int}>
     */
    private function days(Collection $booked): array
    {
        $byDay = $booked->groupBy(fn (Booking $b) => $b->business_date->toDateString());

        $days = [];
        for ($day = $this->from; $day->lessThanOrEqualTo($this->to); $day = $day->addDay()) {
            $date = $day->toDateString();
            $days[] = [
                'date' => $date,
                'bookings' => isset($byDay[$date]) ? $byDay[$date]->count() : 0,
                'booked_sen' => isset($byDay[$date]) ? (int) $byDay[$date]->sum('total_sen') : 0,
            ];
        }

        return $days;
    }

    /**
     * Per court: bookings, hours and money, and how full it was: hours booked out of hours open.
     * Courts switched off with no bookings in the range are left out.
     *
     * @param  Collection<int, Booking>  $booked
     * @return list<array<string, mixed>>
     */
    private function courts(Collection $booked, float $openHours): array
    {
        $byCourt = $booked->groupBy('court_id');

        return $this->venue->courts()->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn ($court) => $court->is_active || isset($byCourt[$court->id]))
            ->map(function ($court) use ($byCourt, $openHours) {
                $bookings = $byCourt[$court->id] ?? new Collection;
                $hours = $this->hours($bookings);

                return [
                    'id' => $court->id,
                    'name' => $court->name,
                    'is_active' => $court->is_active,
                    'bookings' => $bookings->count(),
                    'hours_booked' => $hours,
                    'booked_sen' => (int) $bookings->sum('total_sen'),
                    'full_percent' => $openHours > 0 ? (int) round(min(100, $hours / $openHours * 100)) : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Hours the venue was open on these days (the same for every court), from its opening hours,
     * skipping closed dates (FR-21). Times blocked for repairs still count as open.
     */
    private function openHours(): float
    {
        $availability = new AvailabilityService($this->venue);
        $cells = 0;
        for ($day = $this->from; $day->lessThanOrEqualTo($this->to); $day = $day->addDay()) {
            $cells += count($availability->openMinutes($day));
        }

        return $cells * PricingService::CELL_MINUTES / 60;
    }

    /** @param  Collection<int, Booking>  $bookings */
    private function hours(Collection $bookings): float
    {
        $minutes = $bookings->sum(fn (Booking $b) => (int) $b->starts_at->diffInMinutes($b->ends_at));

        return round($minutes / 60, 1);
    }
}
