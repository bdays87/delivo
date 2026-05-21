<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IMobileWalletInterface;
use App\Models\MobileWallet;
use Illuminate\Database\Eloquent\Collection;

class MobileWalletRepository extends BaseRepository implements IMobileWalletInterface
{
    public function __construct(MobileWallet $model)
    {
        parent::__construct($model);
    }

    public function listActive(): Collection
    {
        return $this->model
            ->where('status', MobileWallet::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function listAll(): Collection
    {
        return $this->model->orderBy('sort_order')->orderBy('name')->get();
    }

    public function findByCode(string $code): ?MobileWallet
    {
        return $this->model->where('code', $code)->first();
    }

    public function archive(int $id): bool
    {
        return $this->update($id, ['status' => MobileWallet::STATUS_ARCHIVED]);
    }
}
