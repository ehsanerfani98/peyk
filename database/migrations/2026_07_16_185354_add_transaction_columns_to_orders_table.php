<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_authority')->nullable()->after('payment_url');
            $table->string('payment_driver')->nullable()->after('payment_authority');
            $table->string('payment_ref_id')->nullable()->after('payment_driver');
            $table->timestamp('paid_at')->nullable()->after('payment_ref_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_authority',
                'payment_driver',
                'payment_ref_id',
                'paid_at',
            ]);
        });
    }
};
