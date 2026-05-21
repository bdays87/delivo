<?php

namespace App\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface IBaseInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;

    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): bool;

    public function deleteById(int $id): bool;

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): ?Model;

    public function getList(array $columns = [], array $relations = [], array $where = []): Collection;
}
