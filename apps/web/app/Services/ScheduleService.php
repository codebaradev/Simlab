<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceMonitoring;
use App\Models\Course;
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
        // Pre-fetch course-related participants if available on first item
        $course = null;
        $lecturers = collect();
        $students = collect();
        if (!empty($items[0]['course_id'])) {
            $course = Course::find($items[0]['course_id']);
            if ($course) {
                $lecturers = $course->lecturers()->with('user')->get();
                // attempt to get students from first academic class if exists
                if ($course->academic_classes->isNotEmpty()) {
                    $students = $course->academic_classes->first()->students()->with(['user'])->get();
                }
            }
        }

        return DB::transaction(function () use ($items, $lecturers, $students) {
            $created = [];
            foreach ($items as $data) {
                // Extract room ids (support 'room_id' or 'room_ids') then remove them from payload
                $roomIds = [];
                if (!empty($data['room_ids']) && is_array($data['room_ids'])) {
                    $roomIds = array_values(array_filter($data['room_ids']));
                } elseif (!empty($data['room_id'])) {
                    $roomIds = [(int) $data['room_id']];
                }

                unset($data['room_id'], $data['room_ids']);

                // Use mass assignment; ensure Schedule::$fillable contains required fields
                $schedule = Schedule::create($data);

                // attach rooms if provided
                if (!empty($roomIds)) {
                    $schedule->rooms()->sync($roomIds);
                }

                $created[] = $schedule;

                AttendanceMonitoring::create([
                    'schedule_id' => $schedule->id,
                ]);

                foreach ($students as $student) {
                    $user = $student->user;

                    Attendance::create([
                        'user_id' => $user->id,
                        'schedule_id' => $schedule->id,
                    ]);
                }

                foreach ($lecturers as $lecturer) {
                    $user = $lecturer->user;

                    Attendance::create([
                        'user_id' => $user->id,
                        'schedule_id' => $schedule->id,
                    ]);
                }
            }
            return collect($created);
        });
    }
}
