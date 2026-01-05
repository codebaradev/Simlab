<?php

use App\Models\ScheduleRequest;
use App\Models\User;
use App\Services\ScheduleRequestService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ScheduleRequestService();
    $this->user = User::factory()->create();
});

describe('ScheduleRequestService', function () {
    describe('getAll', function () {
        it('should return paginated results by default', function () {
            ScheduleRequest::factory()->count(15)->create();

            $result = $this->service->getAll();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
            expect($result->count())->toBeGreaterThan(0);
        });

        it('should return collection when isPaginated is false', function () {
            ScheduleRequest::factory()->count(5)->create();

            $result = $this->service->getAll([], 'created_at', 'desc', null, false);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(5);
        });

        it('should apply search filter', function () {
            $user = User::factory()->create(['username' => 'uniqueusername']);
            ScheduleRequest::factory()->create(['user_id' => $user->id]);
            ScheduleRequest::factory()->count(3)->create();

            $result = $this->service->getAll(['search' => 'uniqueusername']);

            expect($result->total())->toBe(1);
        });

        it('should filter active records', function () {
            ScheduleRequest::factory()->count(3)->create();
            $deletedRequest = ScheduleRequest::factory()->create();
            $deletedRequest->delete();

            $result = $this->service->getAll(['status' => 'active']);

            expect($result->total())->toBe(3);
        });

        it('should filter deleted records', function () {
            ScheduleRequest::factory()->count(2)->create();
            $deletedRequest = ScheduleRequest::factory()->create();
            $deletedRequest->delete();

            $result = $this->service->getAll(['status' => 'deleted']);

            expect($result->total())->toBe(1);
        });

        it('should respect perPage parameter', function () {
            ScheduleRequest::factory()->count(10)->create();

            $result = $this->service->getAll([], 'created_at', 'desc', 5);

            expect($result->perPage())->toBe(5);
            expect($result->count())->toBe(5);
        });

        it('should not exceed max per page limit', function () {
            config(['pagination.max_limit' => 50]);
            $this->service = new ScheduleRequestService();

            ScheduleRequest::factory()->count(100)->create();

            $result = $this->service->getAll([], 'created_at', 'desc', 100);

            expect($result->perPage())->toBe(50);
        });
    });

    describe('findById', function () {
        it('should find schedule request by id', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();

            $result = $this->service->findById($scheduleRequest->id);

            expect($result->id)->toBe($scheduleRequest->id);
        });

        it('should find with relationships when provided', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();

            $result = $this->service->findById($scheduleRequest->id, ['user']);

            expect($result->relationLoaded('user'))->toBeTrue();
        });

        it('should find trashed records when withTrashed is true', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();
            $scheduleRequest->delete();

            $result = $this->service->findById($scheduleRequest->id, [], true);

            expect($result->id)->toBe($scheduleRequest->id);
            expect($result->trashed())->toBeTrue();
        });

        it('should throw exception when record not found', function () {
            expect(fn() => $this->service->findById(999))
                ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        });
    });

    describe('create', function () {
        it('should create a new schedule request', function () {
            $data = [
                'user_id' => $this->user->id,
                'status' => \App\Enums\ScheduleRequest\StatusEnum::APPROVE,
                'category' => \App\Enums\ScheduleRequest\CategoryEnum::COURSE,
                'information' => 'Test information'
            ];

            $result = $this->service->create($data);

            expect($result)->toBeInstanceOf(ScheduleRequest::class);
            expect($result->user_id)->toBe($this->user->id);
            expect($result->information)->toBe('Test information');
            $this->assertDatabaseHas('schedule_requests', ['information' => 'Test information']);
        });
    });

    describe('update', function () {
        it('should update schedule request', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();
            $newInformation = 'Updated information';

            $result = $this->service->update($scheduleRequest, ['information' => $newInformation]);

            expect($result->information)->toBe($newInformation);
            $this->assertDatabaseHas('schedule_requests', [
                'id' => $scheduleRequest->id,
                'information' => $newInformation
            ]);
        });
    });

    describe('delete', function () {
        it('should soft delete schedule request', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();

            $result = $this->service->delete($scheduleRequest);

            expect($result)->toBeTrue();
            $this->assertSoftDeleted('schedule_requests', ['id' => $scheduleRequest->id]);
        });
    });

    describe('restore', function () {
        it('should restore soft deleted schedule request', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();
            $scheduleRequest->delete();

            $result = $this->service->restore($scheduleRequest->id);

            expect($result)->toBeTrue();
            $this->assertDatabaseHas('schedule_requests', [
                'id' => $scheduleRequest->id,
                'deleted_at' => null
            ]);
        });
    });

    describe('forceDelete', function () {
        it('should permanently delete schedule request', function () {
            $scheduleRequest = ScheduleRequest::factory()->create();

            $result = $this->service->forceDelete($scheduleRequest->id);

            expect($result)->toBeTrue();
            $this->assertDatabaseMissing('schedule_requests', ['id' => $scheduleRequest->id]);
        });
    });

    describe('bulk operations', function () {
        beforeEach(function () {
            $this->scheduleRequests = ScheduleRequest::factory()->count(5)->create();
            $this->ids = $this->scheduleRequests->pluck('id')->toArray();
        });

        it('should bulk delete schedule requests', function () {
            $result = $this->service->bulkDelete($this->ids);

            expect($result)->toBe(5);
            foreach ($this->ids as $id) {
                $this->assertSoftDeleted('schedule_requests', ['id' => $id]);
            }
        });

        it('should bulk restore schedule requests', function () {
            // First delete them
            ScheduleRequest::whereIn('id', $this->ids)->delete();

            $result = $this->service->bulkRestore($this->ids);

            expect($result)->toBe(5);
            foreach ($this->ids as $id) {
                $this->assertDatabaseHas('schedule_requests', [
                    'id' => $id,
                    'deleted_at' => null
                ]);
            }
        });

        it('should bulk force delete schedule requests', function () {
            $result = $this->service->bulkForceDelete($this->ids);

            expect($result)->toBe(5);
            foreach ($this->ids as $id) {
                $this->assertDatabaseMissing('schedule_requests', ['id' => $id]);
            }
        });

        it('should handle empty array for bulk operations', function () {
            $result = $this->service->bulkDelete([]);

            expect($result)->toBe(0);
        });
    });

    describe('database transactions', function () {
        it('should rollback create on failure', function () {
            expect(function () {
                $this->service->create([
                    'user_id' => $this->user->id,
                    'status' => 'invalid_status',
                    'category' => \App\Enums\ScheduleRequest\CategoryEnum::COURSE,
                    'information' => 'Test'
                ]);
            })->toThrow(TypeError::class);

            // Verify no record was created
            $this->assertDatabaseCount('schedule_requests', 0);
        });
    });
});
