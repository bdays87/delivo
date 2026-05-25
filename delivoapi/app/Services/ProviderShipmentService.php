<?php

namespace App\Services;

use App\Models\DeliveryProvider;
use App\Models\OrderDeliveryShipment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProviderShipmentService
{
    public function listForProvider(DeliveryProvider $provider, ?string $status = null): Collection
    {
        $query = OrderDeliveryShipment::query()
            ->with([
                'order:id,order_number,status,ship_recipient_name,ship_recipient_phone,ship_city,ship_suburb,ship_street,ship_notes',
                'vendor:id,business_name,city',
                'hub:id,city,hub_name,hub_address',
            ])
            ->where('delivery_provider_id', $provider->id)
            ->latest('assigned_at');

        if ($status !== null) {
            $query->where('shipment_status', $status);
        }

        return $query->get();
    }

    public function findForProvider(DeliveryProvider $provider, int $shipmentId): ?OrderDeliveryShipment
    {
        return OrderDeliveryShipment::query()
            ->with([
                'order:id,order_number,status,ship_recipient_name,ship_recipient_phone,ship_city,ship_suburb,ship_street,ship_notes',
                'vendor:id,business_name,city',
                'hub:id,city,hub_name,hub_address',
            ])
            ->where('delivery_provider_id', $provider->id)
            ->where('id', $shipmentId)
            ->first();
    }

    /**
     * Transition the shipment forward through the pipeline. Each step has a
     * strict precondition: ASSIGNED → PICKED_UP → OUT_FOR_DELIVERY → DELIVERED.
     * Out-of-order transitions return false; the caller turns that into a 409.
     */
    public function pickup(OrderDeliveryShipment $shipment): bool
    {
        // Rider can't pick up until the vendor has confirmed dropoff at the hub.
        if ($shipment->dropped_off_at === null) {
            return false;
        }

        return $this->transition($shipment, OrderDeliveryShipment::STATUS_ASSIGNED, OrderDeliveryShipment::STATUS_PICKED_UP, 'picked_up_at');
    }

    public function dispatchShipment(OrderDeliveryShipment $shipment): bool
    {
        return $this->transition($shipment, OrderDeliveryShipment::STATUS_PICKED_UP, OrderDeliveryShipment::STATUS_OUT_FOR_DELIVERY, 'out_for_delivery_at');
    }

    /**
     * Rider records the customer-read delivery code as proof of handover.
     * Returns one of: 'ok' (delivered), 'invalid_code', 'wrong_state'.
     */
    public function deliver(OrderDeliveryShipment $shipment, string $code): string
    {
        $order = $shipment->order;
        if ($order === null) {
            return 'wrong_state';
        }
        if ($order->delivery_code === null || $order->delivery_code === '') {
            return 'invalid_code';
        }
        if (! hash_equals((string) $order->delivery_code, trim($code))) {
            return 'invalid_code';
        }
        if ($shipment->shipment_status !== OrderDeliveryShipment::STATUS_OUT_FOR_DELIVERY) {
            return 'wrong_state';
        }

        $ok = $this->transition(
            $shipment,
            OrderDeliveryShipment::STATUS_OUT_FOR_DELIVERY,
            OrderDeliveryShipment::STATUS_DELIVERED,
            'delivered_at',
        );
        if (! $ok) {
            return 'wrong_state';
        }

        // Customer's receipt is implicit when the rider enters the right code.
        $order->forceFill(['customer_delivery_confirmed_at' => now()])->save();

        return 'ok';
    }

    private function transition(OrderDeliveryShipment $shipment, string $from, string $to, string $timestampField): bool
    {
        if ($shipment->shipment_status !== $from) {
            return false;
        }

        return DB::transaction(function () use ($shipment, $to, $timestampField) {
            $shipment->forceFill([
                'shipment_status' => $to,
                $timestampField => now(),
            ])->save();

            $order = $shipment->order()->with('shipments')->first();
            if ($order !== null) {
                app(OrderStatusService::class)->recomputeDeliveryStatus($order);
            }

            return true;
        });
    }
}
