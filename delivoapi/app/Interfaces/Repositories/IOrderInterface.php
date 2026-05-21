<?php

namespace App\Interfaces\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface IOrderInterface extends IBaseInterface
{
    public function listForUser(int $userId): Collection;

    public function findForUser(string $orderNumber, int $userId): ?Order;

    public function nextSequenceForYear(int $year): int;
}
