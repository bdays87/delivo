<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Module;
use App\Models\Submodule;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class AdminModuleController extends Controller
{
    /**
     * Read-only modules surface. Modules and submodules themselves are
     * seeder-managed; this admin view lets operators inspect the tree and
     * confirm which permissions a submodule controls.
     */
    public function index(): JsonResponse
    {
        $modules = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('submodules')
            ->get();

        return ApiResponse::success($modules, 'Modules retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $module = Module::query()
            ->with(['submodules' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->find($id);

        if ($module === null) {
            return ApiResponse::notFound('Module not found.');
        }

        return ApiResponse::success($module, 'Module retrieved successfully.');
    }

    /**
     * Permissions associated with a specific submodule. Before returning,
     * the endpoint guarantees the module's and the submodule's default
     * permissions exist in the Spatie permissions table AND are attached
     * to the submodule — self-healing if the seeder ever drifts.
     */
    public function submodulePermissions(int $moduleId, int $submoduleId): JsonResponse
    {
        $module = Module::query()->find($moduleId);
        if ($module === null) {
            return ApiResponse::notFound('Module not found.');
        }

        $submodule = Submodule::query()
            ->where('module_id', $moduleId)
            ->find($submoduleId);
        if ($submodule === null) {
            return ApiResponse::notFound('Submodule not found.');
        }

        // Ensure both defaults exist as Spatie permissions.
        $modulePerm = Permission::firstOrCreate([
            'name' => $module->default_permission,
            'guard_name' => 'web',
        ]);
        $submodulePerm = Permission::firstOrCreate([
            'name' => $submodule->default_permission,
            'guard_name' => 'web',
        ]);

        // Ensure both are attached to the submodule.
        $submodule->permissions()->syncWithoutDetaching([
            $modulePerm->id,
            $submodulePerm->id,
        ]);

        return ApiResponse::success([
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'default_permission' => $module->default_permission,
            ],
            'submodule' => [
                'id' => $submodule->id,
                'name' => $submodule->name,
                'url' => $submodule->url,
                'default_permission' => $submodule->default_permission,
            ],
            'permissions' => $submodule->permissions()->orderBy('name')->get()
                ->map(fn (Permission $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'guard_name' => $p->guard_name,
                    'is_default' => in_array($p->name, [
                        $module->default_permission,
                        $submodule->default_permission,
                    ], true),
                ])
                ->values(),
        ], 'Permissions retrieved successfully.');
    }

    /**
     * Flat list of every Spatie permission known to the system. Powers the
     * role-permission picker.
     */
    public function allPermissions(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return ApiResponse::success($permissions, 'Permissions retrieved successfully.');
    }

    /**
     * Full module → submodule → permission tree, used by the role-permission
     * matrix on the frontend so checkboxes can be grouped sensibly.
     */
    public function tree(): JsonResponse
    {
        $modules = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with([
                'submodules' => fn ($q) => $q->orderBy('sort_order')->orderBy('name'),
                'submodules.permissions' => fn ($q) => $q->orderBy('name'),
            ])
            ->get();

        $orphanPermissions = Permission::query()
            ->whereNotIn('id', function ($q) {
                $q->select('permission_id')->from('submodule_permissions');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return ApiResponse::success([
            'modules' => $modules,
            'orphan_permissions' => $orphanPermissions,
        ], 'Permission tree retrieved successfully.');
    }
}
