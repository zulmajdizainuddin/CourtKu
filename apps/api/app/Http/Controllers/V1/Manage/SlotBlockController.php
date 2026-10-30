<?php

namespace App\Http\Controllers\V1\Manage;

use App\Http\Controllers\Controller;
use App\Models\SlotBlock;
use App\Models\Venue;
use App\Services\BlockService;
use App\Support\MalaysiaTime;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Closing courts for repairs or a private event (FR-21). Owners and staff.
 * Routes with {slotBlock} only find blocks of this {venue} (scopeBindings).
 */
class SlotBlockController extends Controller
{
    /**
     * POST /api/v1/manage/venues/{venue}/blocks
     * { "court_ids": [6, 7], "starts_at": "2026-11-06T18:00:00+08:00", "minutes": 120, "reason": "Floor repair" }
     * One block per court, all or nothing: 409 SLOT_TAKEN (with the bookings in the way) when any
     * of these half hours is already booked or blocked.
     */
    public function store(Request $request, Venue $venue, BlockService $blocks): JsonResponse
    {
        $data = $request->validate([
            'court_ids' => ['required', 'array', 'min:1', 'max:50'],
            'court_ids.*' => ['integer', 'distinct'],
            'starts_at' => ['required', 'date'],
            // Half-hour steps, at most a whole day; longer closures are closure days (FR-07).
            'minutes' => ['required', 'integer', 'min:30', 'max:1440', 'multiple_of:30'],
            'reason' => ['required', 'string', 'max:100'],
        ], [
            'court_ids.required' => 'Choose at least one court.',
            'court_ids.min' => 'Choose at least one court.',
            'reason.required' => 'Say why, e.g. floor repair or private event.',
        ]);

        // Only this venue's courts: venue A can't block venue B's court.
        $courts = $venue->courts()->whereIn('id', $data['court_ids'])->orderBy('sort_order')->orderBy('id')->get();
        if ($courts->count() !== count($data['court_ids'])) {
            throw ValidationException::withMessages(['court_ids' => 'Choose this venue\'s courts.']);
        }

        $startsAt = CarbonImmutable::parse($data['starts_at'], MalaysiaTime::TIMEZONE)->setTimezone(MalaysiaTime::TIMEZONE);
        $made = $blocks->block($venue, $courts, $startsAt, (int) $data['minutes'], trim($data['reason']), $request->user());

        return response()->json(['data' => $made->map(fn (SlotBlock $block) => self::toArray($block))], 201);
    }

    /** DELETE /api/v1/manage/venues/{venue}/blocks/{slotBlock}: the time can be booked again. */
    public function destroy(Venue $venue, SlotBlock $slotBlock, BlockService $blocks): Response
    {
        $blocks->lift($slotBlock);

        return response()->noContent();
    }

    /**
     * A block as the schedule shows it.
     *
     * @return array<string, mixed>
     */
    public static function toArray(SlotBlock $block): array
    {
        return [
            'id' => $block->id,
            'court_id' => $block->court_id,
            'starts_at' => $block->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'ends_at' => $block->ends_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->toIso8601String(),
            'minutes' => (int) $block->starts_at->diffInMinutes($block->ends_at),
            'reason' => $block->reason,
            'created_by' => $block->creator?->name,
        ];
    }
}
