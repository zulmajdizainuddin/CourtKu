<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * php artisan courtku:reset-two-factor owner@example.com
 *
 * The emergency unlock for someone who lost their phone AND all their recovery codes.
 * It turns two-factor login off for that one account; they log in with their password
 * and are asked to set it up again on their new phone. There is no web page for this,
 * so only someone with access to the database can do it. Steps: docs/deploy.md.
 */
class ResetTwoFactor extends Command
{
    protected $signature = 'courtku:reset-two-factor {email : The email of the locked-out account}';

    protected $description = 'Turn off two-factor login for one account that lost its phone and recovery codes';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("No account uses {$email}.");

            return self::FAILURE;
        }

        if ($user->two_factor_secret === null) {
            $this->info("Two-factor login is already off for {$email}. Nothing to do.");

            return self::SUCCESS;
        }

        // A thief with a stolen password may also ask for this, so the person running it
        // must have checked first, e.g. with an email to the account's own address.
        if (! $this->confirm("Did you check that this request really comes from {$user->name} ({$email})?")) {
            $this->warn('Nothing changed.');

            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        // Leaves a trace in the logs (the user id only, no personal data).
        Log::warning('Two-factor login was reset by command.', ['user_id' => $user->id]);

        $this->info("Two-factor login is off for {$email}. Ask them to log in and set it up again on their new phone.");

        return self::SUCCESS;
    }
}
