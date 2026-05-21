<?php

namespace App\Services;

use App\Interfaces\Repositories\IAddressInterface;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function __construct(private readonly IAddressInterface $repo) {}

    public function listForUser(User $user): Collection
    {
        return $this->repo->listForUser($user->id);
    }

    public function findForUser(User $user, int $id): ?Address
    {
        return $this->repo->findForUser($id, $user->id);
    }

    public function create(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            $isDefault = (bool) ($data['is_default'] ?? false);
            // First address is always default.
            if ($isDefault || $this->repo->listForUser($user->id)->isEmpty()) {
                $this->repo->clearDefaultsForUser($user->id);
                $isDefault = true;
            }

            return $this->repo->create(array_merge($data, [
                'user_id' => $user->id,
                'is_default' => $isDefault,
            ]));
        });
    }

    public function update(User $user, Address $address, array $data): bool
    {
        return DB::transaction(function () use ($user, $address, $data) {
            if (! empty($data['is_default'])) {
                $this->repo->clearDefaultsForUser($user->id);
            }

            return $this->repo->update($address->id, $data);
        });
    }

    public function delete(User $user, Address $address): bool
    {
        return DB::transaction(function () use ($user, $address) {
            $wasDefault = $address->is_default;
            $deleted = $this->repo->deleteById($address->id);

            // Promote another address to default if needed.
            if ($wasDefault) {
                $next = $this->repo->listForUser($user->id)->first();
                if ($next !== null) {
                    $this->repo->update($next->id, ['is_default' => true]);
                }
            }

            return $deleted;
        });
    }

    public function setDefault(User $user, Address $address): bool
    {
        return DB::transaction(function () use ($user, $address) {
            $this->repo->clearDefaultsForUser($user->id);

            return $this->repo->update($address->id, ['is_default' => true]);
        });
    }
}
