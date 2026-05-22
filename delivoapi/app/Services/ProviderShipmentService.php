<?php

namespace App\Services;

use App\Models\DeliveryProvider;
use App\Models\Order;
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
        return $this->transition($shipment, OrderDeliveryShipment::STATUS_ASSIGNED, OrderDeliveryShipment::STATUS_PICKED_UP, 'picked_up_at');
    }

    public function dispatchShipment(OrderDeliveryShipment $shipment): bool
    {
        return $this->transition($shipment, OrderDeliveryShipment::STATUS_PICKED_UP, OrderDeliveryShipment::STATUS_OUT_FOR_DELIVERY, 'out_for_delivery_at');
    }

    public function deliver(OrderDeliveryShipment $shipment): bool
    {
        $ok = $this->transition($shipment, OrderDeliveryShipment::STATUS_OUT_FOR_DELIVERY, OrderDeliveryShipment::STATUS_DELIVERED, 'delivered_at');
        if ($ok) {
            $this->maybeFinaliseOrder($shipment->order_id);
        }

        return $ok;
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

            return true;
        });
    }

    /**
     * If every shipment on the order is DELIVERED, mark the order itself
     * delivered too. Customer's order timeline reflects the same status.
     */
    private function maybeFinaliseOrder(int $orderId): void
    {
        $order = Order::query()->with('shipments:id,order_id,shipment_status')->find($orderId);
        if ($order === null) {
            return;
        }
        if ($order->shipments->isEmpty()) {
            return;
        }
        $allDelivered = $order->shipments->every(
            fn ($s) => $s->shipment_status === OrderDeliveryShipment::STATUS_DELIVERED,
        );
        if ($allDelivered && $order->status !== Order::STATUS_DELIVERED) {
            $order->forceFill(['status' => Order::STATUS_DELIVERED])->save();
        }
    }
}
