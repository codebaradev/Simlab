<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentService
{
    public function getAll(array $filters = [], string $sortField = 'name', string $sortDirection = 'asc', int $perPage = 10): LengthAwarePaginator
    {
        $query = Department::query();

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

        // Sorting
        $validSortFields = ['code', 'name', 'created_at'];
        $sortField = in_array($sortField, $validSortFields) ? $sortField : 'name';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortField, $sortDirection)
                    ->paginate($perPage);
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? Department::withTrashed() : Department::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            // Check if code already exists
            if (Department::where('code', $data['code'])->exists()) {
                throw new \Exception('Kode jurusan sudah digunakan.');
            }

            $department = Department::create([
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
            ]);

            // Log activity
            // activity()
            //     ->performedOn($department)
            //     ->withProperties(['attributes' => $data])
            //     ->log('department_created');

            return $department;
        });
    }

    public function update(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            // Check if code already exists (excluding current department)
            if (Department::where('code', $data['code'])->where('id', '!=', $department->id)->exists()) {
                throw new \Exception('Kode jurusan sudah digunakan.');
            }

            $original = $department->toArray();

            $department->update([
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
            ]);

            // // Log activity
            // activity()
            //     ->performedOn($department)
            //     ->withProperties([
            //         'old' => $original,
            //         'attributes' => $data
            //     ])
            //     ->log('department_updated');

            return $department;
        });
    }

    public function delete(Department $department): bool
    {
        return DB::transaction(function () use ($department) {
            // Soft delete the department
            $department->delete();

            // Log activity
            // activity()
            //     ->performedOn($department)
            //     ->log('department_deleted');

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $department = Department::withTrashed()->findOrFail($id);
            $department->restore();

            // Log activity
            // activity()
            //     ->performedOn($department)
            //     ->log('department_restored');

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $department = Department::withTrashed()->findOrFail($id);
            $department->forceDelete();

            // Log activity
            // activity()
            //     ->performedOn($department)
            //     ->log('department_force_deleted');

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Department::whereIn('id', $ids)->delete();

            // Log activity
            // activity()
            //     ->withProperties(['ids' => $ids])
            //     ->log('department_bulk_deleted');

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Department::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            // Log activity

            // activity()
            //     ->withProperties(['ids' => $ids])
            //     ->log('department_bulk_restored');

            return $count;
        });
    }
}
