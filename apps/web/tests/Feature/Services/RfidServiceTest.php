<?php

use App\Models\Rfid;
use App\Models\User;
use App\Services\RfidService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new RfidService();

    // Create user
    $this->user = User::factory()->create();

    // Create RFID
    $this->rfid = Rfid::factory()->create([
        'user_id' => $this->user->id,
        'code' => 'RFID001',
    ]);
});

describe('RFID Service - GetAll Method', function () {
    it('can get all rfids with default parameters', function () {
        Rfid::factory()->count(5)->create();

        $result = $this->service->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBe(6) // 5 new + 1 from beforeEach
            ->and($result->perPage())->toBe(config('pagination.default'));
    });

    it('can get all rfids as collection when pagination is disabled', function () {
        Rfid::factory()->count(3)->create();

        $result = $this->service->getAll([], 'code', 'asc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBe(4); // 3 new + 1 from beforeEach
    });

    it('can filter rfids by search term using code', function () {
        Rfid::factory()->create(['code' => 'RFID111']);
        Rfid::factory()->create(['code' => 'RFID222']);

        $result = $this->service->getAll(['search' => 'RFID111']);

        expect($result->count())->toBe(1)
            ->and($result->first()->code)->toBe('RFID111');
    });

    it('can filter active rfids', function () {
        $deletedRfid = Rfid::factory()->create();
        $deletedRfid->delete();

        $result = $this->service->getAll(['status' => 'active']);

        expect($result->count())->toBe(1) // 1 from beforeEach + 1 active
            ->and($result->first()->trashed())->toBeFalse();
    });

    it('can filter deleted rfids', function () {
        $this->rfid->delete();

        $result = $this->service->getAll(['status' => 'deleted']);

        expect($result->count())->toBe(1)
            ->and($result->first()->trashed())->toBeTrue();
    });

    it('can sort rfids by code', function () {
        Rfid::factory()->create(['code' => 'ARFID01']);
        Rfid::factory()->create(['code' => 'BRFID01']);

        $result = $this->service->getAll([], 'code', 'asc');

        expect($result->first()->code)->toBe('ARFID01');
    });

    it('can sort rfids by created_at', function () {
        Rfid::query()->delete();
        $oldRfid = Rfid::factory()->create(['code' => 'OLD001', 'created_at' => now()->subDays(2)]);
        $newRfid = Rfid::factory()->create(['code' => 'NEW001', 'created_at' => now()]);

        $result = $this->service->getAll([], 'created_at', 'desc');

        expect($result->first()->code)->toBe('NEW001');
    });

    it('can set custom pagination limit', function () {
        Rfid::factory()->count(10)->create();

        $result = $this->service->getAll([], 'code', 'asc', 5);

        expect($result->count())->toBe(5)
            ->and($result->perPage())->toBe(5);
    });

    it('respects maximum pagination limit', function () {
        $maxLimit = config('pagination.max_limit');
        $result = $this->service->getAll([], 'code', 'asc', $maxLimit + 100);

        expect($result->perPage())->toBe($maxLimit);
    });
});

describe('RFID Service - FindById Method', function () {
    it('can find rfid by id', function () {
        $found = $this->service->findById($this->rfid->id);

        expect($found->id)->toBe($this->rfid->id)
            ->and($found->code)->toBe('RFID001')
            ->and($found->user_id)->toBe($this->user->id);
    });

    it('can find rfid with user relationship', function () {
        $found = $this->service->findById($this->rfid->id, ['user']);

        expect($found->user)->toBeInstanceOf(User::class)
            ->and($found->relationLoaded('user'))->toBeTrue()
            ->and($found->user->id)->toBe($this->user->id);
    });

    it('can find rfid with trashed records', function () {
        $this->rfid->delete();

        $found = $this->service->findById($this->rfid->id, [], true);

        expect($found->id)->toBe($this->rfid->id)
            ->and($found->trashed())->toBeTrue();
    });

    it('throws exception when rfid not found', function () {
        $this->service->findById(999);
    })->throws(ModelNotFoundException::class);
});

describe('RFID Service - Create Method', function () {
    it('can create a new rfid', function () {
        $data = [
            'code' => 'RFID999',
        ];

        $newUser = User::factory()->create();

        $rfid = $this->service->create($newUser->id, $data);

        expect($rfid)->toBeInstanceOf(Rfid::class)
            ->and($rfid->code)->toBe('RFID999')
            ->and($rfid->user_id)->toBe($newUser->id)
            ->and($rfid->exists)->toBeTrue();
    });

    it('creates rfid within transaction', function () {
        $data = ['code' => 'RFID555'];
        $newUser = User::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->create($newUser->id, $data);
    });

    it('requires valid user_id', function () {
        $data = ['code' => 'RFID777'];

        $rfid = $this->service->create($this->user->id, $data);

        expect($rfid->user_id)->toBe($this->user->id)
            ->and($rfid->user)->toBeInstanceOf(User::class);
    });
});

describe('RFID Service - Update Method', function () {
    it('can update a rfid', function () {
        $newData = [
            'code' => 'RFID001-UPDATED',
        ];

        $newUser = User::factory()->create();

        $updated = $this->service->update($this->rfid, $newUser->id, $newData);

        expect($updated->code)->toBe('RFID001-UPDATED')
            ->and($updated->user_id)->toBe($newUser->id)
            ->and($updated->id)->toBe($this->rfid->id);
    });

    it('can update rfid with same user', function () {
        $newData = [
            'code' => 'RFID001-NEW',
        ];

        $updated = $this->service->update($this->rfid, $this->user->id, $newData);

        expect($updated->code)->toBe('RFID001-NEW')
            ->and($updated->user_id)->toBe($this->user->id);
    });

    it('updates rfid within transaction', function () {
        $newData = ['code' => 'RFID001-UPDATED'];
        $newUser = User::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->update($this->rfid, $newUser->id, $newData);
    });
});

describe('RFID Service - Delete Method', function () {
    it('can soft delete a rfid', function () {
        $result = $this->service->delete($this->rfid);

        expect($result)->toBeTrue()
            ->and($this->rfid->fresh())->not->toBeNull()
            ->and($this->rfid->fresh()->trashed())->toBeTrue()
            ->and(Rfid::find($this->rfid->id))->toBeNull()
            ->and(Rfid::withTrashed()->find($this->rfid->id))->not->toBeNull();
    });

    it('deletes rfid within transaction', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->delete($this->rfid);
    });
});

