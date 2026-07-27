<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * لحظه‌ای که آخرین پیشنهاد سفارش به یک پیک داده شده است.
     * برای محاسبه مهلت پاسخ پیک (courier_offer_timeout_seconds) استفاده می‌شود.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('courier_offered_at')
                ->nullable()
                ->after('courier_search_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('courier_offered_at');
        });
    }
};
