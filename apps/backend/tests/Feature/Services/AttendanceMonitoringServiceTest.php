<?php

use App\Models\AttendanceMonitoring;
use App\Models\Schedule;
use App\Services\AttendanceMonitoringService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->attendanceMonitoringService = app(AttendanceMonitoringService::class);
    $this->schedule = Schedule::factory()->create();
    $this->attendances = AttendanceMonitoring::factory()->count(10)->create([
        'schedule_id' => $this->schedule->id
    ]);
});

// =============================================
// TEST GROUP: getAll
// =============================================
describe('getAll method', function () {
    it('can get all attendance monitorings with pagination', function () {
        $result = $this->attendanceMonitoringService->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    it('can get all attendance monitorings without pagination', function () {
        $result = $this->attendanceMonitoringService->getAll([], 'created_at', 'desc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    // it('can filter attendance monitorings by search', function () {
    //     $specificAttendance = AttendanceMonitoring::factory()->create([
    //         'topic' => 'Special test topic',
    //         'schedule_id' => $this->schedule->id
    //     ]);

    //     $result = $this->attendanceMonitoringService->getAll(['search' => 'Special test']);

    //     expect($result->items())->toHaveCount(1)
    //         ->and($result->items()[0]->topic)->toBe('Special test topic');
    // });

    it('can filter active attendance monitorings', function () {
        $deletedAttendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $deletedAttendance->delete();

        $result = $this->attendanceMonitoringService->getAll(['status' => 'active']);

        expect($result->total())->toBe(10); // Only non-deleted attendances
    });

    it('can filter deleted attendance monitorings', function () {
        $deletedAttendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $deletedAttendance->delete();

        $result = $this->attendanceMonitoringService->getAll(['status' => 'deleted']);

        expect($result->total())->toBe(1); // Only deleted attendance
    });

    it('respects per page limit', function () {
        $result = $this->attendanceMonitoringService->getAll([], 'created_at', 'desc', 5);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->perPage())->toBe(5);
    });

    it('applies sorting correctly', function () {
        $result = $this->attendanceMonitoringService->getAll([], 'created_at', 'asc');

        $attendances = $result->items();
        expect($attendances[0]->created_at->lessThanOrEqualTo($attendances[1]->created_at))->toBeTrue();
    });
});

// =============================================
// TEST GROUP: findById
// =============================================
describe('findById method', function () {
    it('can find attendance monitoring by id', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceMonitoringService->findById($attendance->id);

        expect($result->id)->toBe($attendance->id);
    });

    it('throws exception when attendance monitoring not found', function () {
        expect(fn() => $this->attendanceMonitoringService->findById(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('can find attendance monitoring with relationships', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceMonitoringService->findById($attendance->id, ['schedule']);

        expect($result->relationLoaded('schedule'))->toBeTrue()
            ->and($result->schedule->id)->toBe($this->schedule->id);
    });

    it('can find attendance monitoring with trashed records', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $attendance->delete();

        $result = $this->attendanceMonitoringService->findById($attendance->id, [], true);

        expect($result->id)->toBe($attendance->id)
            ->and($result->trashed())->toBeTrue();
    });
});

// =============================================
// TEST GROUP: create
// =============================================
describe('create method', function () {
    it('can create a new attendance monitoring', function () {
        $attendanceData = AttendanceMonitoring::factory()->make([
            'schedule_id' => $this->schedule->id
        ])->toArray();

        $result = $this->attendanceMonitoringService->create($attendanceData);

        expect($result)->toBeInstanceOf(AttendanceMonitoring::class)
            ->and($result->exists)->toBeTrue()
            ->and($result->schedule_id)->toBe($attendanceData['schedule_id'])
            ->and($result->topic)->toBe($attendanceData['topic']);
    });

    it('creates attendance monitoring with topic and subu_topic', function () {
        $attendanceData = [
            'schedule_id' => $this->schedule->id,
            'topic' => 'Mathematics Lecture',
            'sub_topic' => 'Algebra Basics'
        ];

        $result = $this->attendanceMonitoringService->create($attendanceData);

        expect($result->topic)->toBe('Mathematics Lecture')
            ->and($result->sub_topic)->toBe('Algebra Basics');
    });

    it('creates attendance monitoring with optional subu_topic', function () {
        $attendanceData = [
            'schedule_id' => $this->schedule->id,
            'topic' => 'Physics Class',
            'subu_topic' => null
        ];

        $result = $this->attendanceMonitoringService->create($attendanceData);

        expect($result->topic)->toBe('Physics Class')
            ->and($result->sub_topic)->toBeNull();
    });
});

// =============================================
// TEST GROUP: update
// =============================================
describe('update method', function () {
    it('can update attendance monitoring', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $updateData = ['topic' => 'Updated topic'];

        $result = $this->attendanceMonitoringService->update($attendance, $updateData);

        expect($result->topic)->toBe('Updated topic')
            ->and($result->id)->toBe($attendance->id);
    });

    it('updates attendance monitoring with sub_topic', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $newSubuTopic = 'Advanced Calculus';

        $result = $this->attendanceMonitoringService->update($attendance, ['sub_topic' => $newSubuTopic]);

        expect($result->sub_topic)->toBe($newSubuTopic);
    });

    it('updates multiple fields simultaneously', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $updateData = [
            'topic' => 'New Topic',
            'sub_topic' => 'New Sub Topic'
        ];

        $result = $this->attendanceMonitoringService->update($attendance, $updateData);

        expect($result->topic)->toBe('New Topic')
            ->and($result->sub_topic)->toBe('New Sub Topic');
    });
});

// =============================================
// TEST GROUP: delete
// =============================================
describe('delete method', function () {
    it('can soft delete attendance monitoring', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceMonitoringService->delete($attendance);

        expect($result)->toBeTrue()
            ->and(AttendanceMonitoring::find($attendance->id))->toBeNull()
            ->and(AttendanceMonitoring::withTrashed()->find($attendance->id))->not->toBeNull();
    });
});

// =============================================
// TEST GROUP: restore
// =============================================
describe('restore method', function () {
    it('can restore soft deleted attendance monitoring', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $attendance->delete();

        $result = $this->attendanceMonitoringService->restore($attendance->id);

        expect($result)->toBeTrue()
            ->and(AttendanceMonitoring::find($attendance->id))->not->toBeNull();
    });

    it('throws exception when restoring non-existent attendance monitoring', function () {
        expect(fn() => $this->attendanceMonitoringService->restore(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

// =============================================
// TEST GROUP: forceDelete
// =============================================
describe('forceDelete method', function () {
    it('can permanently delete attendance monitoring', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);

        $result = $this->attendanceMonitoringService->forceDelete($attendance->id);

        expect($result)->toBeTrue()
            ->and(AttendanceMonitoring::withTrashed()->find($attendance->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent attendance monitoring', function () {
        expect(fn() => $this->attendanceMonitoringService->forceDelete(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

// =============================================
// TEST GROUP: bulk operations
// =============================================
describe('bulk operations', function () {
    it('can bulk delete attendance monitorings', function () {
        $attendances = AttendanceMonitoring::factory()->count(3)->create([
            'schedule_id' => $this->schedule->id
        ]);
        $ids = $attendances->pluck('id')->toArray();

        $result = $this->attendanceMonitoringService->bulkDelete($ids);

        expect($result)->toBe(3)
            ->and(AttendanceMonitoring::whereIn('id', $ids)->count())->toBe(0)
            ->and(AttendanceMonitoring::withTrashed()->whereIn('id', $ids)->count())->toBe(3);
    });

    it('can bulk force delete attendance monitorings', function () {
        $attendances = AttendanceMonitoring::factory()->count(3)->create([
            'schedule_id' => $this->schedule->id
        ]);
        $ids = $attendances->pluck('id')->toArray();

        $result = $this->attendanceMonitoringService->bulkForceDelete($ids);

        expect($result)->toBe(3)
            ->and(AttendanceMonitoring::withTrashed()->whereIn('id', $ids)->count())->toBe(0);
    });

    it('can bulk restore attendance monitorings', function () {
        $attendances = AttendanceMonitoring::factory()->count(3)->create([
            'schedule_id' => $this->schedule->id
        ]);
        $ids = $attendances->pluck('id')->toArray();

        // First delete them
        AttendanceMonitoring::whereIn('id', $ids)->delete();

        $result = $this->attendanceMonitoringService->bulkRestore($ids);

        expect($result)->toBe(3)
            ->and(AttendanceMonitoring::whereIn('id', $ids)->count())->toBe(3);
    });

    it('returns zero when bulk deleting empty array', function () {
        $result = $this->attendanceMonitoringService->bulkDelete([]);

        expect($result)->toBe(0);
    });

    it('handles bulk operations with mixed existing and non-existing ids', function () {
        $existingAttendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $ids = [$existingAttendance->id, 9999, 10000];

        $result = $this->attendanceMonitoringService->bulkDelete($ids);

        // Should only delete the existing one
        expect($result)->toBe(1);
    });
});

// =============================================
// TEST GROUP: edge cases
// =============================================
describe('edge cases', function () {
    it('handles pagination with per page exceeding max limit', function () {
        // Assuming max_per_page is 100 in config
        $result = $this->attendanceMonitoringService->getAll([], 'created_at', 'desc', 200);

        expect($result->perPage())->toBeLessThanOrEqual(100);
    });

    it('handles invalid sort field gracefully', function () {
        $result = $this->attendanceMonitoringService->getAll([], 'invalid_field', 'desc');

        // Should default to 'created_at'
        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });

    it('handles database transactions correctly for create', function () {
        $attendanceData = AttendanceMonitoring::factory()->make([
            'schedule_id' => $this->schedule->id
        ])->toArray();

        $result = $this->attendanceMonitoringService->create($attendanceData);

        expect($result)->toBeInstanceOf(AttendanceMonitoring::class)
            ->and($result->wasRecentlyCreated)->toBeTrue();
    });

    it('maintains schedule relationship after update', function () {
        $attendance = AttendanceMonitoring::factory()->create([
            'schedule_id' => $this->schedule->id
        ]);
        $newSchedule = Schedule::factory()->create();

        $result = $this->attendanceMonitoringService->update($attendance, [
            'schedule_id' => $newSchedule->id
        ]);

        expect($result->schedule_id)->toBe($newSchedule->id)
            ->and($result->schedule->id)->toBe($newSchedule->id);
    });
});
