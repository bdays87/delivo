<?php

namespace App\Services;

use App\Interfaces\Repositories\IDeliveryFeeInterface;
use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Interfaces\Repositories\IPlatformSettingsInterface;
use App\Models\Address;
use App\Models\Cart;
use App\Models\DeliveryZone;
use App\Models\Vendor;

class PricingService
{
    public function __construct(
        private readonly IPlatformSettingsInterface $settings,
        private readonly IDeliveryZoneInterface $zones,
        private readonly IDeliveryFeeInterface $bands,
        private readonly GoogleDistanceMatrixService $distance,
    ) {}

    /**
     * Service charge: % of subtotal, with a USD floor. Zero on empty carts.
     */
    public function serviceChargeFor(float $subtotalUsd): float
    {
        if ($subtotalUsd <= 0) {
            return 0.0;
        }
        $cfg = $this->settings->current();
        $pct = (float) $cfg->service_charge_pct;
        $min = (float) $cfg->service_charge_min_usd;
        $raw = round($subtotalUsd * ($pct / 100), 2);

        return max($raw, $min);
    }

    /**
     * Per-vendor shipment fees for a cart delivered to the given address.
     *
     * Strategy:
     *  - Group cart items by vendor.
     *  - For each vendor's city, look up the dispatch hub in delivery_zones.
     *    Vendor in an uncovered city is a hard fail.
     *  - Ask Google Distance Matrix for the km between the hub and the
     *    customer's address. Failure → mark the shipment as uncovered (the
     *    customer is told we can't deliver to that address).
     *  - Find the delivery_fees band that covers the km. Missing band →
     *    uncovered (admin needs to extend the table).
     *
     * @return array{shipments: array<int, array<string, mixed>>, all_covered: bool}
     */
    public function shipmentsForCart(Cart $cart, ?Address $address): array
    {
        $shipments = [];
        $allCovered = true;

        if ($address === null) {
            // No address yet — caller renders "calculated at checkout".
            $byVendor = $this->groupByVendor($cart);
            foreach ($byVendor as $vendorId => $info) {
                $shipments[] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $info['name'],
                    'vendor_city' => $info['city'],
                    'hub' => null,
                    'distance_km' => null,
                    'fee_usd' => null,
                    'band_id' => null,
                    'is_covered' => null,
                    'reason' => null,
                ];
            }

            return ['shipments' => $shipments, 'all_covered' => false];
        }

        $destinationRef = $this->destinationStringFromAddress($address);

        foreach ($this->groupByVendor($cart) as $vendorId => $info) {
            $hub = $this->zones->findByCity($info['city']);
            if ($hub === null) {
                $allCovered = false;
                $shipments[] = $this->uncoveredShipment(
                    $vendorId,
                    $info,
                    null,
                    "Vendor city '{$info['city']}' has no active dispatch hub.",
                );

                continue;
            }

            $km = $this->distance->distanceKm($hub->originRef(), $destinationRef);
            if ($km === null) {
                // Haversine fallback: when Google can't resolve (missing key,
                // quota, network), fall back to the great-circle distance
                // between the two cities' hub centroids. Approximate but lets
                // checkout function in dev / under outage.
                $km = $this->haversineFallback($hub, $address);
            }

            if ($km === null) {
                $allCovered = false;
                $shipments[] = $this->uncoveredShipment(
                    $vendorId,
                    $info,
                    $hub,
                    "We couldn't resolve a route to this address.",
                );

                continue;
            }

            $band = $this->bands->findBandForDistance($km);
            if ($band === null) {
                $allCovered = false;
                $shipments[] = $this->uncoveredShipment(
                    $vendorId,
                    $info,
                    $hub,
                    "No delivery fee band configured for {$km} km.",
                    $km,
                );

                continue;
            }

            $shipments[] = [
                'vendor_id' => $vendorId,
                'vendor_name' => $info['name'],
                'vendor_city' => $info['city'],
                'hub' => $this->hubPayload($hub),
                'distance_km' => $km,
                'fee_usd' => (float) $band->fee_usd,
                'band_id' => $band->id,
                'is_covered' => true,
                'reason' => null,
            ];
        }

