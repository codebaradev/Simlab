<?php

use App\Models\FingerPrint;
use App\Models\Room;
use App\Services\FingerPrintService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new FingerPrintService();
    $this->fingerprints = FingerPrint::factory()->count(10)->create();
});

describe('getAll', function () {
    it('returns paginated fingerprints by default', function () {
        $result = $this->service->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBeGreaterThan(0);
    });

    it('returns collection when isPaginated is false', function () {
        $result = $this->service->getAll(isPaginated: false);

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->count())->toBeGreaterThan(0);
    });

    it('filters by room_id when provided', function () {
        $roomId = Room::factory()->create()->id;
        FingerPrint::factory()->count(3)->create(['room_id' => $roomId]);

        $result = $this->service->getAll(room_id: $roomId);

        expect($result->total())->toBe(3);
        expect($result->first()->room_id)->toBe($roomId);
    });

    it('applies search filter', function () {
        $searchTerm = 'UniqueFingerPrint';
        FingerPrint::factory()->create(['code' => $searchTerm]);

        $result = $this->service->getAll(filters: ['search' => $searchTerm]);

        expect($result->total())->toBe(1);
        expect($result->first()->code)->toBe($searchTerm);
    });

    it('filters active fingerprints', function () {
        FingerPrint::factory()->count(2)->deleted()->create();

        $result = $this->service->getAll(filters: ['status' => 'active']);

        expect($result->total())->toBe(10); // Only active ones
    });

    it('filters deleted fingerprints', function () {
        $deletedFingerPrints = FingerPrint::factory()->count(3)->deleted()->create();

        $result = $this->service->getAll(filters: ['status' => 'deleted']);

        expect($result->total())->toBe(3);
    });

    it('sorts by created_at ascending by default', function () {
        $result = $this->service->getAll(isPaginated: false);

        $createdAts = $result->pluck('created_at')->toArray();
        $sortedCreatedAts = $createdAts;
        sort($sortedCreatedAts);

        expect($createdAts)->toBe($sortedCreatedAts);
    });

    it('sorts by created_at descending when specified', function () {
        $result = $this->service->getAll(sortField: 'created_at', sortDirection: 'desc', isPaginated: false);

        $createdAts = $result->pluck('created_at')->toArray();
        $sortedCreatedAts = $createdAts;
        rsort($sortedCreatedAts);

        expect($createdAts)->toBe($sortedCreatedAts);
    });

    it('defaults to created_at when invalid sort field provided', function () {
        $result = $this->service->getAll(sortField: 'invalid_field', isPaginated: false);

        // Should not throw error and use default 'created_at' field
        expect($result->count())->toBeGreaterThan(0);
    });

    it('respects perPage parameter', function () {
        $perPage = 5;
        $result = $this->service->getAll(perPage: $perPage);

        expect($result->perPage())->toBe($perPage);
    });

    it('limits perPage to maximum allowed', function () {
        $maxPerPage = config('pagination.max_limit');
        $result = $this->service->getAll(perPage: $maxPerPage + 10);

        expect($result->perPage())->toBe($maxPerPage);
    });

    it('handles empty filters array', function () {
        $result = $this->service->getAll(filters: []);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBeGreaterThan(0);
    });
});

