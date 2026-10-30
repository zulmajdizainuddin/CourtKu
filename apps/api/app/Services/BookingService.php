<?php

namespace App\Services;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\VenueStatus;
use App\Exceptions\ApiException;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Makes bookings (FR-15, FR-18). Plan section 7, step by step:
 *   1. prepare(): check the time (opening hours, closures, window, notice, length, free)
 *      and price every half hour.
 *   2. store(): in one database transaction, save the booking and one booking_slots row
 *      per half hour.
 *
 * Step 1 alone can't stop double booking: two players can both pass it at the same moment.
 * Step 2 can: booking_slots has a unique index on (court_id, slot_start), so the database
 * saves only the first claim on a half hour. The second fails, everything it saved is
 * rolled back, and the player gets 409 SLOT_TAKEN.
 */
class BookingService
{
    /** Letters and digits that can't be mixed up when read out (no 0/O, 1/I/L). */
    private const CODE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    /**
     * Checks a booking (venue approved, court bookable, start on :00 or :30, allowed length, opening
     * hours, closures, window, notice, free) and works out its price. Throws a clear error when it
     * can't be booked.
     */
    public function prepare(Court $court, CarbonImmutable $startsAt, int $minutes): BookingDraft
    {
        $venue = $court->venue;
        // Plan section 7, step 1: only an approved venue takes bookings.
        if ($venue->status !== VenueStatus::Active) {
            throw new ApiException('VENUE_NOT_ACTIVE', 'This venue is not taking bookings yet.');
        }
        [$date, $startMinute] = (new AvailabilityService($venue))->check($court, $startsAt, $minutes);
        $pricing = PricingService::forVenue($venue);

        $cells = [];
        for ($minute = $startMinute; $minute < $startMinute + $minutes; $minute += PricingService::CELL_MINUTES) {
            $cells[] = [
                'starts_at' => AvailabilityService::startsAt($date, $minute)->utc(),
                'price_sen' => $pricing->cellPriceSen($court, $date->dayOfWeek, $minute),
            ];
        }

        return new BookingDraft(
            court: $court,
            startsAt: $startsAt->utc(),
            endsAt: $startsAt->utc()->addMinutes($minutes),
            businessDate: $date,
            cells: $cells,
            totalSen: array_sum(array_column($cells, 'price_sen')),
        );
    }

    /**
     * Saves a prepared booking, or throws 409 SLOT_TAKEN when someone took one of its
     * half hours since it was prepared. In M3 every booking is paid at the venue,
     * so it is confirmed straight away (ADR-021).
     */
    public function store(
        BookingDraft $draft,
        BookingSource $source,
        ?User $player = null,
        ?User $createdBy = null,
        ?int $sportId = null,
        ?string $customerName = null,
        ?string $customerPhone = null,
        ?string $idempotencyKey = null,
    ): Booking {
        try {
            return DB::transaction(function () use ($draft, $source, $player, $createdBy, $sportId, $customerName, $customerPhone, $idempotencyKey) {
                $booking = new Booking([
                    'code' => $this->newCode(),
                    'idempotency_key' => $idempotencyKey,
                    'venue_id' => $draft->court->venue_id,
                    'court_id' => $draft->court->id,
                    'sport_id' => $sportId,
                    'user_id' => $player?->id,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'source' => $source,
                    'starts_at' => $draft->startsAt,
                    'ends_at' => $draft->endsAt,
                    'business_date' => $draft->businessDate->toDateString(),
                    'total_sen' => $draft->totalSen,
                    'created_by' => $createdBy?->id,
                ]);
                $booking->status = BookingStatus::Confirmed;
                $booking->save();

                // One insert for all half hours. If any of them is taken, this throws.
                $now = now();
                BookingSlot::insert(array_map(fn (array $cell) => [
                    'court_id' => $draft->court->id,
                    'slot_start' => $cell['starts_at']->format('Y-m-d H:i:s'),
                    'booking_id' => $booking->id,
                    'price_sen' => $cell['price_sen'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $draft->cells));

                AuditLog::record($booking, 'booking.created', $createdBy, [
                    'source' => $source->value,
                    'total_sen' => $draft->totalSen,
                ]);

                return $booking;
            });
        } catch (UniqueConstraintViolationException $e) {
            // The same Book press arriving twice at once: the caller returns the first booking.
            if (str_contains($e->getMessage(), 'idempotency_key')) {
                throw $e;
            }

            // Everything this booking saved was rolled back. Say which half hours the other booking took.
            $taken = AvailabilityService::takenOf(
                $draft->court,
                array_map(fn (array $cell) => $cell['starts_at']->format('Y-m-d H:i:s'), $draft->cells),
            );

            throw new ApiException(
                'SLOT_TAKEN',
                "Someone has just booked {$draft->court->name} at this time. Please choose another time or court.",
                details: ['slots' => $taken],
            );
        }
    }

    /** "CK-7F3K2": 31^5 = 28 million codes, so trying again on the rare repeat is enough. */
    private function newCode(): string
    {
        do {
            $code = 'CK-';
            for ($i = 0; $i < 5; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (Booking::where('code', $code)->exists());

        return $code;
    }
}
