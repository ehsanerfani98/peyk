<?php

namespace App\Models;

use App\Events\Order\OrderStatusChanged;
use App\Jobs\SearchCourierForOrderJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model
{
    /**
     * وضعیت‌هایی که در آن‌ها کاربر می‌تواند سفارش جدید ثبت کند
     * (یعنی سفارش قبلی به یکی از این حالت‌های نهایی رسیده باشد).
     */
    public const TERMINAL_STATUSES = [
        'DELIVERED',
        'DELIVERY_FAILED',
        'RETURNED_TO_SENDER',
        'CANCELLED',
        'COURIER_NOT_FOUND',
    ];

    /**
     * وضعیت‌هایی که در آن‌ها امکان لغو/کنسل کردن سفارش (توسط مشتری یا پیک) وجود ندارد.
     * طبق قرارداد پروژه: از لحظه تحویل گرفتن بسته توسط پیک تا پایان مسیر، سفارش قابل لغو نیست.
     */
    public const NON_CANCELLABLE_STATUSES = [
        'PICKED_UP',
        'IN_TRANSIT',
        'DELIVERED',
        'RETURNED_TO_SENDER',
    ];

    protected $fillable = [
        'customer_id',
        'sender_is_customer',
        'sender_name',
        'sender_mobile',
        'sender_address',
        'sender_lat',
        'sender_lng',
        'receiver_is_customer',
        'receiver_name',
        'receiver_mobile',
        'receiver_address',
        'receiver_lat',
        'receiver_lng',
        'package_description',
        'package_weight_kg',
        'package_size',
        'payment_method',
        'payment_by',
        'payment_status',
        'payment_url',
        'payment_authority',
        'payment_driver',
        'payment_ref_id',
        'price',
        'status',
        'courier_id',
        'cancelled_by',
        'cancel_reason',
        'courier_search_started_at',
        'courier_offered_at',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'paid_at',
    ];

    protected $casts = [
        'sender_is_customer' => 'boolean',
        'receiver_is_customer' => 'boolean',
        'sender_lat' => 'decimal:7',
        'sender_lng' => 'decimal:7',
        'receiver_lat' => 'decimal:7',
        'receiver_lng' => 'decimal:7',
        'package_weight_kg' => 'decimal:2',
        'price' => 'decimal:2',
        'courier_search_started_at' => 'datetime',
        'courier_offered_at' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->orderByDesc('id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(OrderVerification::class, 'order_id');
    }

    public function locationSnapshots(): HasMany
    {
        return $this->hasMany(CourierLocationSnapshot::class, 'order_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'order_id');
    }

    public function surveyTokens(): HasMany
    {
        return $this->hasMany(SurveyToken::class, 'order_id');
    }

    /**
     * ثبت تغییر وضعیت سفارش و ایجاد رکورد در جدول order_status_histories.
     *
     * همچنین ایونت بلادرنگ OrderStatusChanged را از طریق Reverb dispatch می‌کند
     * تا مشتری و پیک از وضعیت لحظه‌ای سفارش مطلع شوند.
     */
    public function changeStatus(string $newStatus, ?int $changedBy = null): void
    {
        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);

        $this->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy ?? $this->customer_id,
        ]);

        $this->broadcastStatusChange();
    }

    /**
     * نسخه‌ای از changeStatus برای تغییرات خودکار/سیستمی (بدون نسبت دادن به مشتری یا پیک)،
     * مثل شروع جستجوی پیک، پیشنهاد سفارش، یا کنسل خودکار پس از اتمام مهلت جستجو.
     *
     * همچنین ایونت بلادرنگ OrderStatusChanged را از طریق Reverb dispatch می‌کند
     * تا مشتری و پیک از وضعیت لحظه‌ای سفارش مطلع شوند.
     */
    public function changeStatusBySystem(string $newStatus): void
    {
        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);

        $this->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => null,
        ]);

        $this->broadcastStatusChange();
    }

    /**
     * ارسال ایونت بلادرنگ تغییر وضعیت سفارش از طریق Reverb.
     */
    private function broadcastStatusChange(): void
    {
        OrderStatusChanged::dispatch([
            'order_id' => $this->id,
            'status' => $this->status,
            'timestamp' => now()->toIso8601String(),
        ], $this->id);
    }

    /**
     * آغاز فرآیند جستجوی پیک: وضعیت به SEARCHING_COURIER تغییر می‌کند، زمان شروع جستجو
     * ثبت می‌شود و اولین Job جستجو به صف اضافه می‌شود.
     */
    public function startCourierSearch(): void
    {
        $this->update(['courier_search_started_at' => now()]);
        $this->changeStatusBySystem('SEARCHING_COURIER');

        SearchCourierForOrderJob::dispatch($this->id);
    }

    /**
     * آغاز مجدد جستجوی پیک پس از وضعیت COURIER_NOT_FOUND.
     *
     * تایمر جستجو از صفر ریست می‌شود (courier_search_started_at = now)
     * و وضعیت مجددا به SEARCHING_COURIER تغییر می‌کند.
     * این متد توسط کاربر از طریق اپ موبایل فراخوانی می‌شود.
     */
    public function restartCourierSearch(): void
    {
        $this->update(['courier_search_started_at' => now()]);
        $this->changeStatusBySystem('SEARCHING_COURIER');

        SearchCourierForOrderJob::dispatch($this->id);
    }

    /**
     * شناسه پیک‌هایی که قبلا پیشنهاد این سفارش را رد کرده‌اند - برای حذف از دور بعدی جستجو.
     *
     * @return array<int, int>
     */
    public function excludedCourierIds(): array
    {
        return $this->statusHistories()
            ->where('new_status', 'COURIER_REJECTED')
            ->whereNotNull('changed_by')
            ->pluck('changed_by')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isCancellable(): bool
    {
        return ! in_array($this->status, [...self::NON_CANCELLABLE_STATUSES, 'CANCELLED'], true);
    }
}
