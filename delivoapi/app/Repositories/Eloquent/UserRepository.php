<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IUserInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements IUserInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function createUser(array $attributes): User
    {
        return $this->model->create($attributes);
    }

    public function assignRole(User $user, string $role): User
    {
        $user->assignRole($role);

        return $user;
    }

    public function issueToken(User $user, string $tokenName): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
