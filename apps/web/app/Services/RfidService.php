<?php

namespace App\Services;

use App\Models\Rfid;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RfidService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(array $filters = [], string $sortField = 'code', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = Rfid::query();

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

        $sortField = in_array($sortField, ['nidn', 'nip', 'code', 'created_at']) ? $sortField : 'code';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);

        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function findById($id, $with = [], $withTrashed = false)
    {
        $query = $withTrashed ? Rfid::withTrashed() : Rfid::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(int $user_id, array $data): Rfid
    {
        return DB::transaction(function () use ($user_id, $data) {
            $Rfid = Rfid::make($data);

            $Rfid->user_id = $user_id;

            $Rfid->save();
            return $Rfid;
        });
    }

    public function update(Rfid $Rfid, int $user_id, array $data): Rfid
    {
        return DB::transaction(function () use ($Rfid, $user_id, $data) {
            $Rfid->fill($data);

            $Rfid->user_id = $user_id;

            $Rfid->update($data);
            return $Rfid;
        });
    }

    public function delete(Rfid $Rfid): bool
    {
        return DB::transaction(function () use ($Rfid) {
            // Soft delete the Rfid
            $Rfid->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Rfid = Rfid::withTrashed()->findOrFail($id);
            $Rfid->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $Rfid = Rfid::withTrashed()->findOrFail($id);
            $Rfid->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Rfid::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Rfid::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Rfid::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
