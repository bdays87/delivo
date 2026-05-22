<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_delivery_shipments', function (Blueprint $table) {
            $table->foreignId('delivery_provider_id')
                ->nullable()
                ->after('hub_id')
                ->constrained('delivery_providers')
                ->nullOnDelete();
            $table->string('shipment_status', 24)
                ->default('AWAITING_PROVIDER')
                ->after('fee_usd');
            // AWAITING_PROVIDER | ASSIGNED | PICKED_UP | OUT_FOR_DELIVERY | DELIVERED | CANCELLED
            $table->timestamp('assigned_at')->nullable()->after('shipment_status');
            $table->timestamp('picked_up_at')->nullable()->after('assigned_at');
            $table->timestamp('out_for_delivery_at')->nullable()->after('picked_up_at');
            $table->timestamp('delivered_at')->nullable()->after('out_for_delivery_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_delivery_shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_provider_id');
            $table->dropColumn([
                'shipment_status',
                'assigned_at',
                'picked_up_at',
                'out_for_delivery_at',
                'delivered_at',
            ]);
        });
    }
};
