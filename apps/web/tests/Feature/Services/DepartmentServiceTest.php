<?php

use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

// TODO: Fix softdelete test

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DepartmentService();
    $this->department = Department::factory()->create([
        'code' => 'DEPT001',
        'name' => 'Human Resources',
    ]);
});

// Test: GetAll Method
it('can get all departments with default parameters', function () {
    Department::factory()->count(5)->create();

    $result = $this->service->getAll();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->count())->toBe(6) // 5 new + 1 from beforeEach
        ->and($result->perPage())->toBe(15);
});

it('can filter departments by search term', function () {
    Department::factory()->create(['name' => 'Engineering Department']);
    Department::factory()->create(['name' => 'Marketing Team']);

    $result = $this->service->getAll(['search' => 'Engineering']);

    expect($result->count())->toBe(1)
        ->and($result->first()->name)->toBe('Engineering Department');
});

it('can filter active departments', function () {
    $deletedDept = Department::factory()->create();
    $deletedDept->delete();

    $result = $this->service->getAll(['status' => 'active']);

    expect($result->count())->toBe(1) // Only the active one from beforeEach
        ->and($result->first()->trashed())->toBeFalse();
});

it('can filter deleted departments', function () {
    $this->department->delete();

    $result = $this->service->getAll(['status' => 'deleted']);

    expect($result->count())->toBe(1)
        ->and($result->first()->trashed())->toBeTrue();
});

it('can sort departments by different fields and directions', function () {
    Department::factory()->create(['name' => 'Accounting', 'code' => 'DEPT003']);
    Department::factory()->create(['name' => 'Finance', 'code' => 'DEPT002']);

    $resultAsc = $this->service->getAll([], 'name', 'asc');
    $resultDesc = $this->service->getAll([], 'name', 'desc');

    expect($resultAsc->first()->name)->toBe('Accounting')
        ->and($resultDesc->first()->name)->toBe('Human Resources');
});

it('can set custom pagination limit', function () {
    Department::factory()->count(15)->create();

    $result = $this->service->getAll([], 'name', 'asc', 5);

    expect($result->count())->toBe(5)
        ->and($result->perPage())->toBe(5);
});

// Test: FindById Method
it('can find department by id', function () {
    $found = $this->service->findById($this->department->id);

    expect($found->id)->toBe($this->department->id)
        ->and($found->name)->toBe('Human Resources')
        ->and($found->code)->toBe('DEPT001');
});

// it('can find department with relationships', function () {
//     // Assuming Department has users relationship
//     $found = $this->service->findById($this->department->id, ['users']);

//     expect($found)->toBeInstanceOf(Department::class);
// });

it('can find department with trashed records', function () {
    $this->department->delete();

    $found = $this->service->findById($this->department->id, [], true);

    expect($found->id)->toBe($this->department->id)
        ->and($found->trashed())->toBeTrue();
});

