<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IExchangeRateInterface;
use App\Models\ExchangeRate;

class ExchangeRateRepository extends BaseRepository implements IExchangeRateInterface
{
    public function __construct(ExchangeRate $model)
    {
        parent::__construct($model);
    }

    public function find(string $from, string $to): ?ExchangeRate
    {
        return $this->model->where('from_currency', $from)->where('to_currency', $to)->first();
    }

    public function upsert(string $from, string $to, string $rate, ?int $userId): ExchangeRate
    {
        return $this->model->updateOrCreate(
            ['from_currency' => $from, 'to_currency' => $to],
            ['rate' => $rate, 'updated_by_user_id' => $userId],
        );
    }
}
