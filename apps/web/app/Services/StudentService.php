<?php

namespace App\Services;

use App\Models\Student;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(array $with = [], array $filters = [], string $sortField = 'generation', string $sortDirection = 'desc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = Student::query();

        $query->whereHas('study_program');

        if (!empty($with)) {
            $query->with($with);
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

        $sortField = in_array($sortField, ['nim', 'generation', 'created_at']) ? $sortField : 'generation';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? Student::withTrashed() : Student::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(int $user_id, array $data): Student
    {
        return DB::transaction(function () use ($user_id, $data) {
            $Student = Student::make($data);

            $Student->user_id = $user_id;

            $Student->save();
            return $Student;
        });
    }

    public function update(Student $Student, int $user_id, array $data): Student
    {
        return DB::transaction(function () use ($Student, $user_id, $data) {
            $Student->fill($data);

            $Student->user_id = $user_id;

            $Student->update($data);
            return $Student;
        });
    }

    public function delete(Student $Student): bool
    {
        return DB::transaction(function () use ($Student) {
            // Soft delete the Student
            $Student->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Student = Student::withTrashed()->findOrFail($id);
            $Student->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Student = Student::withTrashed()->findOrFail($id);
            $Student->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Student::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Student::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Student::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
