<?php

namespace App\Services;

use App\Interfaces\Repositories\IDeliveryZoneInterface;
use App\Interfaces\Repositories\IPlatformSettingsInterface;
use App\Models\Address;

class PricingService
{
    public function __construct(
        private readonly IPlatformSettingsInterface $settings,
        private readonly IDeliveryZoneInterface $zones,
    ) {}

    /**
     * Compute the service charge for a USD subtotal. Service charge is a
     * percentage of subtotal floored at the platform's configured minimum.
     * Returns 0 when subtotal is 0.
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
     * Strict coverage: resolves the delivery fee for a city. Returns null
     * when the city isn't in the active coverage list — callers translate
     * that into a 422 "we don't deliver to {city} yet".
     *
     * @return array{0:?float,1:?string} [fee_usd, zone_label]
     */
    public function deliveryFeeFor(?string $city): array
    {
        if ($city === null || trim($city) === '') {
            return [null, null];
        }

        $zone = $this->zones->findByCity($city);
        if ($zone === null) {
            return [null, null];
        }

        return [(float) $zone->fee_usd, $zone->city];
    }

    public function deliveryFeeForAddress(?Address $address): array
    {
        return $this->deliveryFeeFor($address?->city);
    }

    /**
     * Compute the full breakdown for a cart and (optionally) a delivery
     * address. When no address is supplied OR the address city isn't in
     * the active coverage list, shipping comes back as null and the caller
     * decides whether to render "calculated at checkout" or a coverage
     * error.
     */
    public function quote(float $subtotalUsd, ?Address $address): array
    {
        $service = round($this->serviceChargeFor($subtotalUsd), 2);

        if ($address === null) {
            return [
                'subtotal_usd' => $subtotalUsd,
                'service_charge_usd' => $service,
                'shipping_usd' => null,
                'shipping_zone' => null,
                'is_covered' => null,
                'items_total_usd' => round($subtotalUsd + $service, 2),
                'total_usd' => null,
            ];
        }

        [$shipping, $zoneLabel] = $this->deliveryFeeForAddress($address);

        if ($shipping === null) {
            return [
                'subtotal_usd' => $subtotalUsd,
                'service_charge_usd' => $service,
                'shipping_usd' => null,
                'shipping_zone' => null,
                'is_covered' => false,
                'items_total_usd' => round($subtotalUsd + $service, 2),
                'total_usd' => null,
            ];
        }

        return [
            'subtotal_usd' => $subtotalUsd,
            'service_charge_usd' => $service,
            'shipping_usd' => $shipping,
            'shipping_zone' => $zoneLabel,
            'is_covered' => true,
            'items_total_usd' => round($subtotalUsd + $service, 2),
            'total_usd' => round($subtotalUsd + $service + $shipping, 2),
        ];
    }
}
