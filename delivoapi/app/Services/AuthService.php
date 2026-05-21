<?php

namespace App\Services;

use App\Interfaces\Repositories\IUserInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private const STOREFRONT_TOKEN_NAME = 'delivo-storefront';

    public function __construct(
        private readonly IUserInterface $users,
        private readonly ModuleService $modules,
    ) {}

    /**
     * Create a customer account and issue a Sanctum token.
     *
     * @return array{user: array<string,mixed>, token: string}
     */
    public function register(array $data): array
    {
        $user = $this->users->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
        ]);

        $this->users->assignRole($user, 'customer');

        $token = $this->users->issueToken($user, self::STOREFRONT_TOKEN_NAME);

        return [
            'user' => $this->presentUser($user->fresh() ?? $user),
            'token' => $token,
        ];
    }

    /**
     * Validate credentials and issue a Sanctum token.
     * Returns null when authentication fails — controller decides the
     * HTTP response.
     *
     * @return array{user: array<string,mixed>, token: string}|null
     */
    public function login(array $data): ?array
    {
        $user = $this->users->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return null;
        }

        $token = $this->users->issueToken($user, self::STOREFRONT_TOKEN_NAME);

        return [
            'user' => $this->presentUser($user),
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $this->users->revokeCurrentToken($user);
    }

    /**
     * Public-facing user shape returned by auth and identity endpoints.
     * Includes the flat permission list and the modules → submodules tree
     * that drives the dynamic admin sidebar.
     *
     * @return array<string,mixed>
     */
    public function presentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')->all(),
            'modules' => $this->modules->menuForUser($user),
        ];
    }
}
