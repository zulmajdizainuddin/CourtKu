<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ManageVenueResource;
use App\Models\User;
use App\Models\VenueInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The invited person's side (FR-05): see the invite, then accept it.
 * The link only works for the invited email, so a forwarded link is useless to anyone else.
 */
class InviteController extends Controller
{
    /** GET /api/v1/invites/{token}: what the invite is, before accepting. */
    public function show(Request $request, string $token): JsonResponse
    {
        $invite = $this->findOrFail($token);

        return response()->json(['data' => [
            'venue_name' => $invite->venue->name,
            'role' => $invite->role,
            // Only a hint, e.g. "a***@gmail.com": enough to know which account to use.
            'email_hint' => self::maskEmail($invite->email),
            'for_this_account' => $this->isFor($invite, $request->user()),
            'expired' => $invite->isExpired(),
            'accepted' => $invite->accepted_at !== null,
        ]]);
    }

    /** POST /api/v1/invites/{token}/accept: the logged-in, verified user joins the venue. */
    public function accept(Request $request, string $token): ManageVenueResource
    {
        $user = $request->user();

        $venue = DB::transaction(function () use ($token, $user) {
            // Locked, so pressing Accept twice at once can't add the person twice.
            $invite = VenueInvite::where('token_hash', VenueInvite::hashToken($token))->lockForUpdate()->first()
                ?? throw new ApiException('INVITE_NOT_FOUND', 'This invite doesn\'t exist or was cancelled.', 404);

            if ($invite->accepted_at !== null) {
                throw new ApiException('INVITE_USED', 'This invite has already been used.');
            }
            if ($invite->isExpired()) {
                throw new ApiException('INVITE_EXPIRED', 'This invite has expired. Ask the venue owner to send a new one.', 410);
            }
            if (! $this->isFor($invite, $user)) {
                throw new ApiException('INVITE_WRONG_ACCOUNT', 'This invite is for another email address. Log in with the invited email.', 403);
            }

            $venue = $invite->venue;
            if ($venue->roleOf($user) === null) {
                $venue->members()->attach($user, ['role' => $invite->role]);
            }
            $invite->forceFill(['accepted_at' => now()])->save();

            return $venue;
        });

        return new ManageVenueResource($venue);
    }

    private function findOrFail(string $token): VenueInvite
    {
        return VenueInvite::findByToken($token)
            ?? throw new ApiException('INVITE_NOT_FOUND', 'This invite doesn\'t exist or was cancelled.', 404);
    }

    private function isFor(VenueInvite $invite, User $user): bool
    {
        return strtolower($user->email) === $invite->email;
    }

    /** "aina.rahman@gmail.com" -> "a***@gmail.com" */
    public static function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2) + [1 => ''];

        return mb_substr($name, 0, 1).'***@'.$domain;
    }
}
