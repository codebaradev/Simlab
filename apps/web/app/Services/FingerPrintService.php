<?php

namespace App\Services;

use App\Models\FingerPrint;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FingerPrintService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(?int $room_id = null, array $filters = [], string $sortField = 'name', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = FingerPrint::query();

        if ($room_id) {
            $query->where('room_id', $room_id);
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

        $sortField = in_array($sortField, ['created_at']) ? $sortField : 'created_at';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? FingerPrint::withTrashed() : FingerPrint::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): FingerPrint
    {
        return DB::transaction(function () use ( $data) {
            $FingerPrint = FingerPrint::make($data);

            $FingerPrint->save();
            return $FingerPrint;
        });
    }

    public function update(FingerPrint $FingerPrint,  array $data): FingerPrint
    {
        return DB::transaction(function () use ($FingerPrint, $data) {
            $FingerPrint->fill($data);

            $FingerPrint->update($data);
            return $FingerPrint;
        });
    }

    public function delete(FingerPrint $FingerPrint): bool
    {
        return DB::transaction(function () use ($FingerPrint) {
            // Soft delete the FingerPrint
            $FingerPrint->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $FingerPrint = FingerPrint::withTrashed()->findOrFail($id);
            $FingerPrint->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $FingerPrint = FingerPrint::withTrashed()->findOrFail($id);
            $FingerPrint->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = FingerPrint::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = FingerPrint::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = FingerPrint::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
