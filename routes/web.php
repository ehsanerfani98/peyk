<?php

use App\Http\Controllers\PaymentController;
use App\Jobs\SearchCourierForOrderJob;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')->name('login');
    Route::livewire('/register', 'auth.register')->name('register');
});

Route::get('/', function () {
    return view('landing');
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::livewire('/dashboard', 'admin.dashboard.index')->name('admin.dashboard');
    Route::livewire('/profile', 'profile')->name('admin.profile');
});

Route::middleware(['auth', 'permission:manage users'])->prefix('admin')->group(function () {
    Route::livewire('/roles', 'admin.roles.index')->name('admin.roles');
    Route::livewire('/users', 'admin.users.index')->name('admin.users');
});

Route::middleware(['auth', 'permission:manage orders'])->prefix('admin')->group(function () {
    Route::livewire('/orders', 'admin.orders.index')->name('admin.orders');
    Route::livewire('/orders/{order}', 'admin.orders.show')->name('admin.orders.show');
    Route::livewire('/orders/{order}/manual-actions', 'admin.orders.manual-actions')->name('admin.orders.manual-actions');
});

Route::middleware(['auth', 'permission:manage couriers'])->prefix('admin')->group(function () {
    Route::livewire('/couriers', 'admin.couriers.index')->name('admin.couriers');
    Route::livewire('/couriers/create', 'admin.couriers.create')->name('admin.couriers.create');
    Route::livewire('/couriers/live-map', 'admin.couriers.live-map')->name('admin.couriers.live-map');
    Route::livewire('/couriers/{courier}', 'admin.couriers.show')->name('admin.couriers.show');
});

Route::middleware(['auth', 'permission:manage payments'])->prefix('admin')->group(function () {
    Route::livewire('/payments', 'admin.payments.index')->name('admin.payments');
    Route::livewire('/payments/{order}', 'admin.payments.show')->name('admin.payments.show');
});

Route::middleware(['auth', 'permission:manage surveys'])->prefix('admin')->group(function () {
    Route::livewire('/surveys', 'admin.surveys.index')->name('admin.surveys');
    Route::livewire('/surveys/{survey}', 'admin.surveys.show')->name('admin.surveys.show');
});

Route::middleware(['auth', 'permission:manage reviews'])->prefix('admin')->group(function () {
    Route::livewire('/reviews', 'admin.reviews.index')->name('admin.reviews');
});

Route::middleware(['auth', 'permission:manage customers'])->prefix('admin')->group(function () {
    Route::livewire('/customers', 'admin.customers.index')->name('admin.customers');
    Route::livewire('/customers/{customer}', 'admin.customers.show')->name('admin.customers.show');
});

Route::middleware(['auth', 'permission:manage settings'])->prefix('admin')->group(function () {
    Route::livewire('/settings/general', 'admin.settings.general')->name('admin.settings.general');
    Route::livewire('/settings/courier-search', 'admin.settings.courier-search')->name('admin.settings.courier-search');
    Route::livewire('/settings/payment', 'admin.settings.payment')->name('admin.settings.payment');
    Route::livewire('/settings/sms', 'admin.settings.sms')->name('admin.settings.sms');
    Route::livewire('/settings/otp', 'admin.settings.otp')->name('admin.settings.otp');
});

Route::middleware(['auth', 'permission:view reports'])->prefix('admin')->group(function () {
    Route::livewire('/reports/orders', 'admin.reports.orders')->name('admin.reports.orders');
    Route::livewire('/reports/financial', 'admin.reports.financial')->name('admin.reports.financial');
    Route::livewire('/reports/couriers', 'admin.reports.couriers')->name('admin.reports.couriers');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::livewire('/logs/activity', 'admin.logs.activity')->name('admin.logs.activity');
});

Route::get('/pay/{driver}/{orderId}', [PaymentController::class, 'pay'])
    ->whereIn('driver', ['zarinpal', 'zibal'])
    ->name('payment.pay');

Route::get('/pay/{driver}/{orderId}/callback', [PaymentController::class, 'callback'])
    ->whereIn('driver', ['zarinpal', 'zibal'])
    ->name('payment.callback');

Route::livewire('/pay/{orderId}/success', 'payment.success')->name('payment.success');

Route::livewire('/pay/{orderId}/failed', 'payment.failed')->name('payment.failed');

Route::livewire('/verify/{token}', 'verify.confirm')->name('verification.confirm');

Route::livewire('/survey/{token}', 'survey.form')->name('survey.form');
