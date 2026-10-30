<?php

namespace App\Services;

use App\Models\Court;
use App\Models\OperatingHour;
use App\Models\Venue;
use App\Models\VenueClosure;
use App\Support\MalaysiaTime;
use App\Support\OpeningHoursRules;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Which half hours players can book at a venue (FR-11, FR-12, FR-13).
 *
 * A half hour (a "cell", ADR-010) is bookable when:
 *   - it is inside the venue's opening hours for that business day (ADR-015, ADR-016),
 *   - the day is not a closure day,
 *   - the day is inside the booking window ("14 days ahead" = today to today + 14),
 *   - it starts at least the venue's notice from now (0 = right up to the start time).
 * Bookings themselves arrive in M3; then a booked cell becomes unavailable too.
 *
 * Every time here is Malaysia time. A cell is "business date + minutes after that date's
 * midnight": at a venue open Friday until 2 am, Saturday 00:30 is Friday, minute 1470.
 */
class AvailabilityService
{
    private const CELL_MINUTES = PricingService::CELL_MINUTES;

    /** @var Collection<int, OperatingHour> */
    private Collection $hours;

    /** @var array<string, VenueClosure|null> closures already looked up, by date */
    private array $closures = [];

    public function __construct(private readonly Venue $venue)
    {
        $this->hours = $venue->operatingHours()->get();
    }

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(MalaysiaTime::TIMEZONE);
    }

    /** "2026-11-06" -> midnight at the start of that day, Malaysia time. */
    public static function date(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date, MalaysiaTime::TIMEZONE)->startOfDay();
    }

    /**
     * The first business date players may look at: yesterday, because just after midnight
     * last night's late hours (e.g. Friday until 2 am) can still be booked.
     */
    public function firstDate(): CarbonImmutable
    {
        return self::now()->startOfDay()->subDay();
    }

    /** The last business date players may book: today + the venue's "days ahead". */
    public function lastDate(): CarbonImmutable
    {
        return self::now()->startOfDay()->addDays($this->venue->booking_window_days);
    }

    public function inWindow(CarbonImmutable $date): bool
    {
        return $date->betweenIncluded($this->firstDate(), $this->lastDate());
    }

    /** The closure covering this date (e.g. Hari Raya), or null when it is a normal day. */
    public function closureOn(CarbonImmutable $date): ?VenueClosure
    {
        $day = $date->toDateString();

        return $this->closures[$day] ??= $this->venue->closures()
            ->where('starts_on', '<=', $day)
            ->where('ends_on', '>=', $day)
            ->first();
    }

    /**
     * Every open cell of this business day, as minutes after its midnight, e.g. 08:00 to 02:00
     * gives 480, 510, ... 1530. Empty on a closure day or a day the venue doesn't open.
     *
     * @return list<int>
     */
    public function openMinutes(CarbonImmutable $date): array
    {
        if ($this->closureOn($date) !== null) {
            return [];
        }

        $minutes = [];
        foreach ($this->hours->where('weekday', $date->dayOfWeek) as $period) {
            $opens = OpeningHoursRules::minutes($period->opens_at);
            $closes = OpeningHoursRules::minutes($period->closes_at) + ($period->closes_next_day ? 1440 : 0);
            for ($minute = $opens; $minute < $closes; $minute += self::CELL_MINUTES) {
                $minutes[] = $minute;
            }
        }
        sort($minutes);

        return $minutes;
    }

    /**
     * The open cells players can still book: those starting at least the notice from now.
     *
     * @return list<int>
     */
    public function bookableMinutes(CarbonImmutable $date): array
    {
        if (! $this->inWindow($date)) {
            return [];
        }

        $earliest = $this->earliestStart();

        return array_values(array_filter(
            $this->openMinutes($date),
            fn (int $minute) => self::startsAt($date, $minute)->greaterThanOrEqualTo($earliest),
        ));
    }

    /** The real time a cell starts: Friday + 1470 minutes = Saturday 00:30. */
    public static function startsAt(CarbonImmutable $date, int $minute): CarbonImmutable
    {
        return $date->startOfDay()->addMinutes($minute);
    }

    /**
     * Checks a booking a player wants and finds its business day.
     * Throws a validation error that says what is wrong, e.g. "The venue closes before 7:00 pm."
     *
     * @return array{CarbonImmutable, int} the business date and the start minute
     */
    public function check(Court $court, CarbonImmutable $startsAt, int $minutes): array
    {
        $located = $this->locate($startsAt);
        if ($located === null) {
            throw ValidationException::withMessages(['starts_at' => 'The venue is closed at that time.']);
        }
        [$date, $startMinute] = $located;

        if (! $this->inWindow($date)) {
            throw ValidationException::withMessages([
                'starts_at' => 'You can book up to '.$this->lastDate()->format('j M Y').'.',
            ]);
        }
        if ($startsAt->lessThan($this->earliestStart())) {
            throw ValidationException::withMessages([
                'starts_at' => $this->venue->min_notice_minutes === 0
                    ? 'That time has already started. Choose a later time.'
                    : 'That is too soon. This venue takes bookings until '.$this->noticeText().' before the start.',
            ]);
        }

        $open = $this->openMinutes($date);
        for ($minute = $startMinute; $minute < $startMinute + $minutes; $minute += self::CELL_MINUTES) {
            if (! in_array($minute, $open, true)) {
                $closesAt = $minute % 1440 === 0 ? 'midnight' : self::startsAt($date, $minute)->format('g:i a');
                throw ValidationException::withMessages([
                    'minutes' => "The venue closes at {$closesAt}. Choose a shorter booking or an earlier time.",
                ]);
            }
        }

        return [$date, $startMinute];
    }

    /**
     * Which business day a real start time belongs to: the same calendar day, or the day
     * before when it falls in last night's hours after midnight. null = the venue is closed then.
     * Opening periods never overlap (ADR-016), so at most one of the two matches.
     *
     * @return array{CarbonImmutable, int}|null
     */
    public function locate(CarbonImmutable $startsAt): ?array
    {
        $day = $startsAt->startOfDay();
        $minute = $startsAt->hour * 60 + $startsAt->minute;

        if (in_array($minute, $this->openMinutes($day), true)) {
            return [$day, $minute];
        }
        $dayBefore = $day->subDay();
        if (in_array($minute + 1440, $this->openMinutes($dayBefore), true)) {
            return [$dayBefore, $minute + 1440];
        }

        return null;
    }

    private function earliestStart(): CarbonImmutable
    {
        return self::now()->addMinutes($this->venue->min_notice_minutes);
    }

    /** 30 -> "30 minutes", 60 -> "1 hour", 1440 -> "1 day" (the settings page's choices). */
    private function noticeText(): string
    {
        $minutes = $this->venue->min_notice_minutes;

        return match (true) {
            $minutes === 1440 => '1 day',
            $minutes % 60 === 0 => ($minutes / 60).' hour'.($minutes === 60 ? '' : 's'),
            default => "{$minutes} minutes",
        };
    }
}
