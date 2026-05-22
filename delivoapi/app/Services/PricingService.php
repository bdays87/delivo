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
     * Resolve the delivery fee for a delivery city. Returns the per-zone fee
     * if the city matches an active zone, otherwise the platform default.
     * Second element is a label like "Harare" or "Default zone" for UI.
     *
     * @return array{0:float,1:string}
     */
    public function deliveryFeeFor(?string $city): array
    {
        $default = (float) $this->settings->current()->default_delivery_fee_usd;

        if ($city === null || trim($city) === '') {
            return [$default, 'Default zone'];
        }

        $zone = $this->zones->findByCity($city);
        if ($zone === null) {
            return [$default, 'Default zone'];
        }

        return [(float) $zone->fee_usd, $zone->city];
    }

    public function deliveryFeeForAddress(?Address $address): array
    {
        return $this->deliveryFeeFor($address?->city);
    }

    /**
     * Compute the full breakdown for a cart and (optionally) a delivery
     * address. When no address is supplied, shipping comes back as null so
     * the UI can render "calculated at checkout".
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
                'items_total_usd' => round($subtotalUsd + $service, 2),
                'total_usd' => null,
            ];
        }

        [$shipping, $zoneLabel] = $this->deliveryFeeForAddress($address);

        return [
            'subtotal_usd' => $subtotalUsd,
            'service_charge_usd' => $service,
            'shipping_usd' => $shipping,
            'shipping_zone' => $zoneLabel,
            'items_total_usd' => round($subtotalUsd + $service, 2),
            'total_usd' => round($subtotalUsd + $service + $shipping, 2),
        ];
    }
}
