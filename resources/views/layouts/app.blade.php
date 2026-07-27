<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="font-sans antialiased">
    <x-toast />
    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav sticky full-width>

        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>

            {{-- Brand --}}
            <div>App</div>
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <x-button label="Messages" icon="o-envelope" link="###" class="btn-ghost btn-sm" responsive />
            <x-button label="Notifications" icon="o-bell" link="###" class="btn-ghost btn-sm" responsive />
            <x-theme-toggle class="btn btn-circle" />
            <x-button icon="o-user" class="btn-circle" link="{{ route('admin.profile') }}"
                tooltip-right="ویرایش پروفایل" responsive />
        </x-slot:actions>
    </x-nav>

    {{-- The main content with `full-width` --}}
    <x-main with-nav full-width>

        {{-- This is a sidebar that works also as a drawer on small screens --}}
        {{-- Notice the `main-drawer` reference here --}}
        <x-slot:sidebar drawer="main-drawer" collapsible collapse-text="سایدبار را مخفی کن" class="bg-base-200">

            {{-- User --}}
            @if ($user = auth()->user())
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="bg-base-300 py-4 px-4 border-b-1 border-gray-200">
                    <x-slot:actions>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-right="خروج"
                                type="submit" />
                        </form>
                    </x-slot:actions>
                </x-list-item>
            @endif

            {{-- Activates the menu item when a route matches the `link` property --}}
            <x-menu activate-by-route>
                <x-menu-item title="پیشخوان" icon="o-squares-2x2" link="{{ route('admin.dashboard') }}" />

                @can('manage orders')
                    <x-menu-item title="مدیریت سفارش‌ها" icon="o-truck" link="{{ route('admin.orders') }}" />
                @endcan

                @can('manage couriers')
                    <x-menu-item title="مدیریت پیک‌ها" icon="o-bolt" link="{{ route('admin.couriers') }}" />
                @endcan

                @can('manage payments')
                    <x-menu-item title="مدیریت پرداخت‌ها" icon="o-credit-card" link="{{ route('admin.payments') }}" />
                @endcan

                @can('manage customers')
                    <x-menu-item title="مدیریت مشتریان" icon="o-users" link="{{ route('admin.customers') }}" />
                @endcan

                @can('manage users')
                    <x-menu-item title="مدیریت کاربران" icon="o-user-group" link="{{ route('admin.users') }}" />
                @endcan

                @can('manage roles')
                    <x-menu-item title="نقش‌ها و مجوزها" icon="o-shield-check" link="{{ route('admin.roles') }}" />
                @endcan

                @can('manage surveys')
                    <x-menu-item title="نظرسنجی‌ها" icon="o-clipboard-document-list" link="{{ route('admin.surveys') }}" />
                @endcan

                @can('manage reviews')
                    <x-menu-item title="نظرات" icon="o-star" link="{{ route('admin.reviews') }}" />
                @endcan

                @can('manage settings')
                    <x-menu-sub title="تنظیمات" icon="o-cog-6-tooth">
                        <x-menu-item title="عمومی" icon="o-adjustments-horizontal" link="{{ route('admin.settings.general') }}" />
                        <x-menu-item title="جستجوی پیک" icon="o-magnifying-glass" link="{{ route('admin.settings.courier-search') }}" />
                        <x-menu-item title="پرداخت" icon="o-credit-card" link="{{ route('admin.settings.payment') }}" />
                        <x-menu-item title="پیامک" icon="o-chat-bubble-left-ellipsis" link="{{ route('admin.settings.sms') }}" />
                        <x-menu-item title="OTP" icon="o-key" link="{{ route('admin.settings.otp') }}" />
                    </x-menu-sub>
                @endcan

                @can('view reports')
                    <x-menu-sub title="گزارش‌ها" icon="o-chart-bar">
                        <x-menu-item title="گزارش سفارش‌ها" icon="o-truck" link="{{ route('admin.reports.orders') }}" />
                        <x-menu-item title="گزارش مالی" icon="o-banknotes" link="{{ route('admin.reports.financial') }}" />
                        <x-menu-item title="گزارش پیک‌ها" icon="o-bolt" link="{{ route('admin.reports.couriers') }}" />
                    </x-menu-sub>
                @endcan

                @can('view dashboard')
                    <x-menu-item title="لاگ فعالیت" icon="o-clock" link="{{ route('admin.logs.activity') }}" />
                @endcan
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    @stack('scripts')
</body>

</html>
