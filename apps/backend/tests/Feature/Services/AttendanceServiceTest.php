<?php

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use App\Enums\Attendance\StatusEnum;
use App\Services\AttendanceService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->attendanceService = app(AttendanceService::class);
    $this->user = User::factory()->create();
    $this->schedule = Schedule::factory()->create();
    $this->attendances = Attendance::factory()->count(10)->create([
        'user_id' => $this->user->id,
        'schedule_id' => $this->schedule->id
    ]);
});

// =============================================
// TEST GROUP: getAll
// =============================================
describe('getAll method', function () {
    it('can get all attendances with pagination', function () {
        $result = $this->attendanceService->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    it('can get all attendances without pagination', function () {
        $result = $this->attendanceService->getAll([], 'created_at', 'desc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    it('can filter active attendances', function () {
        $deletedAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $deletedAttendance->delete();

        $result = $this->attendanceService->getAll(['status' => 'active']);

        expect($result->total())->toBe(10); // Only non-deleted attendances
    });

    it('can filter deleted attendances', function () {
        $deletedAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $deletedAttendance->delete();

        $result = $this->attendanceService->getAll(['status' => 'deleted']);

        expect($result->total())->toBe(1); // Only deleted attendance
    });

    it('respects per page limit', function () {
        $result = $this->attendanceService->getAll([], 'created_at', 'desc', 5);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->perPage())->toBe(5);
    });

    it('applies sorting correctly', function () {
        $result = $this->attendanceService->getAll([], 'created_at', 'asc');

        $attendances = $result->items();
        expect($attendances[0]->created_at->lessThanOrEqualTo($attendances[1]->created_at))->toBeTrue();
    });
});

// =============================================
// TEST GROUP: findById
// =============================================
describe('findById method', function () {
    it('can find attendance by id', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceService->findById($attendance->id);

        expect($result->id)->toBe($attendance->id);
    });

    it('throws exception when attendance not found', function () {
        expect(fn() => $this->attendanceService->findById(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('can find attendance with relationships', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceService->findById($attendance->id, ['user', 'schedule']);

        expect($result->relationLoaded('user'))->toBeTrue()
            ->and($result->relationLoaded('schedule'))->toBeTrue()
            ->and($result->user->id)->toBe($this->user->id)
            ->and($result->schedule->id)->toBe($this->schedule->id);
    });

    it('can find attendance with trashed records', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $attendance->delete();

        $result = $this->attendanceService->findById($attendance->id, [], true);

        expect($result->id)->toBe($attendance->id)
            ->and($result->trashed())->toBeTrue();
    });
});

// =============================================
// TEST GROUP: create
// =============================================
describe('create method', function () {
    it('can create a new attendance', function () {
        $attendanceData = Attendance::factory()->make([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ])->toArray();

        $result = $this->attendanceService->create($attendanceData);

        expect($result)->toBeInstanceOf(Attendance::class)
            ->and($result->exists)->toBeTrue()
            ->and($result->user_id)->toBe($attendanceData['user_id'])
            ->and($result->schedule_id)->toBe($attendanceData['schedule_id'])
            ->and($result->status)->toBeInstanceOf(StatusEnum::class);
    });

    it('creates attendance with different statuses', function () {
        $testCases = [
            StatusEnum::PRESENT,
            StatusEnum::ABSENT,
            StatusEnum::LATE,
            StatusEnum::EXCUSED,
            StatusEnum::SICK
        ];

        foreach ($testCases as $status) {
            $attendanceData = [
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
                'status' => $status
            ];

            $result = $this->attendanceService->create($attendanceData);

            expect($result->status)->toBe($status)
                ->and($result->status->label())->toBe($status->label());
        }
    });

    it('creates attendance with valid enum casting', function () {
        $attendanceData = [
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => StatusEnum::PRESENT
        ];

        $result = $this->attendanceService->create($attendanceData);

        expect($result->status)->toBeInstanceOf(StatusEnum::class)
            ->and($result->status)->toBe(StatusEnum::PRESENT)
            ->and($result->getRawOriginal('status'))->toBe(StatusEnum::PRESENT->value);
    });
});

// =============================================
// TEST GROUP: update
// =============================================
describe('update method', function () {
    it('can update attendance', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => StatusEnum::PRESENT
        ]);
        $updateData = ['status' => StatusEnum::SICK];

        $result = $this->attendanceService->update($attendance, $updateData);

        expect($result->status)->toBe(StatusEnum::SICK)
            ->and($result->id)->toBe($attendance->id);
    });

    it('updates attendance status correctly', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceService->update($attendance, [
            'status' => StatusEnum::LATE
        ]);

        expect($result->status)->toBe(StatusEnum::LATE)
            ->and($result->status->label())->toBe('Terlambat');
    });

    it('updates attendance with different user and schedule', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $newUser = User::factory()->create();
        $newSchedule = Schedule::factory()->create();

        $result = $this->attendanceService->update($attendance, [
            'user_id' => $newUser->id,
            'schedule_id' => $newSchedule->id
        ]);

        expect($result->user_id)->toBe($newUser->id)
            ->and($result->schedule_id)->toBe($newSchedule->id);
    });
});

