<?php

namespace App\Interfaces\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface ICategoryInterface extends IBaseInterface
{
    public function listActive(): Collection;

    public function listAll(): Collection;

    public function findBySlug(string $slug): ?Category;

    public function archive(int $id): bool;
}
