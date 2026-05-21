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
            $table->string('order_number', 32)->unique(); // DLV-26-000001
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('mobile_wallet_id')->nullable()->constrained('mobile_wallets')->nullOnDelete();

            // Snapshot of the delivery address as text — survives address deletion/edits.
            $table->string('ship_recipient_name', 120);
            $table->string('ship_recipient_phone', 40);
            $table->string('ship_city', 80);
            $table->string('ship_suburb', 120);
            $table->string('ship_street', 200);
            $table->text('ship_notes')->nullable();

            $table->string('status', 24)->default('PENDING_PAYMENT');
            // PENDING_PAYMENT | PAID | PICKED_UP | OUT_FOR_DELIVERY | DELIVERED | COMPLETED | CANCELLED | REFUNDED

            $table->decimal('subtotal_usd', 12, 2);
            $table->decimal('shipping_usd', 12, 2)->default(0);
            $table->decimal('total_usd', 12, 2);
            $table->decimal('usd_to_zwg_rate', 18, 6)->nullable();

            $table->string('payment_reference', 40);
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
