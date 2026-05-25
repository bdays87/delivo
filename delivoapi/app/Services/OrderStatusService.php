<?php

namespace App\Services;

use App\Interfaces\Repositories\IInfluencerEarningInterface;
use App\Models\InfluencerEarning;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Coordinates order status transitions that have side-effects on the
 * influencer earnings ledger:
 *   - confirmPayment: PENDING_PAYMENT -> PAID, credits PENDING earnings.
 *   - confirmDelivery: PAID -> DELIVERED (when customer enters delivery_code),
 *     clears matching ledger entries so the influencer can request payout.
 */
class OrderStatusService
{
    public function __construct(
        private readonly IInfluencerEarningInterface $earnings,
    ) {}

    public function confirmPayment(Order $order): array
    {
        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            return ['error' => 'Only orders awaiting payment can be confirmed.', 'code' => 422];
        }

        DB::transaction(function () use ($order) {
            $order->forceFill([
                'status' => Order::STATUS_PAID,
                'payment_confirmed_at' => now(),
            ])->save();

            // Credit one PENDING ledger entry per order item with an influencer
            // attribution + non-zero commission snapshot. unique(order_item_id)
            // means a second confirmPayment is a no-op.
            foreach ($order->items as $item) {
                if ($item->influencer_id === null) {
                    continue;
                }
                $amount = (float) $item->line_commission_usd_snapshot;
                if ($amount <= 0) {
                    continue;
                }

                InfluencerEarning::query()->updateOrCreate(
                    ['order_item_id' => $item->id],
                    [
                        'influencer_id' => $item->influencer_id,
                        'order_id' => $order->id,
                        'amount_usd' => $amount,
                        'status' => InfluencerEarning::STATUS_PENDING,
                    ],
                );
            }
        });

        return ['order' => $order->fresh(['items'])];
    }

    public function confirmDelivery(Order $order, string $code): array
    {
        if ($order->status !== Order::STATUS_PAID && $order->status !== Order::STATUS_OUT_FOR_DELIVERY) {
            return ['error' => 'This order is not ready for delivery confirmation.', 'code' => 422];
        }
        if ($order->delivery_code === null || $order->delivery_code === '') {
            return ['error' => 'No delivery code on file for this order.', 'code' => 422];
        }
        if (! hash_equals((string) $order->delivery_code, trim($code))) {
            return ['error' => 'Delivery code is incorrect.', 'code' => 422];
        }

        DB::transaction(function () use ($order) {
            $order->forceFill([
                'status' => Order::STATUS_DELIVERED,
                'delivered_at' => now(),
            ])->save();

            $this->earnings->clearForOrder($order->id);
        });

        return ['order' => $order->fresh(['items'])];
    }
}
