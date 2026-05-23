<?php

namespace App\Services;

use App\Models\DeliveryProvider;
use App\Models\DeliveryProviderRoute;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderDeliveryShipment;

class ShipmentAssignmentService
{
    /**
     * Match every shipment on the order to an active provider:
     *  - Intra-city orders (pickup city == destination city) match providers
     *    of route_type INTRA_CITY whose coverage includes that city, OR
     *    INTER_CITY providers with offers_intra_city=true whose coverage
     *    includes that city.
     *  - Inter-city orders match INTER_CITY providers with a route whose
     *    ordered city sequence (origin + waypoints + destination) contains
     *    both the pickup city and the destination city — with destination
     *    appearing after pickup in the list.
     *
     * Deterministic: lowest provider id wins.
     */
    public function assignForOrder(Order $order): void
    {
        $destCity = $order->ship_city;

        foreach ($order->shipments as $shipment) {
            if ($shipment->shipment_status !== OrderDeliveryShipment::STATUS_AWAITING_PROVIDER) {
                continue;
            }

            $hub = $shipment->hub;
            $pickupCity = $hub?->city;
            if ($pickupCity === null || $destCity === null) {
                continue;
            }

            $providerId = $this->pickProvider($pickupCity, $destCity);
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

    private function pickProvider(string $pickupCity, string $destCity): ?int
    {
        $sameCity = mb_strtolower(trim($pickupCity)) === mb_strtolower(trim($destCity));

        return $sameCity
            ? $this->pickIntraCityProvider($pickupCity)
            : $this->pickInterCityProvider($pickupCity, $destCity);
    }

    /**
     * Intra-city: providers whose coverage_areas includes the city. Either
     * INTRA_CITY providers OR INTER_CITY providers with offers_intra_city.
     */
    private function pickIntraCityProvider(string $city): ?int
    {
        $zoneId = DeliveryZone::query()
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($city))])
            ->where('status', DeliveryZone::STATUS_ACTIVE)
            ->value('id');
        if ($zoneId === null) {
            return null;
        }

        return DeliveryProvider::query()
            ->where('status', DeliveryProvider::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->where('route_type', DeliveryProvider::ROUTE_INTRA_CITY)
                    ->orWhere(function ($qq) {
                        $qq->where('route_type', DeliveryProvider::ROUTE_INTER_CITY)
                            ->where('offers_intra_city', true);
                    });
            })
            ->whereHas('coverageAreas', fn ($q) => $q->where('delivery_zones.id', $zoneId))
            ->orderBy('id')
            ->value('id');
    }

    /**
     * Inter-city: scan each route's city sequence and pick the lowest
     * provider id with a route containing both cities in the correct order.
     */
    private function pickInterCityProvider(string $pickupCity, string $destCity): ?int
    {
        $pickup = mb_strtolower(trim($pickupCity));
        $dest = mb_strtolower(trim($destCity));

        $candidates = DeliveryProvider::query()
            ->with(['routes' => fn ($q) => $q->where('status', DeliveryProviderRoute::STATUS_ACTIVE)])
            ->where('status', DeliveryProvider::STATUS_ACTIVE)
            ->where('route_type', DeliveryProvider::ROUTE_INTER_CITY)
            ->orderBy('id')
            ->get();

        foreach ($candidates as $provider) {
            foreach ($provider->routes as $route) {
                $seq = $route->citySequence();
                $pickupIdx = array_search($pickup, $seq, true);
                $destIdx = array_search($dest, $seq, true);
                if ($pickupIdx === false || $destIdx === false) {
                    continue;
                }
                if ($destIdx > $pickupIdx) {
                    return $provider->id;
                }
            }
        }

        return null;
    }
}
