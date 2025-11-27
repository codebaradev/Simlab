<?php

use App\Models\Computer;
use App\Models\Room;
use App\Services\ComputerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->computerService = new ComputerService();
});

describe('getAll method', function () {
    it('can get all computers with pagination', function () {
        Computer::factory()->count(20)->create();

        $result = $this->computerService->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->perPage())->toBe(expected: 15)
            ->and($result->total())->toBe(20);
    });

    it('can get all computers without pagination', function () {
        Computer::factory()->count(5)->create();

        $result = $this->computerService->getAll(isPaginated: false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(5);
    });

    it('can filter computers by room id', function () {
        $room1 = Room::factory()->create();
        $room2 = Room::factory()->create();

        Computer::factory()->count(3)->create(['room_id' => $room1->id]);
        Computer::factory()->count(2)->create(['room_id' => $room2->id]);

        $result = $this->computerService->getAll(room_id: $room1->id, isPaginated: false);

        expect($result)->toHaveCount(3)
            ->and($result->every(fn($computer) => $computer->room_id === $room1->id))->toBeTrue();
    });

    it('can search computers by name', function () {
        Computer::factory()->create(['name' => 'Gaming PC 001']);
        Computer::factory()->create(['name' => 'Office PC 001']);
        Computer::factory()->create(['name' => 'Workstation 001']);

        $result = $this->computerService->getAll(
            filters: ['search' => 'Gaming'],
            isPaginated: false
        );

        expect($result)->toHaveCount(1)
            ->and($result->first()->name)->toBe('Gaming PC 001');
    });

    it('can filter active computers', function () {
        Computer::factory()->count(3)->create();
        Computer::factory()->create()->delete();

        $result = $this->computerService->getAll(
            filters: ['status' => 'active'],
            isPaginated: false
        );

        expect($result)->toHaveCount(3)
            ->and($result->every(fn($computer) => $computer->deleted_at === null))->toBeTrue();
    });

    it('can filter deleted computers', function () {
        Computer::factory()->count(2)->create();
        Computer::factory()->count(3)->deleted()->create();

        $result = $this->computerService->getAll(
            filters: ['status' => 'deleted'],
            isPaginated: false
        );

        expect($result)->toHaveCount(3)
            ->and($result->every(fn($computer) => $computer->deleted_at !== null))->toBeTrue();
    });

    it('can sort computers by name ascending', function () {
        Computer::factory()->create(['name' => 'Z Computer']);
        Computer::factory()->create(['name' => 'A Computer']);
        Computer::factory()->create(['name' => 'M Computer']);

        $result = $this->computerService->getAll(
            sortField: 'name',
            sortDirection: 'asc',
            isPaginated: false
        );

        expect($result->first()->name)->toBe('A Computer')
            ->and($result->last()->name)->toBe('Z Computer');
    });

    it('can sort computers by name descending', function () {
        Computer::factory()->create(['name' => 'A Computer']);
        Computer::factory()->create(['name' => 'Z Computer']);
        Computer::factory()->create(['name' => 'M Computer']);

        $result = $this->computerService->getAll(
            sortField: 'name',
            sortDirection: 'desc',
            isPaginated: false
        );

        expect($result->first()->name)->toBe('Z Computer')
            ->and($result->last()->name)->toBe('A Computer');
    });

    it('can sort computers by created_at', function () {
        $oldComputer = Computer::factory()->create(['created_at' => now()->subDays(2)]);
        $newComputer = Computer::factory()->create(['created_at' => now()]);

        $result = $this->computerService->getAll(
            sortField: 'created_at',
            sortDirection: 'desc',
            isPaginated: false
        );

        expect($result->first()->id)->toBe($newComputer->id)
            ->and($result->last()->id)->toBe($oldComputer->id);
    });

    it('uses default sorting when invalid sort field is provided', function () {
        Computer::factory()->create(['name' => 'B Computer']);
        Computer::factory()->create(['name' => 'A Computer']);

        $result = $this->computerService->getAll(
            sortField: 'invalid_field',
            sortDirection: 'asc',
            isPaginated: false
        );

        expect($result->first()->name)->toBe('A Computer')
            ->and($result->last()->name)->toBe('B Computer');
    });

    it('uses custom per page value', function () {
        Computer::factory()->count(25)->create();

        $result = $this->computerService->getAll(perPage: 5);

        expect($result->perPage())->toBe(5)
            ->and($result->count())->toBe(5);
    });

    it('respects max per page limit', function () {
        Computer::factory()->count(100)->create();

        // Assume max_per_page is 50 in config
        $result = $this->computerService->getAll(perPage: 200);

        expect($result->perPage())->toBe(100);
    });

    it('can handle complex filters combination', function () {
        $room = Room::factory()->create();
        Computer::factory()->count(2)
            ->create(['room_id' => $room->id,'name' => 'Gaming PC']);
        Computer::factory()->count(1)
            ->deleted()
            ->create(['room_id' => $room->id, 'name' => 'Gaming PC']);

        $result = $this->computerService->getAll(
            room_id: $room->id,
            filters: ['search' => 'Gaming', 'status' => 'active'],
            isPaginated: false
        );

        expect($result)->toHaveCount(2)
            ->and($result->every(fn($c) => $c->room_id === $room->id && str_contains($c->name, 'Gaming')))->toBeTrue();
    });
});