        return ['shipments' => $shipments, 'all_covered' => $allCovered && ! empty($shipments)];
    }

    /**
     * Full quote breakdown for a cart against an address. Shipping_usd is
     * the sum of per-vendor fees when fully covered, else null.
     *
     * @return array<string, mixed>
     */
    public function quote(Cart $cart, float $subtotalUsd, ?Address $address): array
    {
        $service = round($this->serviceChargeFor($subtotalUsd), 2);
        $shipmentResult = $this->shipmentsForCart($cart, $address);

        $shippingTotal = null;
        $totalUsd = null;
        if ($shipmentResult['all_covered']) {
            $shippingTotal = round(
                array_sum(array_map(fn ($s) => (float) ($s['fee_usd'] ?? 0), $shipmentResult['shipments'])),
                2,
            );
            $totalUsd = round($subtotalUsd + $service + $shippingTotal, 2);
        }

        return [
            'subtotal_usd' => $subtotalUsd,
            'service_charge_usd' => $service,
            'shipping_usd' => $shippingTotal,
            'is_covered' => $address === null ? null : $shipmentResult['all_covered'],
            'items_total_usd' => round($subtotalUsd + $service, 2),
            'total_usd' => $totalUsd,
            'shipments' => $shipmentResult['shipments'],
        ];
    }

    /**
     * @return array<int, array{name: string, city: string}>
     */
    private function groupByVendor(Cart $cart): array
    {
        $out = [];
        foreach ($cart->items as $item) {
            $product = $item->product;
            if ($product === null || $product->vendor === null) {
                continue;
            }
            /** @var Vendor $vendor */
            $vendor = $product->vendor;
            if (! isset($out[$vendor->id])) {
                $out[$vendor->id] = [
                    'name' => $vendor->business_name,
                    'city' => $vendor->city ?? '',
                ];
            }
        }

        return $out;
    }

    private function uncoveredShipment(int $vendorId, array $info, ?DeliveryZone $hub, string $reason, ?float $km = null): array
    {
        return [
            'vendor_id' => $vendorId,
            'vendor_name' => $info['name'],
            'vendor_city' => $info['city'],
            'hub' => $hub ? $this->hubPayload($hub) : null,
            'distance_km' => $km,
            'fee_usd' => null,
            'band_id' => null,
            'is_covered' => false,
            'reason' => $reason,
        ];
    }

    private function hubPayload(DeliveryZone $hub): array
    {
        return [
            'id' => $hub->id,
            'city' => $hub->city,
            'name' => $hub->hub_name,
            'address' => $hub->hub_address,
        ];
    }

    private function haversineFallback(DeliveryZone $hub, Address $address): ?float
    {
        if ($hub->hub_latitude === null || $hub->hub_longitude === null) {
            return null;
        }

        $destZone = $this->zones->findByCity($address->city);
        if ($destZone === null
            || $destZone->hub_latitude === null
            || $destZone->hub_longitude === null) {
            return null;
        }

        $earthKm = 6371.0;
        $lat1 = deg2rad((float) $hub->hub_latitude);
        $lat2 = deg2rad((float) $destZone->hub_latitude);
        $dLat = $lat2 - $lat1;
        $dLng = deg2rad((float) $destZone->hub_longitude - (float) $hub->hub_longitude);

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Multiply by a 1.25 factor to approximate road distance vs straight-line.
        return round($earthKm * $c * 1.25, 2);
    }

    private function destinationStringFromAddress(Address $address): string
    {
        $parts = array_filter([
            $address->street,
            $address->suburb,
            $address->city,
            'Zimbabwe',
        ]);

        return implode(', ', $parts);
    }
}
