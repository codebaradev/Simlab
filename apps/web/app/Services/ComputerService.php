<?php

namespace App\Services;

use App\Models\Computer;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ComputerService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(?int $room_id = null, array $filters = [], string $sortField = 'name', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = Computer::query();

        if ($room_id) {
            $query->where('room_id', $room_id);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'deleted') {
                $query->onlyTrashed();
            }
        }

        $sortField = in_array($sortField, ['name', 'created_at']) ? $sortField : 'name';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? Computer::withTrashed() : Computer::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Computer
    {
        return DB::transaction(function () use ( $data) {
            $computer = Computer::make($data);

            $computer->save();
            return $computer;
        });
    }

    public function update(Computer $computer,  array $data): Computer
    {
        return DB::transaction(function () use ($computer, $data) {
            $computer->fill($data);

            $computer->update($data);
            return $computer;
        });
    }

    public function delete(Computer $computer): bool
    {
        return DB::transaction(function () use ($computer) {
            // Soft delete the Computer
            $computer->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $computer = Computer::withTrashed()->findOrFail($id);
            $computer->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $computer = Computer::withTrashed()->findOrFail($id);
            $computer->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Computer::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Computer::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Computer::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
