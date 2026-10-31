<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });

        Schema::table('watches', function (Blueprint $table) {
            $table->unsignedInteger('weight_gram')->default(500);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_postal_code', 10)->nullable();
            $table->string('shipping_courier')->nullable();
            $table->string('shipping_service')->nullable();
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->string('payment_status')->default('paid')->index();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable()->unique();
            $table->text('payment_url')->nullable();
            $table->timestamp('payment_expires_at')->nullable();
            $table->uuid('tracking_token')->nullable()->unique();
            $table->string('tracking_number')->nullable();
            $table->string('shipping_order_id')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->boolean('stock_released')->default(false);
        });

        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('id')->constrained('orders')->nullOnDelete();
            $table->unique(['order_id', 'jam_id']);
        });
    }

    public function down(): void
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'jam_id']);
            $table->dropConstrainedForeignId('order_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payment_reference']);
            $table->dropUnique(['tracking_token']);
            $table->dropColumn([
                'customer_email',
                'customer_phone',
                'shipping_address',
                'shipping_postal_code',
                'shipping_courier',
                'shipping_service',
                'shipping_fee',
                'payment_status',
                'payment_method',
                'payment_reference',
                'payment_url',
                'payment_expires_at',
                'tracking_token',
                'tracking_number',
                'shipping_order_id',
                'shipped_at',
                'stock_released',
            ]);
        });

        Schema::table('watches', function (Blueprint $table) {
            $table->dropColumn('weight_gram');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
