<?php

namespace App\Services;

use App\Interfaces\Repositories\IModuleInterface;
use App\Models\User;

class ModuleService
{
    public function __construct(private readonly IModuleInterface $modules) {}

    /**
     * Build the admin sidebar tree for the given user — modules and
     * submodules are filtered by the user's Spatie permissions.
     *
     * @return array<int, array<string,mixed>>
     */
    public function menuForUser(User $user): array
    {
        $permissions = $user->getAllPermissions()->pluck('name')->all();

        return $this->modules->menuForPermissions($permissions)->all();
    }
}
