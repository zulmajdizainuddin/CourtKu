<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\ApiException;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Venue;
use App\Support\MalaysiaTime;
use App\Support\OpeningHoursRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * FR-38: settings never strand bookings. Changing the opening hours, adding a closure or switching
 * a court off is refused while upcoming bookings would no longer fit, and the owner gets the list
 * of those bookings to cancel first (plan section 7, "Settings never orphan bookings").
 *
 * "Upcoming" = confirmed or checked in, and not over yet. Past, cancelled and no-show bookings
 * are history and never block a change.
 */
class BookingConflicts
{
    private const CELL_MINUTES = PricingService::CELL_MINUTES;

    /**
     * Upcoming bookings with a half hour outside these new opening periods (on their business day, ADR-015).
     *
     * @param  list<array{weekday: int, opens_at: string, closes_at: string, closes_next_day: bool}>  $periods
     * @return Collection<int, Booking>
     */
    public function outsideHours(Venue $venue, array $periods): Collection
    {
        // Every open half hour per weekday, as minutes after that day's midnight (past 1440 after midnight).
        $open = [];
        foreach ($periods as $period) {
            $opens = OpeningHoursRules::minutes($period['opens_at']);
            $closes = OpeningHoursRules::minutes($period['closes_at']) + ($period['closes_next_day'] ? 1440 : 0);
            for ($minute = $opens; $minute < $closes; $minute += self::CELL_MINUTES) {
                $open[$period['weekday']][$minute] = true;
            }
        }

        return $this->upcoming()->where('venue_id', $venue->id)->get()->filter(function (Booking $booking) use ($open) {
            $day = AvailabilityService::date($booking->business_date->toDateString());
            $start = (int) $day->diffInMinutes($booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE));
            $length = (int) $booking->starts_at->diffInMinutes($booking->ends_at);
            for ($minute = $start; $minute < $start + $length; $minute += self::CELL_MINUTES) {
                if (! isset($open[$day->dayOfWeek][$minute])) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * Upcoming bookings on these business days, e.g. a closure from 30 Mar to 1 Apr.
     *
     * @return Collection<int, Booking>
     */
    public function onDays(Venue $venue, string $from, string $to): Collection
    {
        return $this->upcoming()->where('venue_id', $venue->id)->whereBetween('business_date', [$from, $to])->get();
    }

    /**
     * Upcoming bookings on this court, e.g. before switching it off.
     *
     * @return Collection<int, Booking>
     */
    public function onCourt(Court $court): Collection
    {
        return $this->upcoming()->where('court_id', $court->id)->get();
    }

    /**
     * 409 BOOKINGS_CONFLICT with the bookings in the way, for the owner to cancel or move first.
     * In $what, "{is}" becomes "is" for one booking and "are" for several.
     *
     * @param  Collection<int, Booking>  $bookings
     */
    public static function refuse(Collection $bookings, string $what, string $then): ApiException
    {
        $count = $bookings->count();
        $noun = $count === 1 ? '1 upcoming booking' : "{$count} upcoming bookings";
        $what = str_replace('{is}', $count === 1 ? 'is' : 'are', $what);

        return new ApiException(
            'BOOKINGS_CONFLICT',
            "{$noun} {$what}. Cancel ".($count === 1 ? 'it' : 'them')." first from the Schedule, then {$then}.",
            details: ['bookings' => $bookings->map(fn (Booking $b) => [
                'code' => $b->code,
                'court' => $b->court->name,
                'starts_at' => $b->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
                'business_date' => $b->business_date->toDateString(),
                'customer' => $b->user->name ?? $b->customer_name,
            ])->values()->all()],
        );
    }

    /**
     * @return Builder<Booking>
     */
    private function upcoming(): Builder
    {
        return Booking::query()
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::CheckedIn->value])
            ->where('ends_at', '>', now())
            ->with(['court', 'user'])
            ->orderBy('starts_at');
    }
}
