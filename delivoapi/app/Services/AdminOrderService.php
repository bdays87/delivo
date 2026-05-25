<?php

namespace App\Services;

use App\Interfaces\Repositories\IOrderInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class AdminOrderService
{
    public function __construct(
        private readonly IOrderInterface $repo,
        private readonly OrderStatusService $statusSvc,
    ) {}

    public function listByStatus(?string $status = null): Collection
    {
        $query = Order::query()
            ->with(['user:id,name,email', 'mobileWallet:id,name,code'])
            ->latest('id');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function find(string $orderNumber): ?Order
    {
        return Order::query()
            ->with(['user:id,name,email', 'mobileWallet:id,name,code', 'items', 'shipments'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function confirmPayment(Order $order): array
    {
        return $this->statusSvc->confirmPayment($order);
    }
}
