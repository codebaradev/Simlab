<?php

namespace App\Services;

use App\Models\AcademicClass;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AcademicClassService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(array $filters = [], string $sortField = 'name', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = AcademicClass::query();

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

        $sortField = in_array($sortField, ['name', 'year', 'code', 'created_at']) ? $sortField : 'created_at';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? AcademicClass::withTrashed() : AcademicClass::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data, ?int $clId = null): AcademicClass
    {
        return DB::transaction(function () use ($data, $clId) {
            $AcademicClass = AcademicClass::make($data);

            if ($clId) {
                $AcademicClass->cl_id = $clId;
            }

            $AcademicClass->save();
            return $AcademicClass;
        });
    }

    public function update(AcademicClass $AcademicClass, array $data, ?int $clId = null): AcademicClass
    {
        return DB::transaction(function () use ($AcademicClass, $clId, $data) {
            $AcademicClass->fill($data);

            if ($clId) {
                $AcademicClass->cl_id = $clId;
            }

            $AcademicClass->save();
            return $AcademicClass;
        });
    }

    public function addStudent(AcademicClass $AcademicClass, array $studentIds): bool
    {
        return DB::transaction(function () use ($AcademicClass, $studentIds) {
            $AcademicClass->students()->attach($studentIds);

            return true;
        });
    }

    public function removeStudent(AcademicClass $AcademicClass, int $studentIds): bool
    {
        return DB::transaction(function () use ($AcademicClass, $studentIds) {
            $AcademicClass->students()->detach($studentIds);

            return true;
        });
    }

    public function delete(AcademicClass $AcademicClass): bool
    {
        return DB::transaction(function () use ($AcademicClass) {
            // Soft delete the AcademicClass
            $AcademicClass->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $AcademicClass = AcademicClass::withTrashed()->findOrFail($id);
            $AcademicClass->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $AcademicClass = AcademicClass::withTrashed()->findOrFail($id);
            $AcademicClass->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = AcademicClass::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = AcademicClass::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = AcademicClass::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
