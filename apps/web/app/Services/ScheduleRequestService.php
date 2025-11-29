<?php

namespace App\Services;

use App\Models\ScheduleRequest;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ScheduleRequestService
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
}
