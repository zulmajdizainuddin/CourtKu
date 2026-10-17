<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with demo data.
     * These accounts are for local development and the public demo only;
     * they use @example.com addresses, which can never receive real email.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Player',
            'email' => 'player@example.com',
            'password' => 'CourtKu-Demo-2026',
        ]);
    }
}
