<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage roles',
            'manage permissions',
            'manage users',
            'view dashboard',
            'manage orders',
            'force cancel orders',
            'manual order actions',
            'manage couriers',
            'manage payments',
            'process refunds',
            'manage surveys',
            'manage reviews',
            'manage customers',
            'manage settings',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);
    }
}
