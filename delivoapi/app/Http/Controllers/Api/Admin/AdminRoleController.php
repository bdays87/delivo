<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRolePermissionsRequest;
use App\Http\Requests\Admin\AdminRoleRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminRoleController extends Controller
{
    /**
     * The three seeded roles ship with the app — they're load-bearing for
     * customer / vendor / admin auth, so we block deletion. Permissions on
     * them remain editable (use with care; the admin role grants access to
     * this very page).
     */
    private const PROTECTED_ROLES = ['admin', 'vendor', 'customer'];

    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return ApiResponse::success(
            $roles->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'guard_name' => $r->guard_name,
                'permissions_count' => $r->permissions_count,
                'is_protected' => in_array($r->name, self::PROTECTED_ROLES, true),
            ]),
            'Roles retrieved successfully.',
        );
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::query()->with('permissions:id,name,guard_name')->find($id);
        if ($role === null) {
            return ApiResponse::notFound('Role not found.');
        }

        return ApiResponse::success([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'is_protected' => in_array($role->name, self::PROTECTED_ROLES, true),
            'permissions' => $role->permissions->map(fn (Permission $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'guard_name' => $p->guard_name,
            ])->values(),
        ], 'Role retrieved successfully.');
    }

    public function store(AdminRoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->validated()['name'],
            'guard_name' => 'web',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return ApiResponse::created([
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'is_protected' => false,
        ], 'Role created.');
    }

    public function update(AdminRoleRequest $request, int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if ($role === null) {
            return ApiResponse::notFound('Role not found.');
        }
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return ApiResponse::error('Protected roles cannot be renamed.', 409);
        }

        $role->update(['name' => $request->validated()['name']]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return ApiResponse::success([], 'Role updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if ($role === null) {
            return ApiResponse::notFound('Role not found.');
        }
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return ApiResponse::error('Protected roles cannot be deleted.', 409);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return ApiResponse::success([], 'Role deleted.');
    }

    /**
     * Replace the role's permissions with the supplied set. Idempotent.
     */
    public function syncPermissions(AdminRolePermissionsRequest $request, int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if ($role === null) {
            return ApiResponse::notFound('Role not found.');
        }

        $permissionIds = $request->validated()['permission_ids'] ?? [];
        $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return ApiResponse::success([
            'role_id' => $role->id,
            'permissions_count' => $role->permissions()->count(),
        ], 'Role permissions updated.');
    }
}
