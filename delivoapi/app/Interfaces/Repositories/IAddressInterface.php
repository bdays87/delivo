<?php

namespace App\Interfaces\Repositories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;

interface IAddressInterface extends IBaseInterface
{
    public function listForUser(int $userId): Collection;

    public function findForUser(int $id, int $userId): ?Address;

    public function clearDefaultsForUser(int $userId): void;
}
