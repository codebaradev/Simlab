<?php

namespace App\Services;

use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Attendance;

class AttendanceService
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

        $query = Attendance::query();

        // Search filter
        // if (!empty($filters['search'])) {
        //     $query->search($filters['search']);
        // }

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
        $query = $withTrashed ? Attendance::withTrashed() : Attendance::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Attendance
    {
        return DB::transaction(function () use ($data) {
            $Attendance = Attendance::make($data);

            $Attendance->save();
            return $Attendance;
        });
    }

    public function update(Attendance $Attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($Attendance, $data) {
            $Attendance->fill($data);

            $Attendance->update($data);
            return $Attendance;
        });
    }

    public function delete(Attendance $Attendance): bool
    {
        return DB::transaction(function () use ($Attendance) {
            // Soft delete the Attendance
            $Attendance->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Attendance = Attendance::withTrashed()->findOrFail($id);
            $Attendance->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Attendance = Attendance::withTrashed()->findOrFail($id);
            $Attendance->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Attendance::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Attendance::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Attendance::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
