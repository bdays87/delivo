<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IAddressInterface;
use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository extends BaseRepository implements IAddressInterface
{
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }

    public function listForUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();
    }

    public function findForUser(int $id, int $userId): ?Address
    {
        return $this->model->where('id', $id)->where('user_id', $userId)->first();
    }

    public function clearDefaultsForUser(int $userId): void
    {
        $this->model->where('user_id', $userId)->update(['is_default' => false]);
    }
}