// =============================================
// TEST GROUP: delete
// =============================================
describe('delete method', function () {
    it('can soft delete attendance', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceService->delete($attendance);

        expect($result)->toBeTrue()
            ->and(Attendance::find($attendance->id))->toBeNull()
            ->and(Attendance::withTrashed()->find($attendance->id))->not->toBeNull();
    });
});

// =============================================
// TEST GROUP: restore
// =============================================
describe('restore method', function () {
    it('can restore soft deleted attendance', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $attendance->delete();

        $result = $this->attendanceService->restore($attendance->id);

        expect($result)->toBeTrue()
            ->and(Attendance::find($attendance->id))->not->toBeNull();
    });

    it('throws exception when restoring non-existent attendance', function () {
        expect(fn() => $this->attendanceService->restore(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

// =============================================
// TEST GROUP: forceDelete
// =============================================
describe('forceDelete method', function () {
    it('can permanently delete attendance', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceService->forceDelete($attendance->id);

        expect($result)->toBeTrue()
            ->and(Attendance::withTrashed()->find($attendance->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent attendance', function () {
        expect(fn() => $this->attendanceService->forceDelete(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

// =============================================
// TEST GROUP: bulk operations
// =============================================
describe('bulk operations', function () {
    it('can bulk delete attendances', function () {
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $ids = $attendances->pluck('id')->toArray();

        $result = $this->attendanceService->bulkDelete($ids);

        expect($result)->toBe(3)
            ->and(Attendance::whereIn('id', $ids)->count())->toBe(0)
            ->and(Attendance::withTrashed()->whereIn('id', $ids)->count())->toBe(3);
    });

    it('can bulk force delete attendances', function () {
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $ids = $attendances->pluck('id')->toArray();

        $result = $this->attendanceService->bulkForceDelete($ids);

        expect($result)->toBe(3)
            ->and(Attendance::withTrashed()->whereIn('id', $ids)->count())->toBe(0);
    });

    it('can bulk restore attendances', function () {
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $ids = $attendances->pluck('id')->toArray();

        // First delete them
        Attendance::whereIn('id', $ids)->delete();

        $result = $this->attendanceService->bulkRestore($ids);

        expect($result)->toBe(3)
            ->and(Attendance::whereIn('id', $ids)->count())->toBe(3);
    });

    it('returns zero when bulk deleting empty array', function () {
        $result = $this->attendanceService->bulkDelete([]);

        expect($result)->toBe(0);
    });

    it('handles bulk operations with mixed existing and non-existing ids', function () {
        $existingAttendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ]);
        $ids = [$existingAttendance->id, 9999, 10000];

        $result = $this->attendanceService->bulkDelete($ids);

        // Should only delete the existing one
        expect($result)->toBe(1);
    });
});

// =============================================
// TEST GROUP: edge cases and enum specific tests
// =============================================
describe('edge cases and enum specific tests', function () {
    it('handles pagination with per page exceeding max limit', function () {
        // Assuming max_per_page is 100 in config
        $result = $this->attendanceService->getAll([], 'created_at', 'desc', 200);

        expect($result->perPage())->toBeLessThanOrEqual(100);
    });

    it('handles invalid sort field gracefully', function () {
        $result = $this->attendanceService->getAll([], 'invalid_field', 'desc');

        // Should default to 'created_at'
        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });

    it('handles database transactions correctly for create', function () {
        $attendanceData = Attendance::factory()->make([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id
        ])->toArray();

        $result = $this->attendanceService->create($attendanceData);

        expect($result)->toBeInstanceOf(Attendance::class)
            ->and($result->wasRecentlyCreated)->toBeTrue();
    });

    it('maintains enum casting after update', function () {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'schedule_id' => $this->schedule->id,
            'status' => StatusEnum::PRESENT
        ]);

        $result = $this->attendanceService->update($attendance, [
            'status' => StatusEnum::EXCUSED
        ]);

        expect($result->status)->toBeInstanceOf(StatusEnum::class)
            ->and($result->status)->toBe(StatusEnum::EXCUSED)
            ->and($result->status->label())->toBe('Izin');
    });

    it('creates attendance with all possible status enum values', function () {
        foreach (StatusEnum::cases() as $status) {
            $attendanceData = [
                'user_id' => $this->user->id,
                'schedule_id' => $this->schedule->id,
                'status' => $status
            ];

            $result = $this->attendanceService->create($attendanceData);

            expect($result->status)->toBe($status)
                ->and($result->getRawOriginal('status'))->toBe($status->value);
        }
    });
});
