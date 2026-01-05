<?php

namespace App\Services;

use App\Enums\UserRoleEnum;
use App\Models\Lecturer;
use App\Models\Role;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LecturerService
{
    private $perPage;
    private $maxPerPage;

    public function __construct() {
        $this->perPage = config('pagination.default');
        $this->maxPerPage = config('pagination.max_limit');
    }

    public function getAll(array $with = [], array $filters = [], string $sortField = 'code', string $sortDirection = 'asc', ?int $perPage = null, bool $isPaginated = true): LengthAwarePaginator|Collection
    {
        $perPage = min($perPage ?? $this->perPage, $this->maxPerPage);

        $query = Lecturer::query();

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
        $query = $withTrashed ? Lecturer::withTrashed() : Lecturer::query();
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->findOrFail($id);
    }

    public function create(int $user_id, array $data): Lecturer
    {
        return DB::transaction(function () use ($user_id, $data) {
            $lecturer = Lecturer::make($data);
            $lecturer->user_id = $user_id;
            $lecturer->save();

            $user = User::findOrFail($user_id);
            $lecturerRoleId = Role::where('code', UserRoleEnum::LECTURER->value)->value('id');
            $user->roles()->syncWithoutDetaching([$lecturerRoleId]);

            return $lecturer;
        });
    }

    public function update(Lecturer $lecturer, int $user_id, array $data): Lecturer
    {
        return DB::transaction(function () use ($lecturer, $user_id, $data) {
            $lecturer->fill($data);

            $lecturer->user_id = $user_id;

            $lecturer->update($data);
            return $lecturer;
        });
    }

    public function delete(Lecturer $lecturer): bool
    {
        return DB::transaction(function () use ($lecturer) {
            // Soft delete the Lecturer
            $lecturer->delete();

            return true;
        });
    }

    public function restore($id): bool
    {
        return DB::transaction(function () use ($id) {
            $lecturer = Lecturer::withTrashed()->findOrFail($id);
            $lecturer->restore();

            return true;
        });
    }

    public function forceDelete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $lecturer = Lecturer::withTrashed()->findOrFail($id);
            $lecturer->forceDelete();

            return true;
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Lecturer::whereIn('id', $ids)->delete();

            return $count;
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Lecturer::whereIn('id', $ids)->forceDelete();

            return $count;
        });
    }

    public function bulkRestore(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = Lecturer::withTrashed()
                ->whereIn('id', $ids)
                ->restore();

            return $count;
        });
    }
}
