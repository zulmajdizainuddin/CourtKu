<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\VenueRole;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueInvite;
use App\Notifications\StaffInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * The owner's staff page (FR-05): who works here, pending invites, inviting and removing.
 * Owners only; every route checks VenuePolicy::update.
 */
class StaffController extends Controller
{
    /** Stops a script from sending hundreds of invites from one venue. */
    private const MAX_PENDING_INVITES = 20;

    /** GET /api/v1/manage/venues/{venue}/staff */
    public function index(Venue $venue): JsonResponse
    {
        $members = $venue->members()->orderBy('venue_members.created_at')->get()->map(fn (User $user) => [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRelationValue('pivot')?->role,
            'joined_at' => $user->getRelationValue('pivot')?->created_at?->toIso8601String(),
        ]);

        $invites = $venue->invites()->pending()->orderBy('created_at')->get()->map(fn (VenueInvite $invite) => [
            'id' => $invite->id,
            'email' => $invite->email,
            'expires_at' => $invite->expires_at->toIso8601String(),
        ]);

        return response()->json(['data' => ['members' => $members, 'invites' => $invites]]);
    }

    /**
     * POST /api/v1/manage/venues/{venue}/staff-invites  { "email": "aina@example.com" }
     * Inviting the same email again sends a fresh link; the old one stops working.
     */
    public function invite(Request $request, Venue $venue): JsonResponse
    {
        $email = strtolower(trim((string) $request->validate(['email' => ['required', 'email', 'max:255']])['email']));

        if ($venue->members()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'This person already works at this venue.']);
        }

        $existing = $venue->invites()->pending()->where('email', $email)->first();
        if ($existing === null && $venue->invites()->pending()->count() >= self::MAX_PENDING_INVITES) {
            throw new ApiException('INVITE_LIMIT', 'This venue has '.self::MAX_PENDING_INVITES.' invites waiting. Cancel some first.');
        }

        $token = VenueInvite::newToken();
        $existing?->delete();
        $invite = $venue->invites()->create([
            'email' => $email,
            'role' => VenueRole::Staff,
            'token_hash' => VenueInvite::hashToken($token),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(VenueInvite::DAYS_VALID),
        ]);

        // Sent to the address itself: the person may not have an account yet.
        Notification::route('mail', $email)->notify(new StaffInvite($venue, $request->user()->name, $token));

        return response()->json(['data' => [
            'id' => $invite->id,
            'email' => $invite->email,
            'expires_at' => $invite->expires_at->toIso8601String(),
        ]], 201);
    }

    /** DELETE /api/v1/manage/venues/{venue}/staff-invites/{invite}: the link stops working. */
    public function cancelInvite(Venue $venue, VenueInvite $invite): Response
    {
        $invite->delete();

        return response()->noContent();
    }

    /** DELETE /api/v1/manage/venues/{venue}/staff/{user}: they lose access straight away. */
    public function remove(Venue $venue, int $user): Response
    {
        $member = $venue->members()->whereKey($user)->first();
        if ($member === null) {
            abort(404);
        }
        if ($venue->roleOf($member) === VenueRole::Owner) {
            throw new ApiException('CANNOT_REMOVE_OWNER', 'The owner can\'t be removed from their venue.');
        }

        $venue->members()->detach($member->id);

        return response()->noContent();
    }
}
