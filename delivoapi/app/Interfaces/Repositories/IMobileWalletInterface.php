<?php

namespace App\Interfaces\Repositories;

use App\Models\MobileWallet;
use Illuminate\Database\Eloquent\Collection;

interface IMobileWalletInterface extends IBaseInterface
{
    public function listActive(): Collection;

    public function listAll(): Collection;

    public function findByCode(string $code): ?MobileWallet;

    public function archive(int $id): bool;
}
