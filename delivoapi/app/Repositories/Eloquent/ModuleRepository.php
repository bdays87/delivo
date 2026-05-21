<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IModuleInterface;
use App\Models\Module;
use Illuminate\Support\Collection;

class ModuleRepository extends BaseRepository implements IModuleInterface
{
    public function __construct(Module $model)
    {
        parent::__construct($model);
    }

    public function menuForPermissions(array $permissions): Collection
    {
        $modules = $this->model
            ->with(['submodules' => function ($query) {
                $query->where('status', 'active')->orderBy('sort_order');
            }])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return $modules
            ->filter(fn (Module $m) => in_array($m->default_permission, $permissions, true))
            ->map(fn (Module $m) => [
                'uuid' => $m->uuid,
                'name' => $m->name,
                'icon' => $m->icon,
                'default_permission' => $m->default_permission,
                'submodules' => $m->submodules
                    ->filter(fn ($s) => in_array($s->default_permission, $permissions, true))
                    ->map(fn ($s) => [
                        'uuid' => $s->uuid,
                        'name' => $s->name,
                        'icon' => $s->icon,
                        'url' => $s->url,
                        'default_permission' => $s->default_permission,
                    ])
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $m) => ! empty($m['submodules']))
            ->values();
    }
}
