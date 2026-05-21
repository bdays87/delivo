<?php

namespace App\Services;

use App\Interfaces\Repositories\IMobileWalletInterface;
use App\Models\MobileWallet;
use Illuminate\Database\Eloquent\Collection;

class MobileWalletService
{
    public function __construct(private readonly IMobileWalletInterface $repository) {}

    public function listAll(): Collection
    {
        return $this->repository->listAll();
    }

    public function listActive(): Collection
    {
        return $this->repository->listActive();
    }

    public function findById(int $id): ?MobileWallet
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): MobileWallet
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function archive(int $id): bool
    {
        return $this->repository->archive($id);
    }
}