it('throws exception when department not found', function () {
    $this->service->findById(999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

// Test: Create Method
it('can create a new department', function () {
    $data = [
        'code' => 'DEPT002',
        'name' => 'Engineering',
    ];

    $department = $this->service->create($data);

    expect($department)->toBeInstanceOf(Department::class)
        ->and($department->code)->toBe('DEPT002')
        ->and($department->name)->toBe('Engineering')
        ->and($department->exists)->toBeTrue();
});

it('creates department within transaction', function () {
    $data = ['code' => 'DEPT003', 'name' => 'Marketing'];

    DB::shouldReceive('transaction')
        ->once()
        ->with(\Closure::class)
        ->andReturnUsing(fn($callback) => $callback());

    $this->service->create($data);
});

// Test: Update Method
it('can update a department', function () {
    $newData = [
        'code' => 'DEPT001-UPDATED',
        'name' => 'HR Department Updated',
    ];

    $updated = $this->service->update($this->department, $newData);

    expect($updated->code)->toBe('DEPT001-UPDATED')
        ->and($updated->name)->toBe('HR Department Updated')
        ->and($updated->id)->toBe($this->department->id);
});

it('updates department within transaction', function () {
    $newData = ['name' => 'Updated Name'];

    DB::shouldReceive('transaction')
        ->once()
        ->with(\Closure::class)
        ->andReturnUsing(fn($callback) => $callback());

    $this->service->update($this->department, $newData);
});

// Test: Delete Method
it('can soft delete a department', function () {
    $result = $this->service->delete($this->department);

    expect($result)->toBeTrue()
        ->and($this->department->fresh())->not->toBeNull() // Model still exists
        ->and($this->department->fresh()->trashed())->toBeTrue() // But it's soft deleted
        ->and(Department::find($this->department->id))->toBeNull() // Regular find won't find it
        ->and(Department::withTrashed()->find($this->department->id))->not->toBeNull(); // With trashed will find it
});

it('deletes department within transaction', function () {
    DB::shouldReceive('transaction')
        ->once()
        ->with(\Closure::class)
        ->andReturnUsing(fn($callback) => $callback());

    $this->service->delete($this->department);
});

// Test: Restore Method
it('can restore a soft deleted department', function () {
    $this->department->delete();

    $result = $this->service->restore($this->department->id);

    expect($result)->toBeTrue()
        ->and($this->department->fresh())->not->toBeNull()
        ->and($this->department->fresh()->trashed())->toBeFalse();
});

it('throws exception when restoring non-existent department', function () {
    $this->service->restore(999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

// Test: ForceDelete Method
it('can force delete a department', function () {
    $result = $this->service->forceDelete($this->department->id);

    expect($result)->toBeTrue()
        ->and(Department::withTrashed()->find($this->department->id))->toBeNull();
});

it('throws exception when force deleting non-existent department', function () {
    $this->service->forceDelete(999);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

// Test: Bulk Operations
it('can bulk delete departments', function () {
    $dept2 = Department::factory()->create();
    $dept3 = Department::factory()->create();

    $ids = [$dept2->id, $dept3->id];

    $count = $this->service->bulkDelete($ids);

    expect($count)->toBe(2)
        ->and(Department::find($dept2->id))->toBeNull()
        ->and(Department::find($dept3->id))->toBeNull()
        ->and(Department::withTrashed()->find($dept2->id))->not->toBeNull();
});

it('can bulk force delete departments', function () {
    $dept1 = Department::factory()->create();
    $dept2 = Department::factory()->create();
    $dept3 = Department::factory()->create();

    $ids = [$dept1->id, $dept2->id];

    $count = $this->service->bulkForceDelete($ids);

    expect($count)->toBe(2)
        ->and(Department::find($dept1->id))->toBeNull()
        ->and(Department::find($dept2->id))->toBeNull()
        ->and(Department::find($dept3->id))->not->toBeNull() // Should not be deleted
        ->and(Department::withTrashed()->find($dept1->id))->toBeNull() // Permanently gone
        ->and(Department::withTrashed()->find($dept2->id))->toBeNull(); // Permanently gone
});

it('can bulk restore departments', function () {
    $dept2 = Department::factory()->create();
    $dept3 = Department::factory()->create();

    $ids = [$dept2->id, $dept3->id];

    // First delete them
    Department::whereIn('id', $ids)->delete();

    $count = $this->service->bulkRestore($ids);

    expect($count)->toBe(2)
        ->and(Department::find($dept2->id))->not->toBeNull()
        ->and(Department::find($dept3->id))->not->toBeNull()
        ->and($dept2->fresh()->trashed())->toBeFalse()
        ->and($dept3->fresh()->trashed())->toBeFalse();
});

it('returns zero when bulk deleting empty array', function () {
    $count = $this->service->bulkDelete([]);

    expect($count)->toBe(0);
});

it('returns zero when bulk restoring empty array', function () {
    $count = $this->service->bulkRestore([]);

    expect($count)->toBe(0);
});

it('handles database errors gracefully', function () {
    $this->expectException(QueryException::class);
    $this->service->getAll(sortField: 'testinglakjflasdjkf');
});

it('handles connection timeout during query', function () {
    $this->expectException(QueryException::class);
    $this->service->getAll(sortField: "damar");
});
