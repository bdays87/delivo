<?php

namespace App\Services;

use App\Interfaces\Repositories\IExchangeRateInterface;
use App\Models\ExchangeRate;
use App\Models\User;

class ExchangeRateService
{
    public function __construct(private readonly IExchangeRateInterface $rates) {}

    public function get(string $from, string $to): ?ExchangeRate
    {
        return $this->rates->find($from, $to);
    }

    public function setUsdToZwg(User $user, string $rate): ExchangeRate
    {
        return $this->rates->upsert('USD', 'ZWG', $rate, $user->id);
    }

    public function usdToZwg(): ?ExchangeRate
    {
        return $this->rates->find('USD', 'ZWG');
    }
}
