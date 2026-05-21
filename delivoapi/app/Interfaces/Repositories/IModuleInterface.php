<?php

namespace App\Interfaces\Repositories;

use Illuminate\Support\Collection;

interface IModuleInterface extends IBaseInterface
{
    /**
     * Return the modules → submodules tree filtered by the supplied flat
     * list of permission names. Used to render the dynamic admin sidebar.
     *
     * @param  array<int,string>  $permissions
     * @return Collection<int, array<string,mixed>>
     */
    public function menuForPermissions(array $permissions): Collection;
}
