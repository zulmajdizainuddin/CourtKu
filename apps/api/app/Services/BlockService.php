<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\SlotBlock;
use App\Models\User;
use App\Models\Venue;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Closing courts for a while, e.g. repairs or a private event (FR-21).
 *
 * A block takes its half hours in booking_slots exactly like a booking (ADR-010), so the same
 * unique index guarantees a block and a booking can never share a half hour: a block over an
 * existing booking is refused, and nobody can book a blocked time.
 */
class BlockService
{
    private const CELL_MINUTES = PricingService::CELL_MINUTES;

    /**
     * Blocks the same time on each of these courts (one block per court), all or nothing.
     *
     * @param  EloquentCollection<int, Court>  $courts  courts of $venue
     * @return Collection<int, SlotBlock>
     */
    public function block(Venue $venue, EloquentCollection $courts, CarbonImmutable $startsAt, int $minutes, string $reason, User $by): Collection
    {
        [$date, $cells] = $this->cells($venue, $startsAt, $minutes);

        $taken = $this->taken($courts, $cells);
        if ($taken !== []) {
            throw self::slotTaken($taken);
        }

        try {
            return DB::transaction(function () use ($venue, $courts, $startsAt, $minutes, $date, $cells, $reason, $by) {
                $now = now();

                return $courts->map(function (Court $court) use ($venue, $startsAt, $minutes, $date, $cells, $reason, $by, $now) {
                    $block = SlotBlock::create([
                        'venue_id' => $venue->id,
                        'court_id' => $court->id,
                        'starts_at' => $startsAt->utc(),
                        'ends_at' => $startsAt->utc()->addMinutes($minutes),
                        'business_date' => $date->toDateString(),
                        'reason' => $reason,
                        'created_by' => $by->id,
                    ]);
                    BookingSlot::insert(array_map(fn (string $cell) => [
                        'court_id' => $court->id,
                        'slot_start' => $cell,
                        'slot_block_id' => $block->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $cells));

                    return $block;
                })->values();
            });
        } catch (UniqueConstraintViolationException) {
            // Someone booked one of these half hours since the check above.
            throw self::slotTaken($this->taken($courts, $cells));
        }
    }

    /** Lifts a block: its half hours can be booked again straight away. */
    public function lift(SlotBlock $block): void
    {
        DB::transaction(function () use ($block) {
            $block->slots()->delete();
            $block->delete();
        });
    }

    /**
     * The business day and the half hours a block would take, in UTC ("2026-11-06 10:00:00"), after
     * checking the time: on :00 or :30, not already over, and inside one stretch of opening hours.
     *
     * @return array{CarbonImmutable, list<string>}
     */
    private function cells(Venue $venue, CarbonImmutable $startsAt, int $minutes): array
    {
        if ($startsAt->second !== 0 || $startsAt->minute % self::CELL_MINUTES !== 0) {
            throw ValidationException::withMessages(['starts_at' => 'Choose a time on the hour or half hour.']);
        }
        // The half hour running now can still be blocked (e.g. a leak found at 7:10 pm).
        $now = AvailabilityService::now();
        $thisHalfHour = $now->setTime($now->hour, $now->minute - $now->minute % self::CELL_MINUTES);
        if ($startsAt->lessThan($thisHalfHour)) {
            throw ValidationException::withMessages(['starts_at' => 'That time has already passed.']);
        }

        $availability = new AvailabilityService($venue);
        $located = $availability->locate($startsAt);
        if ($located === null) {
            throw ValidationException::withMessages(['starts_at' => 'The venue is closed at that time, so there is nothing to block.']);
        }
        [$date, $startMinute] = $located;

        $open = $availability->openMinutes($date);
        $cells = [];
        for ($minute = $startMinute; $minute < $startMinute + $minutes; $minute += self::CELL_MINUTES) {
            if (! in_array($minute, $open, true)) {
                $closesAt = $minute % 1440 === 0 ? 'midnight' : AvailabilityService::startsAt($date, $minute)->format('g:i a');
                throw ValidationException::withMessages(['minutes' => "The venue closes at {$closesAt}. Choose a shorter time."]);
            }
            $cells[] = AvailabilityService::startsAt($date, $minute)->utc()->format('Y-m-d H:i:s');
        }

        return [$date, $cells];
    }

    /**
     * What already holds any of these half hours: [court name, Malaysia time, booking code or null for a block].
     *
     * @param  EloquentCollection<int, Court>  $courts
     * @param  list<string>  $cells
     * @return list<array{court: string, starts_at: string, booking: string|null}>
     */
    private function taken(EloquentCollection $courts, array $cells): array
    {
        $names = $courts->pluck('name', 'id');

        return BookingSlot::whereIn('court_id', $courts->modelKeys())
            ->whereIn('slot_start', $cells)
            ->with('booking:id,code')
            ->orderBy('slot_start')
            ->get()
            ->map(fn (BookingSlot $slot) => [
                'court' => (string) $names[$slot->court_id],
                'starts_at' => $slot->slot_start->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
                'booking' => $slot->booking?->code,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{court: string, starts_at: string, booking: string|null}>  $taken
     */
    private static function slotTaken(array $taken): ApiException
    {
        $first = $taken[0] ?? null;
        $what = $first === null ? 'Part of this time' : "{$first['court']} at ".CarbonImmutable::parse($first['starts_at'])->format('g:i a');
        $by = ($first['booking'] ?? null) !== null ? " is booked ({$first['booking']})" : ' is already taken';

        return new ApiException(
            'SLOT_TAKEN',
            "{$what}{$by}. Cancel or move it first, or choose another time.",
            details: ['slots' => $taken],
        );
    }
}
