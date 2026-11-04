<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountDeletion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Account and login
 *
 * @authenticated
 */
class AccountController extends Controller
{
    /**
     * DELETE /api/v1/me  { "password": "..." }: delete my own account (NFR-07, ADR-033).
     * The password is asked again, so someone at an unlocked laptop can't do it in one click.
     */
    public function destroy(Request $request, AccountDeletion $deletion): Response
    {
        $request->validate(['password' => ['required', 'string']], ['password.required' => 'Enter your password.']);

        /** @var User $user */
        $user = $request->user();
        if (! Hash::check($request->string('password')->value(), $user->password)) {
            throw ValidationException::withMessages(['password' => 'That password is not right.']);
        }

        $deletion->delete($user);

        // Log this browser out too.
        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
