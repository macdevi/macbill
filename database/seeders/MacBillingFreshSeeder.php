<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MacBillingFreshSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@macbilling.local',
                'role' => 'admin',
            ],
            [
                'name' => 'Kasir',
                'username' => 'kasir',
                'email' => 'kasir@macbilling.local',
                'role' => 'collector',
            ],
            [
                'name' => 'Teknisi',
                'username' => 'teknisi',
                'email' => 'teknisi@macbilling.local',
                'role' => 'technician',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'phone' => null,
                    'status' => 'active',
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
