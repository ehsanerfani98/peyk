<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * لحظه شروع جستجوی پیک را نگه می‌داریم تا Job جستجو بتواند سقف ۵ دقیقه‌ای
     * (یا هر مقدار پیکربندی‌شده در config/courier_search.php) را محاسبه کند.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('courier_search_started_at')
                ->nullable()
                ->after('courier_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('courier_search_started_at');
        });
    }
};
