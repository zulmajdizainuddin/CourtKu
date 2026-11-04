<?php

namespace Database\Seeders;

use App\Enums\MalaysianState;
use App\Enums\PaymentMode;
use App\Enums\VenueRole;
use App\Enums\VenueStatus;
use App\Models\Court;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePaymentAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Laravel\Fortify\RecoveryCode;
use RuntimeException;

/**
 * The data for the browser tests (apps/web/e2e, ADR-034): one venue open 24 hours that takes online
 * payments through the pretend gateway, and a player, a staff member and an owner (with two-factor
 * login) for each screen size the tests run on, so the two runs never share an account.
 *
 *   php artisan migrate:fresh --seed --seeder=E2eSeeder   (with APP_ENV=e2e, see apps/web/e2e/start-api.mjs)
 *
 * The password and the two-factor secrets are test values, also in apps/web/e2e/accounts.ts.
 */
class E2eSeeder extends Seeder
{
    public const PASSWORD = 'E2e-Test-Pass-2026';

    /** Two-factor secrets (base32) of the owners, by screen size. */
    public const OWNER_SECRETS = ['desktop' => 'JBSWY3DPEHPK3PXP', 'phone' => 'KRSXG5CTMVRXEZLU'];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('The browser-test data must never be loaded on the live site.');
        }

        $venue = new Venue([
            'name' => 'E2E Arena',
            'slug' => 'e2e-arena',
            'state' => MalaysianState::Selangor,
            'description' => 'The venue the browser tests book at.',
            'address_line' => 'Lot 2, Jalan Ujian',
            'city' => 'Shah Alam',
            'postcode' => '40000',
            'phone' => '+60355551234',
            'facilities' => ['parking', 'toilets'],
            'payment_mode' => PaymentMode::Both,
            'booking_emails' => false,
        ]);
        $venue->forceFill(['status' => VenueStatus::Active, 'approved_at' => now()])->save();

        // Open 24 hours every day, so a walk-in can always start within the next half hour.
        foreach (range(0, 6) as $weekday) {
            $venue->operatingHours()->create(['weekday' => $weekday, 'opens_at' => '00:00', 'closes_at' => '00:00', 'closes_next_day' => true]);
        }

        foreach ([['Court 1', 'badminton', 2000], ['Court 2', 'badminton', 2000], ['Pickleball 1', 'pickleball', 2500]] as $order => [$name, $sport, $price]) {
            /** @var Court $court */
            $court = $venue->courts()->create(['name' => $name, 'is_indoor' => true, 'base_price_sen' => $price, 'sort_order' => $order]);
            $court->sports()->attach(Sport::where('slug', $sport)->value('id'));
        }

        // A ready account at the pretend gateway, so "Pay online" is offered.
        (new VenuePaymentAccount)->forceFill([
            'venue_id' => $venue->id,
            'gateway' => 'pretend',
            'account_id' => 'acct_pretend_e2e',
            'account_name' => 'E2E Arena',
            'charges_enabled' => true,
            'is_live' => false,
            'checked_at' => now(),
        ])->save();

        foreach (array_keys(self::OWNER_SECRETS) as $screen) {
            $this->user("player-{$screen}@example.com", "E2E Player {$screen}");
            $venue->members()->attach($this->user("staff-{$screen}@example.com", "E2E Staff {$screen}"), ['role' => VenueRole::Staff]);

            $owner = $this->user("owner-{$screen}@example.com", "E2E Owner {$screen}");
            $owner->forceFill([
                'two_factor_secret' => encrypt(self::OWNER_SECRETS[$screen]),
                'two_factor_recovery_codes' => encrypt((string) json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())),
                'two_factor_confirmed_at' => now(),
            ])->save();
            $venue->members()->attach($owner, ['role' => VenueRole::Owner]);
        }
    }

    private function user(string $email, string $name): User
    {
        $user = new User(['name' => $name, 'email' => $email, 'password' => self::PASSWORD]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
