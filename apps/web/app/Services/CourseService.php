<?php

namespace App\Services;

use App\Models\Course;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseService
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

        $query = Course::query();

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

        $sortField = in_array($sortField, ['name', 'year', 'sks', 'created_at']) ? $sortField : 'name';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? Course::withTrashed() : Course::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            $course = Course::make($data);
            $course->save();
            return $course;
        });
    }

    public function update(Course $course, array $data,  ): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $course->fill($data);

            $course->save();
            return $course;
        });
    }

    public function addLecturer(Course $course, int $lecturerId): bool
    {
        return DB::transaction(function () use ($course, $lecturerId) {
            $course->lecturers()->attach($lecturerId);

            return true;
        });
    }

    public function removeLecturer(Course $course, int $lecturerId): bool
    {
        return DB::transaction(function () use ($course, $lecturerId) {
            $course->lecturers()->detach($lecturerId);

            return true;
        });
    }

    public function addAcademicClass(Course $course, array $academicClassIds): bool
    {
        return DB::transaction(function () use ($course, $academicClassIds) {
            $course->academic_classes()->attach($academicClassIds);

            return true;
        });
    }

    public function removeAcademicClass(Course $course, array $academicClassIds): bool
    {
        return DB::transaction(function () use ($course, $academicClassIds) {
            $course->academic_classes()->detach($academicClassIds);

            return true;
        });
    }

    public function delete(Course $course): bool
    {
        return DB::transaction(function () use ($course) {
            // Soft delete the Course
            $course->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $course = Course::withTrashed()->findOrFail($id);
            $course->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $course = Course::withTrashed()->findOrFail($id);
            $course->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Course::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Course::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Course::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
