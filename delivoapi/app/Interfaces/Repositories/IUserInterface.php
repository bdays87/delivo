<?php

namespace App\Interfaces\Repositories;

use App\Models\User;

interface IUserInterface extends IBaseInterface
{
    public function findByEmail(string $email): ?User;

    /**
     * Persist a new user. Caller is responsible for any post-create side
     * effects (role assignment, token issuance) — those belong in a service.
     */
    public function createUser(array $attributes): User;

    public function assignRole(User $user, string $role): User;

    public function issueToken(User $user, string $tokenName): string;

    public function revokeCurrentToken(User $user): void;
}
