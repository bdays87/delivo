<?php

namespace App\Services;

use App\Interfaces\Repositories\IInfluencerEarningInterface;
use App\Models\InfluencerEarning;
use App\Models\Order;
use App\Models\OrderDeliveryShipment;
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

            // Start the vendor dropoff window for every shipment that hasn't
            // already shipped. The deadline is what the vendor sees on their
            // orders page; the rider can't pick up until dropped_off_at is set.
            $windowHours = (int) config('logistics.dropoff_window_hours', 48);
            foreach ($order->shipments as $shipment) {
                if ($shipment->dropped_off_at !== null) {
                    continue;
                }
                $shipment->forceFill([
                    'dropoff_deadline' => now()->addHours($windowHours),
                ])->save();
            }

            $this->recomputeDeliveryStatus($order->fresh('shipments'));
        });

        return ['order' => $order->fresh(['items'])];
    }

    /**
     * Recompute the order-level delivery_status from the state of all its
     * shipments. The order shows the "least advanced" shipment's stage so
     * the customer never sees INROUTE while one shipment is still waiting
     * to be dropped off.
     */
    public function recomputeDeliveryStatus(Order $order): void
    {
        if ($order->status === Order::STATUS_PENDING_PAYMENT) {
            $this->setDeliveryStatus($order, Order::DELIVERY_PENDING);

            return;
        }

        $shipments = $order->shipments;
        if ($shipments->isEmpty()) {
            $this->setDeliveryStatus($order, Order::DELIVERY_AWAITING_DROPOFF);

            return;
        }

        // Any shipment that hasn't been initiated drags the order back to
        // AWAITING_DROPOFF — the vendor still needs to act on it.
        $anyUninitiated = $shipments->contains(
            fn ($s) => $s->dropoff_initiated_at === null && $s->dropped_off_at === null,
        );
        if ($anyUninitiated) {
            $this->setDeliveryStatus($order, Order::DELIVERY_AWAITING_DROPOFF);

            return;
        }

        $allDroppedOff = $shipments->every(fn ($s) => $s->dropped_off_at !== null);
        if (! $allDroppedOff) {
            // Vendor initiated, awaiting hub admin to confirm receipt.
            $this->setDeliveryStatus($order, Order::DELIVERY_DROPOFF_INITIATED);

            return;
        }

        $allDispatched = $shipments->every(fn ($s) => in_array($s->shipment_status, [
            OrderDeliveryShipment::STATUS_OUT_FOR_DELIVERY,
            OrderDeliveryShipment::STATUS_DELIVERED,
        ], true));
        if (! $allDispatched) {
            $this->setDeliveryStatus($order, Order::DELIVERY_AWAITING_DISPATCH);

            return;
        }

        $allDelivered = $shipments->every(fn ($s) => $s->shipment_status === OrderDeliveryShipment::STATUS_DELIVERED);
        if (! $allDelivered) {
            $this->setDeliveryStatus($order, Order::DELIVERY_INROUTE);

            return;
        }

        $this->setDeliveryStatus($order, Order::DELIVERY_DELIVERED);
    }

    private function setDeliveryStatus(Order $order, string $delivery): void
    {
        if ($order->delivery_status === $delivery) {
            return;
        }
        $order->forceFill(['delivery_status' => $delivery])->save();
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
        if ($order->customer_delivery_confirmed_at !== null) {
            return ['error' => 'You already confirmed delivery on this order.', 'code' => 422];
        }

        // Customer confirms receipt — this is a signal to Delivo admin, not
        // the close-out itself. Admin still has to mark the order DELIVERED
        // to release influencer earnings and finalise the order.
        $order->forceFill(['customer_delivery_confirmed_at' => now()])->save();

        return ['order' => $order->fresh(['items'])];
    }

    /**
     * Admin-only: mark every shipment on this order as dropped off at its
     * hub. This unblocks rider pickup and rolls the order's delivery_status
     * forward to AWAITING_DISPATCH.
     */
    public function adminMarkDroppedOff(Order $order): array
    {
        if ($order->status !== Order::STATUS_PAID) {
            return ['error' => 'Only paid orders can be marked as dropped off.', 'code' => 422];
        }
        if ($order->delivery_status !== Order::DELIVERY_DROPOFF_INITIATED) {
            return [
                'error' => 'Vendor must initiate the dropoff before hub staff can confirm receipt.',
                'code' => 422,
            ];
        }

        DB::transaction(function () use ($order) {
            foreach ($order->shipments as $shipment) {
                if ($shipment->dropped_off_at !== null) {
                    continue;
                }
                $shipment->forceFill(['dropped_off_at' => now()])->save();
            }
            $this->recomputeDeliveryStatus($order->fresh('shipments'));
        });

        return ['order' => $order->fresh(['items', 'shipments'])];
    }

    /**
     * Admin-only close-out. Flips order.status to DELIVERED, stamps
     * delivered_at, and releases any pending influencer earnings.
     */
    public function adminMarkDelivered(Order $order): array
    {
        if ($order->status === Order::STATUS_DELIVERED || $order->status === Order::STATUS_COMPLETED) {
            return ['error' => 'This order is already marked delivered.', 'code' => 422];
        }
        if ($order->status !== Order::STATUS_PAID) {
            return ['error' => 'Only paid orders can be marked delivered.', 'code' => 422];
        }

        DB::transaction(function () use ($order) {
            $order->forceFill([
                'status' => Order::STATUS_DELIVERED,
                'delivered_at' => now(),
            ])->save();

            $this->earnings->clearForOrder($order->id);
            $this->recomputeDeliveryStatus($order->fresh('shipments'));
        });

        return ['order' => $order->fresh(['items'])];
    }
}
