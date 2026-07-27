<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // مدیر پیش‌فرض
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدیر سیستم',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // کاربر عادی پیش‌فرض
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'کاربر تست',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );
        $user->syncRoles(['user']);
    }
}
