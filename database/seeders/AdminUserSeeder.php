<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's initial administrator account.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@language-learning.test');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command?->warn(
                'ADMIN_PASSWORD is not set. Skipping initial admin creation.'
            );

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'System Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        $this->command?->info(
            "Initial admin account is ready: {$email}"
        );
    }
}