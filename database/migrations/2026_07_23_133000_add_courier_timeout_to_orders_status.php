<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * افزودن وضعیت COURIER_TIMEOUT به ستون status جدول orders.
     *
     * این وضعیت نشان‌دهنده پایان مهلت پاسخ پیک به پیشنهاد سفارش است
     * (پیک نه قبول کرده و نه رد کرده است).
     *
     * تفاوت با COURIER_REJECTED:
     * - COURIER_REJECTED: پیک آگاهانه و فعالانه پیشنهاد را رد کرده است
     *   → پیک در excludedCourierIds قرار می‌گیرد و دیگر برای این سفارش انتخاب نمی‌شود
     * - COURIER_TIMEOUT: پیک در بازه زمانی تعیین‌شده پاسخی نداده است
     *   → پیک در excludedCourierIds قرار نمی‌گیرد و می‌تواند در دورهای بعدی
     *     جستجو (در صورت آنلاین بودن) مجددا انتخاب شود
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'CREATED',
            'WAITING_SENDER_VERIFY',
            'SENDER_VERIFIED',
            'WAITING_RECEIVER_VERIFY',
            'RECEIVER_VERIFIED',
            'SEARCHING_COURIER',
            'COURIER_OFFERED',
            'COURIER_ACCEPTED',
            'COURIER_REJECTED',
            'COURIER_TIMEOUT',
            'COURIER_ASSIGNED',
            'WAITING_PICKUP',
            'PICKED_UP',
            'IN_TRANSIT',
            'DELIVERED',
            'DELIVERY_FAILED',
            'RETURNED_TO_SENDER',
            'CANCELLED',
            'COURIER_NOT_FOUND'
        ) NOT NULL DEFAULT 'CREATED'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'CREATED',
            'WAITING_SENDER_VERIFY',
            'SENDER_VERIFIED',
            'WAITING_RECEIVER_VERIFY',
            'RECEIVER_VERIFIED',
            'SEARCHING_COURIER',
            'COURIER_OFFERED',
            'COURIER_ACCEPTED',
            'COURIER_REJECTED',
            'COURIER_ASSIGNED',
            'WAITING_PICKUP',
            'PICKED_UP',
            'IN_TRANSIT',
            'DELIVERED',
            'DELIVERY_FAILED',
            'RETURNED_TO_SENDER',
            'CANCELLED',
            'COURIER_NOT_FOUND'
        ) NOT NULL DEFAULT 'CREATED'");
    }
};