describe('RFID Service - Restore Method', function () {
    it('can restore a soft deleted rfid', function () {
        $this->rfid->delete();

        $result = $this->service->restore($this->rfid->id);

        expect($result)->toBeTrue()
            ->and($this->rfid->fresh()->trashed())->toBeFalse();
    });

    it('throws exception when restoring non-existent rfid', function () {
        $this->service->restore(999);
    })->throws(ModelNotFoundException::class);
});

describe('RFID Service - ForceDelete Method', function () {
    it('can force delete a rfid', function () {
        $result = $this->service->forceDelete($this->rfid->id);

        expect($result)->toBeTrue()
            ->and(Rfid::withTrashed()->find($this->rfid->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent rfid', function () {
        $this->service->forceDelete(999);
    })->throws(ModelNotFoundException::class);
});

describe('RFID Service - Bulk Operations', function () {
    beforeEach(function () {
        $this->rfids = Rfid::factory()->count(3)->create();
    });

    it('can bulk delete rfids', function () {
        $ids = $this->rfids->pluck('id')->toArray();

        $count = $this->service->bulkDelete($ids);

        expect($count)->toBe(3)
            ->and(Rfid::find($ids[0]))->toBeNull()
            ->and(Rfid::withTrashed()->find($ids[0]))->not->toBeNull();
    });

    it('can bulk force delete rfids', function () {
        $ids = $this->rfids->pluck('id')->take(2)->toArray();

        $count = $this->service->bulkForceDelete($ids);

        expect($count)->toBe(2)
            ->and(Rfid::find($ids[0]))->toBeNull()
            ->and(Rfid::withTrashed()->find($ids[0]))->toBeNull()
            ->and(Rfid::find($this->rfids[2]->id))->not->toBeNull();
    });

    it('can bulk restore rfids', function () {
        $ids = $this->rfids->pluck('id')->toArray();
        Rfid::whereIn('id', $ids)->delete();

        $count = $this->service->bulkRestore($ids);

        expect($count)->toBe(3)
            ->and(Rfid::find($ids[0]))->not->toBeNull()
            ->and($this->rfids[0]->fresh()->trashed())->toBeFalse();
    });

    it('returns zero when bulk deleting empty array', function () {
        $count = $this->service->bulkDelete([]);

        expect($count)->toBe(0);
    });

    it('returns zero when bulk restoring empty array', function () {
        $count = $this->service->bulkRestore([]);

        expect($count)->toBe(0);
    });

    it('returns zero when bulk force deleting empty array', function () {
        $count = $this->service->bulkForceDelete([]);

        expect($count)->toBe(0);
    });
});

describe('RFID Service - Error Handling', function () {
    // it('handles database errors gracefully during getAll', function () {
    //     $this->expectException(QueryException::class);
    //     $this->service->getAll(sortField: 'invalid_field');
    // });

    // it('handles connection timeout during create', function () {
    //     $this->expectException(QueryException::class);

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andThrow(new QueryException('', [], new Exception('Connection timeout')));

    //     $newUser = User::factory()->create();
    //     $this->service->create($newUser->id, ['code' => 'RFID123']);
    // });

    it('handles database constraint violations', function () {
        $this->expectException(QueryException::class);

        // Try to create rfid with non-existent user
        $this->service->create(9999, ['code' => 'RFID123']);
    });

    it('handles duplicate code violation', function () {
        $this->expectException(QueryException::class);

        $newUser = User::factory()->create();
        // Try to create rfid with duplicate code
        $this->service->create($newUser->id, ['code' => 'RFID001']); // Same as beforeEach
    });
});
