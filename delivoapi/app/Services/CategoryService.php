<?php

namespace App\Services;

use App\Interfaces\Repositories\ICategoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(private readonly ICategoryInterface $repository) {}

    public function listAll(): Collection
    {
        return $this->repository->listAll();
    }

    public function listActive(): Collection
    {
        return $this->repository->listActive();
    }

    public function findById(int $id): ?Category
    {
        return $this->repository->findById($id, ['*'], ['parent:id,name,slug']);
    }

    public function create(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function archive(int $id): bool
    {
        return $this->repository->archive($id);
    }
}
