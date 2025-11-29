<?php

use App\Models\Department;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\DepartmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DepartmentService();

    // Create department first
    $this->department = Department::factory()->create([
        'code' => 'DEPT001',
        'name' => 'Human Resources',
    ]);

    // Create study program associated with department
    $this->studyProgram = StudyProgram::factory()->create([
        'department_id' => $this->department->id,
        'code' => 'SP001',
        'name' => 'Computer Science',
    ]);

    // Create user with lecturer role
    $this->user = User::factory()->lecturer()->create();

    // Create lecturer associated with user and study program
    $this->lecturer = Lecturer::factory()->create([
        'user_id' => $this->user->id,
        'sp_id' => $this->studyProgram->id
    ]);

});

describe('Department Service - GetAll Method', function () {
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

        expect($result->count())->toBe(1) // 1 from beforeEach + 1 active
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
});

describe('Department Service - FindById Method', function () {
    it('can find department by id', function () {
        $found = $this->service->findById($this->department->id);

        expect($found->id)->toBe($this->department->id)
            ->and($found->name)->toBe('Human Resources')
            ->and($found->code)->toBe('DEPT001');
    });

    it('can find department with trashed records', function () {
        $this->department->delete();

        $found = $this->service->findById($this->department->id, [], true);

        expect($found->id)->toBe($this->department->id)
            ->and($found->trashed())->toBeTrue();
    });

    it('throws exception when department not found', function () {
        $this->service->findById(999);
    })->throws(ModelNotFoundException::class);
});

describe('Department Service - Create Method', function () {
    it('can create a new department without head', function () {
        $data = [
            'code' => 'DEPT002',
            'name' => 'Engineering',
        ];

        $department = $this->service->create(data: $data);

        expect($department)->toBeInstanceOf(Department::class)
            ->and($department->code)->toBe('DEPT002')
            ->and($department->name)->toBe('Engineering')
            ->and($department->exists)->toBeTrue()
            ->and($department->head_id)->toBeNull();
    });

    // it('can create a new department with head', function () {
    //     $data = [
    //         'code' => 'DEPT002',
    //         'name' => 'Engineering',
    //     ];

    //     $department = $this->service->create(head_id: $this->lecturer->id, data: $data);

    //     expect($department)->toBeInstanceOf(Department::class)
    //         ->and($department->head_id)->toBe($this->lecturer->id)
    //         ->and($department->head)->toBeInstanceOf(Lecturer::class);
    // });

    it('creates department within transaction', function () {
        $data = ['code' => 'DEPT003', 'name' => 'Marketing'];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->create(data: $data);
    });
});

describe('Department Service - Update Method', function () {
    it('can update a department without head', function () {
        $newData = [
            'code' => 'DEPT001-UPDATED',
            'name' => 'HR Department Updated',
        ];

        $updated = $this->service->update($this->department, $newData);

        expect($updated->code)->toBe('DEPT001-UPDATED')
            ->and($updated->name)->toBe('HR Department Updated')
            ->and($updated->id)->toBe($this->department->id)
            ->and($updated->head_id)->toBeNull();
    });

    it('can update a department with head', function () {
        $newData = ['name' => 'Department with Head'];

        $updated = $this->service->update($this->department, $newData, $this->lecturer->id);

        expect($updated->head_id)->toBe($this->lecturer->id)
            ->and($updated->head)->toBeInstanceOf(Lecturer::class);
    });

    it('can remove head from department', function () {
        // Create a department with head first
        $newData = ['name' => 'Department without Head'];

        $updated = $this->service->update($this->department, $newData);

        expect($updated->head_id)->toBeNull();
    });

    it('can change department head', function () {
        // Create another lecturer for the new head
        $newUser = User::factory()->lecturer()->create();
        $newLecturer = Lecturer::factory()->create([
            'user_id' => $newUser->id,
            'sp_id' => $this->studyProgram->id
        ]);

        // Create department with current head
        $department = Department::factory()->create([
            'head_id' => $this->lecturer->id
        ]);

        $newData = ['name' => 'Updated Department'];

        $updated = $this->service->update($department, $newData, $newLecturer->id);

        expect($updated->head_id)->toBe($newLecturer->id)
            ->and($updated->head->id)->toBe($newLecturer->id);
    });

    it('updates department within transaction', function () {
        $newData = ['name' => 'Updated Name'];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->update($this->department, $newData);
    });
});

describe('Department Service - Delete Method', function () {
    it('can soft delete a department', function () {
        $result = $this->service->delete($this->department);

        expect($result)->toBeTrue()
            ->and($this->department->fresh())->not->toBeNull()
            ->and($this->department->fresh()->trashed())->toBeTrue()
            ->and(Department::find($this->department->id))->toBeNull()
            ->and(Department::withTrashed()->find($this->department->id))->not->toBeNull();
    });

    it('deletes department within transaction', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->delete($this->department);
    });
});

describe('Department Service - Restore Method', function () {
    it('can restore a soft deleted department', function () {
        $this->department->delete();

        $result = $this->service->restore($this->department->id);

        expect($result)->toBeTrue()
            ->and($this->department->fresh()->trashed())->toBeFalse();
    });

    it('throws exception when restoring non-existent department', function () {
        $this->service->restore(999);
    })->throws(ModelNotFoundException::class);
});

describe('Department Service - ForceDelete Method', function () {
    it('can force delete a department', function () {
        $result = $this->service->forceDelete($this->department->id);

        expect($result)->toBeTrue()
            ->and(Department::withTrashed()->find($this->department->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent department', function () {
        $this->service->forceDelete(999);
    })->throws(ModelNotFoundException::class);
});

describe('Department Service - Bulk Operations', function () {
    beforeEach(function () {
        $this->departments = Department::factory()->count(3)->create();
    });

    it('can bulk delete departments', function () {
        $ids = $this->departments->pluck('id')->toArray();

        $count = $this->service->bulkDelete($ids);

        expect($count)->toBe(3)
            ->and(Department::find($ids[0]))->toBeNull()
            ->and(Department::withTrashed()->find($ids[0]))->not->toBeNull();
    });

    it('can bulk force delete departments', function () {
        $ids = $this->departments->pluck('id')->take(2)->toArray();

        $count = $this->service->bulkForceDelete($ids);

        expect($count)->toBe(2)
            ->and(Department::find($ids[0]))->toBeNull()
            ->and(Department::withTrashed()->find($ids[0]))->toBeNull()
            ->and(Department::find($this->departments[2]->id))->not->toBeNull();
    });

    it('can bulk restore departments', function () {
        $ids = $this->departments->pluck('id')->toArray();
        Department::whereIn('id', $ids)->delete();

        $count = $this->service->bulkRestore($ids);

        expect($count)->toBe(3)
            ->and(Department::find($ids[0]))->not->toBeNull()
            ->and($this->departments[0]->fresh()->trashed())->toBeFalse();
    });

    it('returns zero when bulk deleting empty array', function () {
        $count = $this->service->bulkDelete([]);

        expect($count)->toBe(0);
    });

    it('returns zero when bulk restoring empty array', function () {
        $count = $this->service->bulkRestore([]);

        expect($count)->toBe(0);
    });
});

describe('Department Service - Error Handling', function () {
    it('handles database errors gracefully', function () {
        $this->expectException(QueryException::class);
        $this->service->getAll(sortField: 'invalid_field');
    });

    // it('handles connection timeout during query', function () {
    //     $this->expectException(QueryException::class);

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andThrow(new QueryException('', [], new Exception('Connection timeout')));

    //     $this->service->create(data: ['name' => 'Test']);
    // });
});
