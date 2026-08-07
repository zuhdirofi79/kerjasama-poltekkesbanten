<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_DEFAULT_PASSWORD', 'ChangeMeNow!2026');

        $accounts = [
            [
                'email' => 'webmaster@poltekkesbanten.ac.id',
                'name' => 'Webmaster Poltekkes Banten',
                'role' => UserRole::Superadmin1,
            ],
            [
                'email' => 'rifaldi.tri@poltekkesbanten.ac.id',
                'name' => 'Rifaldi Tri',
                'role' => UserRole::Superadmin2,
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($password),
                    'role' => $account['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            // On subsequent seeds: ensure role/active without overwriting existing name/password.
            if (! $user->wasRecentlyCreated) {
                $user->forceFill([
                    'role' => $account['role'],
                    'is_active' => true,
                ])->save();
            }
        }
    }
}
