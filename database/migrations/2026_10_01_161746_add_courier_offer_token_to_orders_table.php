<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * توکن لینک تایید پیشنهاد سفارش توسط پیک (لینک قابل کلیک در مرورگر)
     * و زمان ارسال پیامک این پیشنهاد، برای تضمین ارسال فقط یک‌باره پیامک.
     *
     * هر پیشنهاد جدید، توکن تازه می‌گیرد و courier_offer_sms_sent_at صفر می‌شود.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_offer_token', 64)
                ->nullable()
                ->unique()
                ->after('courier_offered_at');

            $table->timestamp('courier_offer_sms_sent_at')
                ->nullable()
                ->after('courier_offer_token');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['courier_offer_token']);
            $table->dropColumn(['courier_offer_token', 'courier_offer_sms_sent_at']);
        });
    }
};
