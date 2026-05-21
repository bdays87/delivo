<?php

namespace App\Interfaces\Repositories;

use App\Models\ExchangeRate;

interface IExchangeRateInterface extends IBaseInterface
{
    public function find(string $from, string $to): ?ExchangeRate;

    public function upsert(string $from, string $to, string $rate, ?int $userId): ExchangeRate;
}
