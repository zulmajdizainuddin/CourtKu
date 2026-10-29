<?php

namespace App\Support;

/**
 * The rules for a venue's weekly opening periods (FR-07, FR-37, ADR-016):
 * at most two periods per weekday, and no two periods may overlap, including a late
 * night that runs into the next morning (Friday until 02:00 vs Saturday from 01:00),
 * and Saturday night running into Sunday.
 *
 * Every period is turned into "minutes since Sunday 00:00" so all of these become
 * one simple check: do two number ranges overlap?
 */
final class OpeningHoursRules
{
    private const MINUTES_PER_DAY = 1440;

    private const MINUTES_PER_WEEK = 10080;

    public const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    /**
     * What is wrong with these periods, as field => message ("periods.3.opens_at" => "...").
     *
     * @param  list<array{weekday: int, opens_at: string, closes_at: string}>  $periods
     * @return array<string, string>
     */
    public static function problems(array $periods): array
    {
        $problems = [];

        $periodsPerDay = [];
        foreach ($periods as $index => $period) {
            $periodsPerDay[$period['weekday']][] = $index;
        }
        foreach ($periodsPerDay as $weekday => $indexes) {
            if (count($indexes) > 2) {
                $problems["periods.{$indexes[2]}.weekday"] = self::DAY_NAMES[$weekday].' can have at most two opening periods.';
            }
        }

        $spans = array_map(self::span(...), $periods);
        foreach ($spans as $i => [$start, $end]) {
            foreach ($spans as $j => [$otherStart, $otherEnd]) {
                if ($j <= $i) {
                    continue;
                }
                // Also compare one week later and earlier, so Saturday night meets Sunday morning.
                foreach ([0, self::MINUTES_PER_WEEK, -self::MINUTES_PER_WEEK] as $shift) {
                    if ($start < $otherEnd + $shift && $otherStart + $shift < $end) {
                        $problems["periods.{$j}.opens_at"] ??= self::describe($periods[$j]).' overlaps '.self::describe($periods[$i]).'.';
                        break;
                    }
                }
            }
        }

        return $problems;
    }

    /**
     * When the period opens and closes, in minutes since Sunday 00:00.
     *
     * @param  array{weekday: int, opens_at: string, closes_at: string}  $period
     * @return array{int, int}
     */
    public static function span(array $period): array
    {
        $dayStart = $period['weekday'] * self::MINUTES_PER_DAY;
        $opens = self::minutes($period['opens_at']);
        $closes = self::minutes($period['closes_at']);
        $nextDay = self::closesNextDay($period['opens_at'], $period['closes_at']) ? self::MINUTES_PER_DAY : 0;

        return [$dayStart + $opens, $dayStart + $closes + $nextDay];
    }

    /**
     * Closing at or before the opening time means the next morning: 08:00 to 02:00 closes
     * at 2 am, 08:00 to 00:00 closes at midnight, and 00:00 to 00:00 is open 24 hours.
     */
    public static function closesNextDay(string $opensAt, string $closesAt): bool
    {
        return self::minutes($closesAt) <= self::minutes($opensAt);
    }

    /** "08:30" -> 510 */
    private static function minutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);

        return (int) $hours * 60 + (int) $minutes;
    }

    /**
     * @param  array{weekday: int, opens_at: string, closes_at: string}  $period
     */
    private static function describe(array $period): string
    {
        return self::DAY_NAMES[$period['weekday']].' '.substr($period['opens_at'], 0, 5).'–'.substr($period['closes_at'], 0, 5);
    }
}
