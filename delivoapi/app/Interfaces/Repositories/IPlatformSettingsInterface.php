<?php

namespace App\Interfaces\Repositories;

use App\Models\PlatformSettings;

interface IPlatformSettingsInterface extends IBaseInterface
{
    public function current(): PlatformSettings;

    public function save(array $attributes): PlatformSettings;
}
