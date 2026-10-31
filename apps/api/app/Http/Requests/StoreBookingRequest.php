<?php

namespace App\Http\Requests;

use App\Enums\VenueStatus;
use App\Models\Venue;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * POST /api/v1/venues/{slug}/bookings: a player books a court (FR-15).
 * { "court_id": 6, "sport": "badminton", "starts_at": "2026-11-06T18:00:00+08:00", "minutes": 60, "pay": "online" }
 * with an Idempotency-Key header. The opening hours, length, notice and whether the time is
 * free are checked by the booking engine itself (BookingService::prepare).
 */
class StoreBookingRequest extends FormRequest
{
    /** Only approved venues take bookings; any other is 404, like its public page. */
    protected function prepareForValidation(): void
    {
        abort_unless($this->venue()->status === VenueStatus::Active, 404);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'court_id' => ['required', 'integer'],
            'sport' => ['required', 'string', Rule::exists('sports', 'slug')],
            'starts_at' => ['required', 'date'],
            'minutes' => ['required', 'integer', 'min:30', 'max:720'],
            // online = hold the time while the player pays (FR-16); counter = pay at the venue. Left out: what the venue takes.
            'pay' => ['sometimes', 'in:online,counter'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sport.required' => 'Choose a sport.',
            'sport.exists' => 'Choose badminton, futsal or pickleball.',
            'pay.in' => 'Choose to pay online or at the counter.',
        ];
    }

    /** True = pay online now. Refused (422) when the venue doesn't take that way of paying. */
    public function paysOnline(): bool
    {
        $venue = $this->venue();
        $pay = $this->string('pay')->value() ?: ($venue->acceptsCounterPayment() ? 'counter' : 'online');

        if ($pay === 'online' && ! $venue->acceptsOnlinePayment()) {
            throw ValidationException::withMessages(['pay' => 'This venue doesn\'t take online payment. You pay at the counter.']);
        }
        if ($pay === 'counter' && ! $venue->acceptsCounterPayment()) {
            throw ValidationException::withMessages(['pay' => 'This venue takes online payment only.']);
        }

        return $pay === 'online';
    }

    /** The start time, in Malaysia time. */
    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->string('starts_at')->value(), MalaysiaTime::TIMEZONE)
            ->setTimezone(MalaysiaTime::TIMEZONE);
    }

    private function venue(): Venue
    {
        /** @var Venue */
        return $this->route('venue');
    }
}
