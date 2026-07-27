<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_current_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('courier_id')->primary();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->geometry('location', 'point');

            $table->timestamp('updated_at')->nullable();

            $table->foreign('courier_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_current_locations');
    }
};
