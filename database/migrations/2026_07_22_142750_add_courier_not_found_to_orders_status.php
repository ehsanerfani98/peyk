<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * افزودن وضعیت COURIER_NOT_FOUND به ستون status جدول orders.
     *
     * این وضعیت نشان‌دهنده پایان مهلت جستجوی پیک بدون یافتن پیک است.
     * برخلاف CANCELLED، سفارش همچنان فعال است و کاربر می‌تواند
     * جستجو را مجددا آغاز کند یا سفارش را لغو نماید.
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
            'CANCELLED'
        ) NOT NULL DEFAULT 'CREATED'");
    }
};
