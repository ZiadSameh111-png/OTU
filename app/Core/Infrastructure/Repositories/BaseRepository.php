<?php

namespace App\Core\Infrastructure\Repositories;

use App\Core\Application\Interfaces\IRepository;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements IRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->getById($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete($id)
    {
        $record = $this->getById($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }
} 