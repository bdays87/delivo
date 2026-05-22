<?php

namespace App\Services;

use App\Models\DeliveryProvider;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderDeliveryShipment;

class ShipmentAssignmentService
{
    /**
     * Try to assign every shipment on the order to an active delivery
     * provider whose coverage includes BOTH the dispatch hub city AND the
     * destination city. Deterministic: picks the lowest provider id when
     * multiple match. Shipments that don't match any provider stay in
     * AWAITING_PROVIDER for admin manual assignment.
     */
    public function assignForOrder(Order $order): void
    {
        $destZone = DeliveryZone::query()
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($order->ship_city))])
            ->where('status', DeliveryZone::STATUS_ACTIVE)
            ->first();

        foreach ($order->shipments as $shipment) {
            if ($shipment->shipment_status !== OrderDeliveryShipment::STATUS_AWAITING_PROVIDER) {
                continue;
            }

            $hubZoneId = $shipment->hub_id;
            if ($hubZoneId === null || $destZone === null) {
                continue;
            }

            $providerId = $this->pickProvider($hubZoneId, $destZone->id);
            if ($providerId === null) {
                continue;
            }

            $shipment->forceFill([
                'delivery_provider_id' => $providerId,
                'shipment_status' => OrderDeliveryShipment::STATUS_ASSIGNED,
                'assigned_at' => now(),
            ])->save();
        }
    }

    /**
     * Lowest-id active provider whose coverage includes both zone IDs.
     * Same hub+destination → coverage must contain that single zone.
     */
    private function pickProvider(int $hubZoneId, int $destZoneId): ?int
    {
        $zoneIds = array_unique([$hubZoneId, $destZoneId]);

        return DeliveryProvider::query()
            ->where('status', DeliveryProvider::STATUS_ACTIVE)
            ->whereHas('coverageAreas', fn ($q) => $q->whereIn('delivery_zones.id', $zoneIds), '=', count($zoneIds))
            ->orderBy('id')
            ->value('id');
    }
}
