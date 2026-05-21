<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(protected Model $model) {}

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->select($columns)->with($relations)->get();
    }

    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->select($columns)->with($relations)->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data) > 0;
    }

    public function deleteById(int $id): bool
    {
        return $this->model->where('id', $id)->delete() > 0;
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->select($columns)->with($relations)->where('uuid', $uuid)->first();
    }

    public function getList(array $columns = [], array $relations = [], array $where = []): Collection
    {
        $query = $this->model->select($columns ?: ['*'])->with($relations);

        if (! empty($where)) {
            $query->where($where);
        }

        return $query->get();
    }
}
