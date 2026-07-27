<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->boolean('sender_is_customer')->default(true);
            $table->string('sender_name');
            $table->string('sender_mobile', 20);
            $table->text('sender_address');
            $table->decimal('sender_lat', 10, 7);
            $table->decimal('sender_lng', 10, 7);

            $table->boolean('receiver_is_customer')->default(false);
            $table->string('receiver_name');
            $table->string('receiver_mobile', 20);
            $table->text('receiver_address');
            $table->decimal('receiver_lat', 10, 7);
            $table->decimal('receiver_lng', 10, 7);

            $table->text('package_description')->nullable();
            $table->decimal('package_weight_kg', 6, 2)->nullable();
            $table->enum('package_size', ['small', 'medium', 'large'])->default('small');

            $table->enum('payment_method', ['cash_on_delivery', 'online'])->default('cash_on_delivery');
            $table->enum('payment_by', ['sender', 'receiver'])->default('sender');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->decimal('price', 12, 2)->default(0);

            $table->enum('status', [
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
            ])->default('CREATED')->index();

            $table->foreignId('courier_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('cancelled_by', ['customer', 'courier', 'admin', 'system'])->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
