<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IOrderInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository implements IOrderInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function listForUser(int $userId): Collection
    {
        return $this->model
            ->with(['items', 'mobileWallet:id,name,code'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function findForUser(string $orderNumber, int $userId): ?Order
    {
        return $this->model
            ->with([
                'items',
                'mobileWallet:id,name,code',
                'address',
                'shipments.provider:id,business_name',
                'shipments.vendor:id,business_name',
            ])
            ->where('order_number', $orderNumber)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Returns the next sequence number for an order in the given year. Used to
     * format human-friendly `DLV-YY-NNNNNN` order numbers.
     */
    public function nextSequenceForYear(int $year): int
    {
        $prefix = sprintf('DLV-%02d-', $year % 100);

        $last = $this->model
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        if ($last === null) {
            return 1;
        }

        $tail = (int) substr((string) $last, strlen($prefix));

        return $tail + 1;
    }
}
