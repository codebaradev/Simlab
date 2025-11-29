<?php

namespace App\Services;

use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\AttendanceMonitoring;

class AttendanceMonitoringService
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

        $query = AttendanceMonitoring::query();

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
        $query = $withTrashed ? AttendanceMonitoring::withTrashed() : AttendanceMonitoring::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): AttendanceMonitoring
    {
        return DB::transaction(function () use ($data) {
            $AttendanceMonitoring = AttendanceMonitoring::make($data);

            $AttendanceMonitoring->save();
            return $AttendanceMonitoring;
        });
    }

    public function update(AttendanceMonitoring $AttendanceMonitoring, array $data): AttendanceMonitoring
    {
        return DB::transaction(function () use ($AttendanceMonitoring, $data) {
            $AttendanceMonitoring->fill($data);

            $AttendanceMonitoring->update($data);
            return $AttendanceMonitoring;
        });
    }

    public function delete(AttendanceMonitoring $AttendanceMonitoring): bool
    {
        return DB::transaction(function () use ($AttendanceMonitoring) {
            // Soft delete the AttendanceMonitoring
            $AttendanceMonitoring->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $AttendanceMonitoring = AttendanceMonitoring::withTrashed()->findOrFail($id);
            $AttendanceMonitoring->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $AttendanceMonitoring = AttendanceMonitoring::withTrashed()->findOrFail($id);
            $AttendanceMonitoring->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = AttendanceMonitoring::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = AttendanceMonitoring::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = AttendanceMonitoring::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
