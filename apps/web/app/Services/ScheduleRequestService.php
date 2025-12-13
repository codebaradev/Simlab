<?php

namespace App\Services;

use App\Enums\ScheduleRequest\CategoryEnum;
use App\Models\Room;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ScheduleRequestService
{
    public ScheduleService $scheduleService;
    private $perPage;
    private $maxPerPage;

    public function __construct(ScheduleService $scheduleService) {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
        $this->scheduleService = $scheduleService;
    }

    public function getAll(array $filters = [], string $sortField = 'created_at', string $sortDirection = 'desc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = ScheduleRequest::query();

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
        $query = $withTrashed ? ScheduleRequest::withTrashed() : ScheduleRequest::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): ScheduleRequest
    {
        return DB::transaction(function () use ($data) {
            $scheduleRequest = ScheduleRequest::make($data);

            $scheduleRequest->save();
            return $scheduleRequest;
        });
    }

    public function update(ScheduleRequest $scheduleRequest, array $data): ScheduleRequest
    {
        return DB::transaction(function () use ($scheduleRequest, $data) {
            $scheduleRequest->fill($data);

            $scheduleRequest->update($data);
            return $scheduleRequest;
        });
    }

    public function delete(ScheduleRequest $scheduleRequest): bool
    {
        return DB::transaction(function () use ($scheduleRequest) {
            // Soft delete the ScheduleRequest
            $scheduleRequest->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $scheduleRequest = ScheduleRequest::withTrashed()->findOrFail($id);
            $scheduleRequest->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $scheduleRequest = ScheduleRequest::withTrashed()->findOrFail($id);
            $scheduleRequest->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = ScheduleRequest::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = ScheduleRequest::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = ScheduleRequest::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }

    public function createWithSchedules(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $repeat = max(1, (int) ($data['repeat_count'] ?? 1));
            $roomIds = $data['room_ids'] ?? [];
            $userId = $data['user_id'] ?? auth()->id();

            // create schedule request record
            $srPayload = [
                'user_id' => $userId,
                'lecturer_id' => $data['lecturer_id'] ?? null,
                'repeat_count' => $repeat,
                'status' => $data['status'] ?? 0,
                'category' => $data['category'] ?? CategoryEnum::COURSE->value,
                'information' => $data['information'] ?? null,
            ];
            $scheduleRequest = ScheduleRequest::create($srPayload);
 
            // If caller provided explicit schedules (occurrences), use them directly
            if (!empty($data['schedules']) && is_array($data['schedules'])) {
                $schedules = [];
                foreach ($data['schedules'] as $sched) {
                    $s = [
                        'room_id' => $sched['room_id'] ?? null,
                        'sr_id' => $scheduleRequest->id,
                        'course_id' => $sched['course_id'] ?? ($data['course_id'] ?? null),
                        'start_date' => $sched['start_date'] ?? ($sched['date'] ?? null),
                        'start_time' => isset($sched['start_time']) ? (strlen($sched['start_time']) > 5 ? $sched['start_time'] : ($sched['start_time'] . ':00')) : null,
                        'end_time' => isset($sched['end_time']) ? (strlen($sched['end_time']) > 5 ? $sched['end_time'] : ($sched['end_time'] . ':00')) : null,
                        'status' => $sched['status'] ?? ($data['status'] ?? 0),
                        'is_open' => $sched['is_open'] ?? ($data['is_open'] ?? false),
                        'building' => $sched['building'] ?? ($data['campus'] ?? null),
                        'information' => $sched['information'] ?? ($data['information'] ?? null),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // only include valid entries
                    if (!empty($s['start_date']) && !empty($s['start_time']) && !empty($s['end_time'])) {
                        $schedules[] = $s;
                    }
                }

                $createdSchedules = $this->scheduleService->createMultiple($schedules);

                return [
                    'request' => $scheduleRequest,
                    'schedules' => $createdSchedules,
                ];
            }

            // support both datetime inputs and separate date/time
            $startInput = $data['start_datetime'] ?? ($data['start_date'] ?? null);
            $endInput = $data['end_datetime'] ?? null;

            $start = $startInput instanceof Carbon ? $startInput : new Carbon($startInput);
            $end   = $endInput instanceof Carbon ? $endInput : new Carbon($endInput);

            $toCreate = [];
            for ($i = 0; $i < $repeat; $i++) {
                $occStart = $start->copy()->addWeeks($i);
                $occEnd = $end->copy()->addWeeks($i);

                foreach ($roomIds as $roomId) {
                    $toCreate[] = [
                        'room_id' => $roomId,
                        'sr_id' => $scheduleRequest->id,
                        'course_id' => $data['course_id'] ?? null,
                        'start_date' => $occStart->toDateString(),
                        'start_time' => $occStart->format('H:i:s'),
                        'end_time' => $occEnd->format('H:i:s'),
                        'status' => $data['status'] ?? 0,
                        'is_open' => $data['is_open'] ?? false,
                        'building' => $data['campus'] ?? null,
                        'information' => $data['information'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $createdSchedules = $this->scheduleService->createMultiple($toCreate);

            return [
                'request' => $scheduleRequest,
                'schedules' => $createdSchedules,
            ];
        });
    }

    /**
     * Helper: get available rooms (example placeholder).
     * You can expand with real availability checks.
     */
    public function getRooms(): Collection
    {
        return Room::orderBy('name')->get();
    }
}
