<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\IPlatformSettingsInterface;
use App\Models\PlatformSettings;

class PlatformSettingsRepository extends BaseRepository implements IPlatformSettingsInterface
{
    public function __construct(PlatformSettings $model)
    {
        parent::__construct($model);
    }

    /**
     * Returns the single settings row, creating it with migration defaults
     * on first call.
     */
    public function current(): PlatformSettings
    {
        $row = $this->model->orderBy('id')->first();
        if ($row !== null) {
            return $row;
        }

        return $this->model->create([]);
    }

    public function save(array $attributes): PlatformSettings
    {
        $row = $this->current();
        $row->forceFill($attributes)->save();

        return $row->fresh();
    }
}
