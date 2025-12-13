<?php

namespace App\Services;

use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Schedule;

class ScheduleService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(array $filters = [], string $sortField = 'created_at', string $sortDirection = 'desc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = Schedule::query();

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

        $sortField = in_array($sortField, ['created_at']) ? $sortField : 'created_at';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'desc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? Schedule::withTrashed() : Schedule::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Schedule
    {
        return DB::transaction(function () use ($data) {
            $Schedule = Schedule::make($data);

            $Schedule->save();
            return $Schedule;
        });
    }

    public function update(Schedule $Schedule, array $data): Schedule
    {
        return DB::transaction(function () use ($Schedule, $data) {
            $Schedule->fill($data);

            $Schedule->update($data);
            return $Schedule;
        });
    }

    public function delete(Schedule $Schedule): bool
    {
        return DB::transaction(function () use ($Schedule) {
            // Soft delete the Schedule
            $Schedule->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Schedule = Schedule::withTrashed()->findOrFail($id);
            $Schedule->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Schedule = Schedule::withTrashed()->findOrFail($id);
            $Schedule->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Schedule::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Schedule::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Schedule::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }

    public function createMultiple(array $items): \Illuminate\Support\Collection
    {
        return DB::transaction(function () use ($items) {
            $created = [];
            foreach ($items as $data) {
                // Use mass assignment; ensure Schedule::$fillable contains required fields
                $created[] = Schedule::create($data);
            }
            return collect($created);
        });
    }
}
