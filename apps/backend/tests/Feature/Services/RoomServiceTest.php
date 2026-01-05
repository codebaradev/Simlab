<?php

use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->roomService = new RoomService();
});

describe('RoomService', function () {
    describe('Constructor', function () {
        it('should initialize with correct pagination values', function () {
            expect($this->roomService)->toBeInstanceOf(RoomService::class);
        });
    });

    describe('getAll', function () {
        beforeEach(function () {
            Room::factory()->create(['code' => 'ROOM001', 'name' => 'Meeting Room A']);
            Room::factory()->create(['code' => 'ROOM002', 'name' => 'Conference Room B']);
            Room::factory()->create(['code' => 'ROOM003', 'name' => 'Training Room C']);
        });

        it('should return paginated results by default', function () {
            $result = $this->roomService->getAll();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
            expect($result->count())->toBe(3);
        });

        it('should return collection when isPaginated is false', function () {
            $result = $this->roomService->getAll([], 'code', 'asc', null, false);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result->count())->toBe(3);
        });

        it('should apply search filter', function () {
            $filters = ['search' => 'Meeting'];
            $result = $this->roomService->getAll($filters, 'code', 'asc', null, false);

            expect($result->count())->toBe(1);
            expect($result->first()->name)->toBe('Meeting Room A');
        });

        it('should filter active rooms', function () {
            $roomToDelete = Room::first();
            $roomToDelete->delete();

            $filters = ['status' => 'active'];
            $result = $this->roomService->getAll($filters, 'code', 'asc', null, false);

            expect($result->count())->toBe(2);
            expect($result->pluck('id'))->not->toContain($roomToDelete->id);
        });

        it('should filter deleted rooms', function () {
            $roomToDelete = Room::first();
            $roomToDelete->delete();

            $filters = ['status' => 'deleted'];
            $result = $this->roomService->getAll($filters, 'code', 'asc', null, false);

            expect($result->count())->toBe(1);
            expect($result->first()->id)->toBe($roomToDelete->id);
        });

        it('should sort by specified field and direction', function () {
            $result = $this->roomService->getAll([], 'code', 'desc', null, false);

            expect($result->first()->code)->toBe('ROOM003');
            expect($result->last()->code)->toBe('ROOM001');
        });

        it('should respect perPage parameter', function () {
            $result = $this->roomService->getAll([], 'code', 'asc', 2);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
            expect($result->count())->toBe(2);
            expect($result->perPage())->toBe(2);
        });
    });

    describe('findById', function () {
        it('should find room by id', function () {
            $room = Room::factory()->create();

            $result = $this->roomService->findById($room->id);

            expect($result->id)->toBe($room->id);
            expect($result->code)->toBe($room->code);
        });

        // it('should find room with relationships', function () {
        //     $room = Room::factory()->create();

        //     $result = $this->roomService->findById($room->id, ['building']);

        //     expect($result->id)->toBe($room->id);
        //     expect($result->relationLoaded('building'))->toBeTrue();
        // });

        it('should find soft deleted room when withTrashed is true', function () {
            $room = Room::factory()->create();
            $room->delete();

            $result = $this->roomService->findById($room->id, [], true);

            expect($result->id)->toBe($room->id);
            expect($result->trashed())->toBeTrue();
        });

        it('should throw exception when room not found', function () {
            expect(fn() => $this->roomService->findById(999))
                ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
        });
    });

    describe('create', function () {
        it('should create a new room', function () {
            $roomData = [
                'code' => 'NEW001',
                'name' => 'New Room',
                'capacity' => 10,
                'status' => 1,
            ];

            $result = $this->roomService->create($roomData);

            expect($result)->toBeInstanceOf(Room::class);
            expect($result->code)->toBe('NEW001');
            expect($result->name)->toBe('New Room');
            expect($result->exists)->toBeTrue();
        });

        it('should create room within transaction', function () {
            DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

            $roomData = ['code' => 'TRANS001', 'name' => 'Transaction Room', 'status' => 1];

            $this->roomService->create($roomData);
        });
    });

    describe('update', function () {
        it('should update room data', function () {
            $room = Room::factory()->create(['code' => 'OLD001', 'name' => 'Old Name']);
            $updateData = ['name' => 'Updated Name', 'status' => 1];

            $result = $this->roomService->update($room, $updateData);

            expect($result->name)->toBe('Updated Name');
            expect($result->status)->toBe(1);
            expect($result->code)->toBe('OLD001'); // unchanged
        });

        it('should update room within transaction', function () {
            $room = Room::factory()->create();

            DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

            $this->roomService->update($room, ['name' => 'Updated']);
        });
    });

    describe('delete', function () {
        it('should soft delete room', function () {
            $room = Room::factory()->create();

            $result = $this->roomService->delete($room);

            expect($result)->toBeTrue();
            expect(Room::find($room->id))->toBeNull();
            expect(Room::withTrashed()->find($room->id))->not->toBeNull();
        });

        it('should delete within transaction', function () {
            $room = Room::factory()->create();

            DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

            $this->roomService->delete($room);
        });
    });

    describe('restore', function () {
        it('should restore soft deleted room', function () {
            $room = Room::factory()->create();
            $room->delete();

            $result = $this->roomService->restore($room->id);

            expect($result)->toBeTrue();
            expect(Room::find($room->id))->not->toBeNull();
        });

        it('should throw exception when restoring non-existent room', function () {
            expect(fn() => $this->roomService->restore(999))
                ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
        });
    });

    describe('forceDelete', function () {
        it('should permanently delete room', function () {
            $room = Room::factory()->create();

            $result = $this->roomService->forceDelete($room->id);

            expect($result)->toBeTrue();
            expect(Room::withTrashed()->find($room->id))->toBeNull();
        });
    });

    describe('bulk operations', function () {
        beforeEach(function () {
            $this->rooms = Room::factory()->count(3)->create();
            $this->roomIds = $this->rooms->pluck('id')->toArray();
        });

        describe('bulkDelete', function () {
            it('should soft delete multiple rooms', function () {
                $result = $this->roomService->bulkDelete($this->roomIds);

                expect($result)->toBe(3);
                expect(Room::count())->toBe(0);
                expect(Room::withTrashed()->count())->toBe(3);
            });

            it('should return correct count when some rooms are already deleted', function () {
                $firstRoom = $this->rooms->first();
                $firstRoom->delete();

                $result = $this->roomService->bulkDelete($this->roomIds);

                expect($result)->toBe(2);
            });
        });

        describe('bulkForceDelete', function () {
            it('should permanently delete multiple rooms', function () {
                $result = $this->roomService->bulkForceDelete($this->roomIds);

                expect($result)->toBe(3);
                expect(Room::withTrashed()->count())->toBe(0);
            });
        });

        describe('bulkRestore', function () {
            it('should restore multiple soft deleted rooms', function () {
                // Soft delete all rooms first
                Room::whereIn('id', $this->roomIds)->delete();

                $result = $this->roomService->bulkRestore($this->roomIds);

                expect($result)->toBe(3);
                expect(Room::count())->toBe(3);
            });
        });
    });
});
