<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * مجوزهای ماژول «شبیه‌ساز پیامک» را می‌سازد و به نقش ادمین متصل می‌کند.
 *
 * این سیدر idempotent است (اجرای چندباره مشکلی ایجاد نمی‌کند) و می‌توان آن را
 * به‌صورت مستقل هم اجرا کرد:
 *   php artisan db:seed --class=SmsSimulatorPermissionSeeder
 */
class SmsSimulatorPermissionSeeder extends Seeder
{
    /**
     * مجوزهای مربوط به ماژول شبیه‌ساز پیامک.
     *
     * @var array<int, string>
     */
    private const PERMISSIONS = [
        'view sms simulator',
        'clear sms simulator',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin->givePermissionTo(self::PERMISSIONS);
    }
}
