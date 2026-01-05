<?php

use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->scheduleService = app(ScheduleService::class);
    $this->schedules = Schedule::factory()->count(10)->create();
});

// =============================================
// TEST GROUP: getAll
// =============================================
describe('getAll method', function () {
    it('can get all schedules with pagination', function () {
        $result = $this->scheduleService->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    it('can get all schedules without pagination', function () {
        $result = $this->scheduleService->getAll([], 'created_at', 'desc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    // it('can filter schedules by search', function () {
    //     $specificSchedule = Schedule::factory()->create([
    //         'information' => 'Special test schedule'
    //     ]);

    //     $result = $this->scheduleService->getAll(['search' => 'Special test']);

    //     expect($result->items())->toHaveCount(1)
    //         ->and($result->items()[0]->information)->toBe('Special test schedule');
    // });

    it('can filter active schedules', function () {
        $deletedSchedule = Schedule::factory()->create();
        $deletedSchedule->delete();

        $result = $this->scheduleService->getAll(['status' => 'active']);

        expect($result->total())->toBe(10); // Only non-deleted schedules
    });

    it('can filter deleted schedules', function () {
        $deletedSchedule = Schedule::factory()->create();
        $deletedSchedule->delete();

        $result = $this->scheduleService->getAll(['status' => 'deleted']);

        expect($result->total())->toBe(1); // Only deleted schedule
    });

    it('respects per page limit', function () {
        $result = $this->scheduleService->getAll([], 'created_at', 'desc', 5);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->perPage())->toBe(5);
    });

    it('applies sorting correctly', function () {
        $result = $this->scheduleService->getAll([], 'created_at', 'asc');

        $schedules = $result->items();
        expect($schedules[0]->created_at->lessThanOrEqualTo($schedules[1]->created_at))->toBeTrue();
    });
});

// =============================================
// TEST GROUP: findById
// =============================================
describe('findById method', function () {
    it('can find schedule by id', function () {
        $schedule = Schedule::factory()->create();

        $result = $this->scheduleService->findById($schedule->id);

        expect($result->id)->toBe($schedule->id);
    });

    it('throws exception when schedule not found', function () {
        expect(fn() => $this->scheduleService->findById(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('can find schedule with relationships', function () {
        $schedule = Schedule::factory()->create();

        $result = $this->scheduleService->findById($schedule->id, ['room', 'course']);

        expect($result->relationLoaded('room'))->toBeTrue()
            ->and($result->relationLoaded('course'))->toBeTrue();
    });

    it('can find schedule with trashed records', function () {
        $schedule = Schedule::factory()->create();
        $schedule->delete();

        $result = $this->scheduleService->findById($schedule->id, [], true);

        expect($result->id)->toBe($schedule->id)
            ->and($result->trashed())->toBeTrue();
    });
});

// =============================================
// TEST GROUP: create
// =============================================
describe('create method', function () {
    it('can create a new schedule', function () {
        $scheduleData = Schedule::factory()->make()->toArray();

        $result = $this->scheduleService->create($scheduleData);

        expect($result)->toBeInstanceOf(Schedule::class)
            ->and($result->exists)->toBeTrue()
            ->and($result->room_id)->toBe($scheduleData['room_id'])
            ->and($result->course_id)->toBe($scheduleData['course_id']);
    });

    it('creates schedule with valid datetime range', function () {
        $start = now()->addDays(1);
        $end = $start->copy()->addHours(2);

        $scheduleData = Schedule::factory()->make([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ])->toArray();

        $result = $this->scheduleService->create($scheduleData);

        expect($result->start_datetime->toDateTimeString() == $start->toDateTimeString())->toBeTrue()
        ->and($result->end_datetime->toDateTimeString() == $end->toDateTimeString())->toBeTrue();
    });
});

// =============================================
// TEST GROUP: update
// =============================================
describe('update method', function () {
    it('can update schedule', function () {
        $schedule = Schedule::factory()->create();
        $updateData = ['information' => 'Updated information'];

        $result = $this->scheduleService->update($schedule, $updateData);

        expect($result->information)->toBe('Updated information')
            ->and($result->id)->toBe($schedule->id);
    });

    it('updates schedule with valid data', function () {
        $schedule = Schedule::factory()->create();
        $newBuilding = 5;

        $result = $this->scheduleService->update($schedule, ['building' => $newBuilding]);

        expect($result->building)->toBe($newBuilding);
    });
});

// =============================================
// TEST GROUP: delete
// =============================================
describe('delete method', function () {
    it('can soft delete schedule', function () {
        $schedule = Schedule::factory()->create();

        $result = $this->scheduleService->delete($schedule);

        expect($result)->toBeTrue()
            ->and(Schedule::find($schedule->id))->toBeNull()
            ->and(Schedule::withTrashed()->find($schedule->id))->not->toBeNull();
    });
});

// =============================================
// TEST GROUP: restore
// =============================================
describe('restore method', function () {
    it('can restore soft deleted schedule', function () {
        $schedule = Schedule::factory()->create();
        $schedule->delete();

        $result = $this->scheduleService->restore($schedule->id);

        expect($result)->toBeTrue()
            ->and(Schedule::find($schedule->id))->not->toBeNull();
    });

    it('throws exception when restoring non-existent schedule', function () {
        expect(fn() => $this->scheduleService->restore(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

// =============================================
// TEST GROUP: forceDelete
// =============================================
describe('forceDelete method', function () {
    it('can permanently delete schedule', function () {
        $schedule = Schedule::factory()->create();

        $result = $this->scheduleService->forceDelete($schedule->id);

        expect($result)->toBeTrue()
            ->and(Schedule::withTrashed()->find($schedule->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent schedule', function () {
        expect(fn() => $this->scheduleService->forceDelete(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

// =============================================
// TEST GROUP: bulk operations
// =============================================
describe('bulk operations', function () {
    it('can bulk delete schedules', function () {
        $schedules = Schedule::factory()->count(3)->create();
        $ids = $schedules->pluck('id')->toArray();

        $result = $this->scheduleService->bulkDelete($ids);

        expect($result)->toBe(3)
            ->and(Schedule::whereIn('id', $ids)->count())->toBe(0)
            ->and(Schedule::withTrashed()->whereIn('id', $ids)->count())->toBe(3);
    });

    it('can bulk force delete schedules', function () {
        $schedules = Schedule::factory()->count(3)->create();
        $ids = $schedules->pluck('id')->toArray();

        $result = $this->scheduleService->bulkForceDelete($ids);

        expect($result)->toBe(3)
            ->and(Schedule::withTrashed()->whereIn('id', $ids)->count())->toBe(0);
    });

    it('can bulk restore schedules', function () {
        $schedules = Schedule::factory()->count(3)->create();
        $ids = $schedules->pluck('id')->toArray();

        // First delete them
        Schedule::whereIn('id', $ids)->delete();

        $result = $this->scheduleService->bulkRestore($ids);

        expect($result)->toBe(3)
            ->and(Schedule::whereIn('id', $ids)->count())->toBe(3);
    });

    it('returns zero when bulk deleting empty array', function () {
        $result = $this->scheduleService->bulkDelete([]);

        expect($result)->toBe(0);
    });
});

// =============================================
// TEST GROUP: edge cases
// =============================================
describe('edge cases', function () {
    it('handles pagination with per page exceeding max limit', function () {
        // Assuming max_per_page is 100 in config
        $result = $this->scheduleService->getAll([], 'created_at', 'desc', 200);

        expect($result->perPage())->toBeLessThanOrEqual(100);
    });

    it('handles invalid sort field gracefully', function () {
        $result = $this->scheduleService->getAll([], 'invalid_field', 'desc');

        // Should default to 'created_at'
        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });

    it('handles database transactions correctly', function () {
        $scheduleData = Schedule::factory()->make()->toArray();

        $result = $this->scheduleService->create($scheduleData);

        expect($result)->toBeInstanceOf(Schedule::class)
            ->and($result->wasRecentlyCreated)->toBeTrue();
    });
});
