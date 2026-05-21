<?php

namespace App\Services;

use App\Interfaces\Repositories\IOrderInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    public function __construct(private readonly IOrderInterface $repo) {}

    public function listForUser(User $user): Collection
    {
        return $this->repo->listForUser($user->id);
    }

    public function findForUser(User $user, string $orderNumber): ?Order
    {
        return $this->repo->findForUser($orderNumber, $user->id);
    }
}
