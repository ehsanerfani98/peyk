<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * تغییر enum snapshot_type: جایگزینی 'midpoint' با 'en_route'
     * تا امکان ثبت چندین اسنپ‌شات در طول مسیر فراهم شود.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE courier_location_snapshots MODIFY COLUMN snapshot_type ENUM('pickup', 'delivery', 'en_route') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE courier_location_snapshots MODIFY COLUMN snapshot_type ENUM('pickup', 'delivery', 'midpoint') NOT NULL");
    }
};
