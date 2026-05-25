<?php

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Recompute delivery_status on every order that has already moved past
        // PENDING_PAYMENT. Orders confirmed before the delivery_status feature
        // existed are sitting at the default 'PENDING' and need to roll forward.
        $service = app(OrderStatusService::class);

        Order::query()
            ->whereNotIn('status', [Order::STATUS_PENDING_PAYMENT, Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])
            ->with('shipments')
            ->chunkById(100, function ($orders) use ($service) {
                foreach ($orders as $order) {
                    $service->recomputeDeliveryStatus($order);
                }
            });
    }

    public function down(): void
    {
        // No-op: rolling delivery_status backwards is meaningless.
    }
};