describe('findById method', function () {
    it('can find computer by id', function () {
        $computer = Computer::factory()->create();

        $found = $this->computerService->findById($computer->id);

        expect($found)->toBeInstanceOf(Computer::class)
            ->and($found->id)->toBe($computer->id);
    });

    it('can find computer by id with relationships', function () {
        $computer = Computer::factory()->create();

        $found = $this->computerService->findById($computer->id, ['room']);

        expect($found)->toBeInstanceOf(Computer::class)
            ->and($found->relationLoaded('room'))->toBeTrue();
    });

    it('can find computer by id with trashed', function () {
        $computer = Computer::factory()->deleted()->create();

        $found = $this->computerService->findById($computer->id, [], true);

        expect($found)->toBeInstanceOf(Computer::class)
            ->and($found->id)->toBe($computer->id)
            ->and($found->deleted_at)->not->toBeNull();
    });

    it('throws exception when computer not found', function () {
        $this->computerService->findById(999);
    })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

describe('create method', function () {
    it('can create a computer', function () {
        $room = Room::factory()->create();
        $computerData = Computer::factory()->make(['room_id' => $room->id])->toArray();

        $created = $this->computerService->create($computerData);

        expect($created)->toBeInstanceOf(Computer::class)
            ->and($created->name)->toBe($computerData['name'])
            ->and($created->exists)->toBeTrue();
    });

    // it('runs create operation in transaction', function () {
    //     $computerData = Computer::factory()->make()->toArray();

    //     DB::shouldReceive('transaction')->atLeast()->once();

    //     $this->computerService->create($computerData);
    // });
});

describe('update method', function () {
    it('can update a computer', function () {
        $computer = Computer::factory()->create(['name' => 'Old Name']);
        $newData = ['name' => 'Updated Name', 'processor' => 'New Processor'];

        $updated = $this->computerService->update($computer, $newData);

        expect($updated)->toBeInstanceOf(Computer::class)
            ->and($updated->name)->toBe('Updated Name')
            ->and($updated->processor)->toBe('New Processor')
            ->and($updated->id)->toBe($computer->id);
    });

    // it('runs update operation in transaction', function () {
    //     $computer = Computer::factory()->create();
    //     $newData = ['name' => 'Updated Name'];

    //     DB::shouldReceive('transaction')->atLeast()->once();

    //     $this->computerService->update($computer, $newData);
    // });
});

describe('delete method', function () {
    it('can delete a computer', function () {
        $computer = Computer::factory()->create();

        $result = $this->computerService->delete($computer);

        expect($result)->toBeTrue()
            ->and($computer->fresh()->deleted_at)->not->toBeNull();
    });

    // it('runs delete operation in transaction', function () {
    //     $computer = Computer::factory()->create();

    //     DB::shouldReceive('transaction')->atLeast()->once();

    //     $this->computerService->delete($computer);
    // });
});

describe('restore method', function () {
    it('can restore a computer', function () {
        $computer = Computer::factory()->create();
        $computer->delete();

        $result = $this->computerService->restore($computer->id);

        expect($result)->toBeTrue()
            ->and($computer->fresh()->deleted_at)->toBeNull();
    });

    // it('runs restore operation in transaction', function () {
    //     $computer = Computer::factory()->deleted()->create();

    //     DB::shouldReceive('transaction')->atLeast()->once();

    //     $this->computerService->restore($computer->id);
    // });

    it('throws exception when restoring non-existent computer', function () {
        $this->computerService->restore(999);
    })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

describe('forceDelete method', function () {
    it('can force delete a computer', function () {
        $computer = Computer::factory()->create();

        $result = $this->computerService->forceDelete($computer->id);

        expect($result)->toBeTrue()
            ->and(Computer::withTrashed()->find($computer->id))->toBeNull();
    });

    // it('runs force delete operation in transaction', function () {
    //     $computer = Computer::factory()->create();

    //     DB::shouldReceive('transaction')->atLeast()->once();

    //     $this->computerService->forceDelete($computer->id);
    // });

    it('throws exception when force deleting non-existent computer', function () {
        $this->computerService->forceDelete(999);
    })->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

describe('bulk operations', function () {
    beforeEach(function () {
        $this->computers = Computer::factory()->count(3)->create();
        $this->ids = $this->computers->pluck('id')->toArray();
    });

    describe('bulkDelete method', function () {
        it('can bulk delete computers', function () {
            $count = $this->computerService->bulkDelete($this->ids);

            expect($count)->toBe(3)
                ->and(Computer::whereIn('id', $this->ids)->count())->toBe(0)
                ->and(Computer::withTrashed()->whereIn('id', $this->ids)->count())->toBe(3);
        });

        it('handles empty array for bulk delete', function () {
            $count = $this->computerService->bulkDelete([]);

            expect($count)->toBe(0);
        });

        // it('runs bulk delete operation in transaction', function () {
        //     DB::shouldReceive('transaction')->atLeast()->once();

        //     $this->computerService->bulkDelete($this->ids);
        // });
    });

    describe('bulkForceDelete method', function () {
        it('can bulk force delete computers', function () {
            $count = $this->computerService->bulkForceDelete($this->ids);

            expect($count)->toBe(3)
                ->and(Computer::withTrashed()->whereIn('id', $this->ids)->count())->toBe(0);
        });

        it('handles empty array for bulk force delete', function () {
            $count = $this->computerService->bulkForceDelete([]);

            expect($count)->toBe(0);
        });

        // it('runs bulk force delete operation in transaction', function () {
        //     DB::shouldReceive('transaction')->atLeast()->once();

        //     $this->computerService->bulkForceDelete($this->ids);
        // });
    });

    describe('bulkRestore method', function () {
        beforeEach(function () {
            Computer::query()->delete(); // Soft delete all
        });

        it('can bulk restore computers', function () {
            $count = $this->computerService->bulkRestore($this->ids);

            expect($count)->toBe(3)
                ->and(Computer::whereIn('id', $this->ids)->count())->toBe(3);
        });

        it('handles empty array for bulk restore', function () {
            $count = $this->computerService->bulkRestore([]);

            expect($count)->toBe(0);
        });

        // it('runs bulk restore operation in transaction', function () {
        //     DB::shouldReceive('transaction')->atLeast()->once();

        //     $this->computerService->bulkRestore($this->ids);
        // });
    });
});
