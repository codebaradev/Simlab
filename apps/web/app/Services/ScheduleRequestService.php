<?php

namespace App\Services;

use App\Enums\ScheduleRequest\CategoryEnum;
use App\Enums\ScheduleRequest\StatusEnum;
use App\Models\ScheduleRequest;
use App\Models\Room;
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

        // filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // filter by schedule-request status (pending/approved/rejected) using enum
        if (!empty($filters['sr_status'])) {
            $statusFilter = $filters['sr_status'];
            if (is_string($statusFilter)) {
                $statusFilter = strtolower($statusFilter);
                if ($statusFilter === 'masuk' || $statusFilter === 'pending') {
                    $query->where('status', StatusEnum::PENDING->value ?? StatusEnum::PENDING);
                } elseif ($statusFilter === 'disetujui' || $statusFilter === 'approved') {
                    $query->where('status', StatusEnum::APPROVED->value ?? StatusEnum::APPROVED);
                } elseif ($statusFilter === 'ditolak' || $statusFilter === 'rejected') {
                    $query->where('status', StatusEnum::REJECTED->value ?? StatusEnum::REJECTED);
                }
            } elseif (is_int($statusFilter)) {
                $query->where('status', $statusFilter);
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
            $userId = $data['user_id'] ?? auth()->id();

            // create schedule request record
            $srPayload = [
                'user_id' => $userId,
                'lecturer_id' => $data['lecturer_id'] ?? null,
                'repeat_count' => $repeat,
                'status' => $data['status'] ?? StatusEnum::PENDING->value,
                'category' => $data['category'] ?? CategoryEnum::COURSE->value,
                'information' => $data['information'] ?? null,
            ];

            $scheduleRequest = ScheduleRequest::create($srPayload);

            $createdSchedules = $this->scheduleService->createMultiple($scheduleRequest->id, items: $data['schedules']);

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
