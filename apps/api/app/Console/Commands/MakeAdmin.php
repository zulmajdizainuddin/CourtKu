<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * php artisan courtku:make-admin you@example.com
 *
 * The only way to become a platform admin. There is no web form or API for it,
 * so nobody can make themselves admin from the website.
 */
class MakeAdmin extends Command
{
    protected $signature = 'courtku:make-admin {email : The email of an account that already exists}';

    protected $description = 'Give an existing account platform admin rights (approve and suspend venues)';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("No account uses {$email}. Register it on the website first.");

            return self::FAILURE;
        }

        // is_platform_admin is not fillable on purpose, so set it directly.
        $user->forceFill(['is_platform_admin' => true])->save();
        $this->info("{$user->name} ({$email}) is now a platform admin.");

        return self::SUCCESS;
    }
}
