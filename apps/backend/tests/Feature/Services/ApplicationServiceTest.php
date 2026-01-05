<?php

use App\Models\Application;
use App\Models\Room;
use App\Services\ApplicationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ApplicationService();
    $this->applications = Application::factory()->count(10)->create();
});

describe('getAll', function () {
    it('returns paginated applications by default', function () {
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
        Application::factory()->count(3)->create(['room_id' => $roomId]);

        $result = $this->service->getAll(room_id: $roomId);

        expect($result->total())->toBe(3);
        expect($result->first()->room_id)->toBe($roomId);
    });

    it('applies search filter', function () {
        $searchTerm = 'UniqueAppName';
        Application::factory()->create(['name' => $searchTerm]);

        $result = $this->service->getAll(filters: ['search' => $searchTerm]);

        expect($result->total())->toBe(1);
        expect($result->first()->name)->toBe($searchTerm);
    });

    it('filters active applications', function () {
        Application::factory()->count(2)->deleted()->create();

        $result = $this->service->getAll(filters: ['status' => 'active']);

        expect($result->total())->toBe(10); // Only active ones
    });

    it('filters deleted applications', function () {
        $deletedApps = Application::factory()->count(3)->deleted()->create();

        $result = $this->service->getAll(filters: ['status' => 'deleted']);

        expect($result->total())->toBe(3);
    });

    it('sorts by name ascending by default', function () {
        $result = $this->service->getAll(isPaginated: false);

        $names = $result->pluck('name')->toArray();
        $sortedNames = $names;
        sort($sortedNames);

        expect($names)->toBe($sortedNames);
    });

    it('sorts by name descending when specified', function () {
        $result = $this->service->getAll(sortField: 'name', sortDirection: 'desc', isPaginated: false);

        $names = $result->pluck('name')->toArray();
        $sortedNames = $names;
        rsort($sortedNames);

        expect($names)->toBe($sortedNames);
    });

    it('sorts by created_at when specified', function () {
        $result = $this->service->getAll(sortField: 'created_at', sortDirection: 'desc', isPaginated: false);

        $createdAts = $result->pluck('created_at')->toArray();
        $sortedCreatedAts = $createdAts;
        rsort($sortedCreatedAts);

        expect($createdAts)->toBe($sortedCreatedAts);
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
});

describe('findById', function () {
    it('finds application by id', function () {
        $application = $this->applications->first();

        $result = $this->service->findById($application->id);

        expect($result->id)->toBe($application->id);
        expect($result->name)->toBe($application->name);
    });

    it('throws exception when application not found', function () {
        expect(fn() => $this->service->findById(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('finds with trashed applications when withTrashed is true', function () {
        $deletedApp = Application::factory()->deleted()->create();

        $result = $this->service->findById($deletedApp->id, withTrashed: true);

        expect($result->id)->toBe($deletedApp->id);
        expect($result->trashed())->toBeTrue();
    });

    it('loads relationships when with parameter provided', function () {
        $application = $this->applications->first();

        $result = $this->service->findById($application->id, with: ['room']);

        expect($result->relationLoaded('room'))->toBeTrue();
    });
});

describe('create', function () {
    it('creates a new application', function () {
        $data = [
            'name' => 'New Application',
            'room_id' => $this->applications->first()->room_id,
        ];

        $result = $this->service->create($data);

        expect($result)->toBeInstanceOf(Application::class);
        expect($result->name)->toBe('New Application');
        expect($result->exists)->toBeTrue();
    });

    // it('runs within database transaction', function () {
    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andReturnUsing(fn($callback) => $callback());

    //     $data = ['name' => 'Test App', 'room_id' => 1];
    //     $this->service->create($data);
    // });
});

describe('update', function () {
    it('updates an existing application', function () {
        $application = $this->applications->first();
        $newName = 'Updated Application Name';

        $result = $this->service->update($application, ['name' => $newName]);

        expect($result->name)->toBe($newName);
        expect($result->id)->toBe($application->id);
    });

    it('runs within database transaction', function () {
        $application = $this->applications->first();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->update($application, ['name' => 'Updated']);
    });
});

describe('delete', function () {
    it('soft deletes an application', function () {
        $application = $this->applications->first();

        $result = $this->service->delete($application);

        expect($result)->toBeTrue();
        expect(Application::find($application->id))->toBeNull();
        expect(Application::withTrashed()->find($application->id))->not->toBeNull();
    });

    it('runs within database transaction', function () {
        $application = $this->applications->first();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->delete($application);
    });
});

describe('restore', function () {
    it('restores a soft deleted application', function () {
        $deletedApp = Application::factory()->deleted()->create();

        $result = $this->service->restore($deletedApp->id);

        expect($result)->toBeTrue();
        expect(Application::find($deletedApp->id))->not->toBeNull();
    });

    it('throws exception when trying to restore non-existent application', function () {
        expect(fn() => $this->service->restore(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('runs within database transaction', function () {
        $deletedApp = Application::factory()->deleted()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->restore($deletedApp->id);
    });
});

describe('forceDelete', function () {
    it('permanently deletes an application', function () {
        $application = Application::factory()->create();

        $result = $this->service->forceDelete($application->id);

        expect($result)->toBeTrue();
        expect(Application::withTrashed()->find($application->id))->toBeNull();
    });

    it('throws exception when trying to force delete non-existent application', function () {
        expect(fn() => $this->service->forceDelete(9999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('runs within database transaction', function () {
        $application = Application::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->forceDelete($application->id);
    });
});

describe('bulk operations', function () {
    beforeEach(function () {
        $this->applicationIds = $this->applications->pluck('id')->toArray();
        $this->firstThreeIds = array_slice($this->applicationIds, 0, 3);
    });

    describe('bulkDelete', function () {
        it('soft deletes multiple applications', function () {
            $result = $this->service->bulkDelete($this->firstThreeIds);

            expect($result)->toBe(3);
            expect(Application::whereIn('id', $this->firstThreeIds)->count())->toBe(0);
            expect(Application::withTrashed()->whereIn('id', $this->firstThreeIds)->count())->toBe(3);
        });

        it('returns 0 when no applications found', function () {
            $result = $this->service->bulkDelete([999, 1000]);

            expect($result)->toBe(0);
        });

        it('runs within database transaction', function () {
            DB::shouldReceive('transaction')
                ->once()
                ->andReturnUsing(fn($callback) => $callback());

            $this->service->bulkDelete($this->firstThreeIds);
        });
    });

    describe('bulkForceDelete', function () {
        it('permanently deletes multiple applications', function () {
            $result = $this->service->bulkForceDelete($this->firstThreeIds);

            expect($result)->toBe(3);
            expect(Application::withTrashed()->whereIn('id', $this->firstThreeIds)->count())->toBe(0);
        });

        it('runs within database transaction', function () {
            DB::shouldReceive('transaction')
                ->once()
                ->andReturnUsing(fn($callback) => $callback());

            $this->service->bulkForceDelete($this->firstThreeIds);
        });
    });

    describe('bulkRestore', function () {
        it('restores multiple soft deleted applications', function () {
            // Soft delete some applications first
            Application::whereIn('id', $this->firstThreeIds)->delete();

            $result = $this->service->bulkRestore($this->firstThreeIds);

            expect($result)->toBe(3);
            expect(Application::whereIn('id', $this->firstThreeIds)->count())->toBe(3);
        });

        it('returns 0 when no applications to restore', function () {
            $result = $this->service->bulkRestore([999, 1000]);

            expect($result)->toBe(0);
        });

        it('runs within database transaction', function () {
            Application::whereIn('id', $this->firstThreeIds)->delete();

            DB::shouldReceive('transaction')
                ->once()
                ->andReturnUsing(fn($callback) => $callback());

            $this->service->bulkRestore($this->firstThreeIds);
        });
    });
});

// Edge cases and error scenarios
describe('edge cases', function () {
    it('handles empty filters array', function () {
        $result = $this->service->getAll(filters: []);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBeGreaterThan(0);
    });

    it('handles invalid sort field by defaulting to name', function () {
        $result = $this->service->getAll(sortField: 'invalid_field', isPaginated: false);

        // Should not throw error and use default 'name' field
        expect($result->count())->toBeGreaterThan(0);
    });

    it('handles empty array for bulk operations', function () {
        expect($this->service->bulkDelete([]))->toBe(0);
        expect($this->service->bulkForceDelete([]))->toBe(0);
        expect($this->service->bulkRestore([]))->toBe(0);
    });
});
