<?php

use App\Models\CourierCurrentLocation;
use App\Models\CourierProfile;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('ثبت پیک جدید')] class extends Component
{
    use Toast;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $mobile = '';

    public string $national_code = '';

    public string $vehicle_type = '';

    public string $vehicle_number = '';

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage couriers'), 403);

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'national_code' => 'required|string|max:20|unique:courier_profiles,national_code',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_number' => 'required|string|max:50',
        ], [], [
            'name' => 'نام',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'mobile' => 'موبایل',
            'national_code' => 'کد ملی',
            'vehicle_type' => 'وسیله نقلیه',
            'vehicle_number' => 'شماره وسیله',
        ]);

        $user = null;

        DB::transaction(function () use (&$user) {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'mobile' => $this->mobile,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('courier');

            CourierProfile::create([
                'user_id' => $user->id,
                'national_code' => $this->national_code,
                'vehicle_type' => $this->vehicle_type,
                'vehicle_number' => $this->vehicle_number,
                'status' => 'offline',
            ]);

            // ایجاد رکورد موقعیت برای پیک
            CourierCurrentLocation::create([
                'courier_id' => $user->id,
                'location' => DB::raw("ST_GeomFromText('POINT(0 0)', 4326)"),
            ]);
        });

        app(ActivityLogger::class)->log('courier.created', $user);
        $this->success('پیک با موفقیت ثبت شد', position: 'toast-bottom toast-end');
        $this->redirect(route('admin.couriers'));
    }
};
