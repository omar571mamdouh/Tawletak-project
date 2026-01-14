<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Super Admin 1',
                'email' => 'admin1@system.com',
                'password' => '123456',
            ],
            [
                'name' => 'Super Admin 2',
                'email' => 'admin2@system.com',
                'password' => '123456',
            ],
            [
                'name' => 'Super Admin 3',
                'email' => 'admin3@system.com',
                'password' => '123456',
            ],
        ];

        foreach ($admins as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => $admin['password'], // hashed تلقائي
                    'role' => 'super_admin',
                    'is_active' => true,
                ]
            );
        }
    }
}
