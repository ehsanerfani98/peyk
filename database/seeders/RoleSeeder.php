<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'مدیر' => 'admin',
            'کاربر' => 'user',
            'مشتری' => 'customer',
            'پیک' => 'courier',
        ];

        foreach ($roles as $title => $role) {
            Role::firstOrCreate([
                'name' => $role,
                'title' => $title,
                'guard_name' => 'web',
            ]);
        }
    }
}
