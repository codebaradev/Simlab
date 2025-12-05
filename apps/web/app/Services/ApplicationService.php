<?php

namespace App\Services;

use App\Models\Application;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(?int $room_id = null, array $with = [], array $filters = [], string $sortField = 'name', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = Application::query();

        if (!empty($with)) {
            $query->with($with);
        }

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
        $query = $withTrashed ? Application::withTrashed() : Application::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Application
    {
        return DB::transaction(function () use ( $data) {
            $Application = Application::make($data);

            $Application->save();
            return $Application;
        });
    }

    public function update(Application $Application,  array $data): Application
    {
        return DB::transaction(function () use ($Application, $data) {
            $Application->fill($data);

            $Application->update($data);
            return $Application;
        });
    }

    public function delete(Application $Application): bool
    {
        return DB::transaction(function () use ($Application) {
            // Soft delete the Application
            $Application->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Application = Application::withTrashed()->findOrFail($id);
            $Application->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Application = Application::withTrashed()->findOrFail($id);
            $Application->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Application::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Application::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Application::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
