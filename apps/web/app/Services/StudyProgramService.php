<?php

namespace App\Services;

use App\Models\StudyProgram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;


class StudyProgramService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(array $with = [], array $filters = [], string $sortField = 'name', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = StudyProgram::query();

        $query->whereHas('department');

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

        // Department filter
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        $sortField = in_array($sortField, ['name', 'code', 'created_at']) ? $sortField : 'name';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? StudyProgram::withTrashed() : StudyProgram::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data, ?int $head_id = null, int $department_id): StudyProgram
    {
        return DB::transaction(function () use ($head_id, $department_id, $data) {
            $studyProgram = StudyProgram::make($data);

            $studyProgram->department_id = $department_id;

            if ($head_id) {
                $studyProgram->head_id = $head_id;
            }

            $studyProgram->save();
            return $studyProgram;
        });
    }

    public function update(StudyProgram $studyProgram,  array $data , ?int $head_id = null, int $department_id): StudyProgram
    {
        return DB::transaction(function () use ($studyProgram, $head_id, $department_id, $data) {
            $studyProgram->fill($data);

            $studyProgram->department_id = $department_id;

            if ($head_id) {
                $studyProgram->head_id = $head_id;
            }

            $studyProgram->update($data);
            return $studyProgram;
        });
    }

    public function delete(StudyProgram $studyProgram): bool
    {
        return DB::transaction(function () use ($studyProgram) {
            // Soft delete the StudyProgram
            $studyProgram->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $studyProgram = StudyProgram::withTrashed()->findOrFail($id);
            $studyProgram->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $studyProgram = StudyProgram::withTrashed()->findOrFail($id);
            $studyProgram->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = StudyProgram::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = StudyProgram::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = StudyProgram::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
