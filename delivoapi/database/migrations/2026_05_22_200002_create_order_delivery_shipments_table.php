<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-vendor shipment breakdown for an order. Multi-vendor carts get
     * one row per vendor — distance from that vendor's city hub to the
     * customer's address, and the fee charged for that leg.
     */
    public function up(): void
    {
        Schema::create('order_delivery_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->foreignId('hub_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->string('hub_name_snapshot', 150)->nullable();
            $table->string('hub_address_snapshot', 255)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->decimal('fee_usd', 12, 2);
            $table->foreignId('delivery_fee_id')->nullable()->constrained('delivery_fees')->nullOnDelete();
            $table->timestamps();

            $table->index('order_id');
            $table->unique(['order_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_delivery_shipments');
    }
};