describe('findById', function () {
    it('finds fingerprint by id', function () {
        $fingerprint = $this->fingerprints->first();

        $result = $this->service->findById($fingerprint->id);

        expect($result->id)->toBe($fingerprint->id);
        expect($result->code)->toBe($fingerprint->code);
    });

    it('throws exception when fingerprint not found', function () {
        expect(fn() => $this->service->findById(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('finds with trashed fingerprints when withTrashed is true', function () {
        $deletedFingerPrint = FingerPrint::factory()->deleted()->create();

        $result = $this->service->findById($deletedFingerPrint->id, withTrashed: true);

        expect($result->id)->toBe($deletedFingerPrint->id);
        expect($result->trashed())->toBeTrue();
    });

    it('loads relationships when with parameter provided', function () {
        $fingerprint = $this->fingerprints->first();

        $result = $this->service->findById($fingerprint->id, with: ['room']);

        expect($result->relationLoaded('room'))->toBeTrue();
    });
});

describe('create', function () {
    it('creates a new fingerprint', function () {
        $data = [
            'code' => 'New FingerPrint',
            'room_id' => Room::factory()->create()->id,
            'status' => \App\Enums\Fingerprint\StatusEnum::ACTIVE->value,
        ];

        $result = $this->service->create($data);

        expect($result)->toBeInstanceOf(FingerPrint::class);
        expect($result->code)->toBe('New FingerPrint');
    });

    // it('runs within database transaction', function () {
    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andReturnUsing(fn($callback) => $callback());

    //     $data = [
    //         'name' => 'Test FingerPrint',
    //         'room_id' => 1,
    //         'template_data' => 'test_template'
    //     ];
    //     $this->service->create($data);
    // });

    it('creates fingerprint with all required fields', function () {
        $data = [
            'code' => 'Complete FingerPrint',
            'room_id' => Room::factory()->create()->id,
            'status' => \App\Enums\Fingerprint\StatusEnum::REGISTERED->value,
        ];

        $result = $this->service->create($data);

        expect($result->code)->toBe('Complete FingerPrint');
    });
});

describe('update', function () {
    it('updates an existing fingerprint', function () {
        $fingerprint = $this->fingerprints->first();
        $newName = 'Updated FingerPrint Name';

        $result = $this->service->update($fingerprint, ['code' => $newName]);

        expect($result->code)->toBe($newName);
        expect($result->id)->toBe($fingerprint->id);
    });

    it('updates multiple fields', function () {
        $fingerprint = $this->fingerprints->first();
        $updateData = [
            'code' => 'Updated Name',
            'status' => \App\Enums\Fingerprint\StatusEnum::INACTIVE->value,
        ];

        $result = $this->service->update($fingerprint, $updateData);

        expect($result->code)->toBe('Updated Name');
        expect($result->status)->toBe(\App\Enums\Fingerprint\StatusEnum::INACTIVE);
    });

    // it('runs within database transaction', function () {
    //     $fingerprint = $this->fingerprints->first();

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andReturnUsing(fn($callback) => $callback());

    //     $this->service->update($fingerprint, ['name' => 'Updated']);
    // });
});

describe('delete', function () {
    it('soft deletes a fingerprint', function () {
        $fingerprint = $this->fingerprints->first();

        $result = $this->service->delete($fingerprint);

        expect($result)->toBeTrue();
        expect(FingerPrint::find($fingerprint->id))->toBeNull();
        expect(FingerPrint::withTrashed()->find($fingerprint->id))->not->toBeNull();
    });

    // it('runs within database transaction', function () {
    //     $fingerprint = $this->fingerprints->first();

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andReturnUsing(fn($callback) => $callback());

    //     $this->service->delete($fingerprint);
    // });
});

describe('restore', function () {
    it('restores a soft deleted fingerprint', function () {
        $deletedFingerPrint = FingerPrint::factory()->deleted()->create();

        $result = $this->service->restore($deletedFingerPrint->id);

        expect($result)->toBeTrue();
        expect(FingerPrint::find($deletedFingerPrint->id))->not->toBeNull();
    });

    it('throws exception when trying to restore non-existent fingerprint', function () {
        expect(fn() => $this->service->restore(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    // it('runs within database transaction', function () {
    //     $deletedFingerPrint = FingerPrint::factory()->deleted()->create();

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andReturnUsing(fn($callback) => $callback());

    //     $this->service->restore($deletedFingerPrint->id);
    // });
});

describe('forceDelete', function () {
    it('permanently deletes a fingerprint', function () {
        $fingerprint = FingerPrint::factory()->create();

        $result = $this->service->forceDelete($fingerprint->id);

        expect($result)->toBeTrue();
        expect(FingerPrint::withTrashed()->find($fingerprint->id))->toBeNull();
    });

    it('throws exception when trying to force delete non-existent fingerprint', function () {
        expect(fn() => $this->service->forceDelete(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    // it('runs within database transaction', function () {
    //     $fingerprint = FingerPrint::factory()->create();

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andReturnUsing(fn($callback) => $callback());

    //     $this->service->forceDelete($fingerprint->id);
    // });
});

describe('bulk operations', function () {
    beforeEach(function () {
        $this->fingerprintIds = $this->fingerprints->pluck('id')->toArray();
        $this->firstThreeIds = array_slice($this->fingerprintIds, 0, 3);
    });

    describe('bulkDelete', function () {
        it('soft deletes multiple fingerprints', function () {
            $result = $this->service->bulkDelete($this->firstThreeIds);

            expect($result)->toBe(3);
            expect(FingerPrint::whereIn('id', $this->firstThreeIds)->count())->toBe(0);
            expect(FingerPrint::withTrashed()->whereIn('id', $this->firstThreeIds)->count())->toBe(3);
        });

        it('returns 0 when no fingerprints found', function () {
            $result = $this->service->bulkDelete([999, 1000]);

            expect($result)->toBe(0);
        });

        it('handles empty array', function () {
            $result = $this->service->bulkDelete([]);

            expect($result)->toBe(0);
        });

        // it('runs within database transaction', function () {
        //     DB::shouldReceive('transaction')
        //         ->once()
        //         ->andReturnUsing(fn($callback) => $callback());

        //     $this->service->bulkDelete($this->firstThreeIds);
        // });
    });

    describe('bulkForceDelete', function () {
        it('permanently deletes multiple fingerprints', function () {
            $result = $this->service->bulkForceDelete($this->firstThreeIds);

            expect($result)->toBe(3);
            expect(FingerPrint::withTrashed()->whereIn('id', $this->firstThreeIds)->count())->toBe(0);
        });

        it('returns 0 when no fingerprints found', function () {
            $result = $this->service->bulkForceDelete([999, 1000]);

            expect($result)->toBe(0);
        });

        it('handles empty array', function () {
            $result = $this->service->bulkForceDelete([]);

            expect($result)->toBe(0);
        });

        // it('runs within database transaction', function () {
        //     DB::shouldReceive('transaction')
        //         ->once()
        //         ->andReturnUsing(fn($callback) => $callback());

        //     $this->service->bulkForceDelete($this->firstThreeIds);
        // });
    });

    describe('bulkRestore', function () {
        it('restores multiple soft deleted fingerprints', function () {
            // Soft delete some fingerprints first
            FingerPrint::whereIn('id', $this->firstThreeIds)->delete();

            $result = $this->service->bulkRestore($this->firstThreeIds);

            expect($result)->toBe(3);
            expect(FingerPrint::whereIn('id', $this->firstThreeIds)->count())->toBe(3);
        });

        it('returns 0 when no fingerprints to restore', function () {
            $result = $this->service->bulkRestore([999, 1000]);

            expect($result)->toBe(0);
        });

        it('handles empty array', function () {
            $result = $this->service->bulkRestore([]);

            expect($result)->toBe(0);
        });

        // it('runs within database transaction', function () {
        //     FingerPrint::whereIn('id', $this->firstThreeIds)->delete();

        //     DB::shouldReceive('transaction')
        //         ->once()
        //         ->andReturnUsing(fn($callback) => $callback());

        //     $this->service->bulkRestore($this->firstThreeIds);
        // });
    });
});

describe('edge cases', function () {
    it('handles null room_id correctly', function () {
        $result = $this->service->getAll(room_id: null);

        expect($result->count())->toBeGreaterThan(0);
    });

    it('handles invalid status filter gracefully', function () {
        $result = $this->service->getAll(filters: ['status' => 'invalid_status']);

        // Should not apply any status filter
        expect($result->count())->toBeGreaterThan(0);
    });

    it('handles very large perPage values', function () {
        $result = $this->service->getAll(perPage: 1000);

        expect($result->perPage())->toBe(config('pagination.max_limit'));
    });
});

// Performance tests
describe('performance', function () {
    it('handles large number of fingerprints efficiently', function () {
        FingerPrint::factory()->count(100)->create();

        $startTime = microtime(true);
        $result = $this->service->getAll();
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;
        expect($executionTime)->toBeLessThan(2.0); // Should complete within 2 seconds
    });

    it('efficiently handles bulk operations', function () {
        $manyFingerprints = FingerPrint::factory()->count(50)->create();
        $ids = $manyFingerprints->pluck('id')->toArray();

        $startTime = microtime(true);
        $result = $this->service->bulkDelete($ids);
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;
        expect($executionTime)->toBeLessThan(1.0); // Should complete within 1 second
        expect($result)->toBe(50);
    });
});
