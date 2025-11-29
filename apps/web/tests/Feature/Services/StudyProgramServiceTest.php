<?php

use App\Models\Department;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\StudyProgramService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new StudyProgramService();

    // Create department
    $this->department = Department::factory()->create([
        'code' => 'DEPT001',
        'name' => 'Computer Science Department',
    ]);

    // Create user with lecturer role
    $this->user = User::factory()->lecturer()->create();
    // Create study program
    $this->studyProgram = StudyProgram::factory()->create([
        'department_id' => $this->department->id,
        'code' => 'SP001',
        'name' => 'Software Engineering',
    ]);

    // Create lecturer
    $this->lecturer = Lecturer::factory()->create([
        'user_id' => $this->user->id,
        'sp_id' => $this->studyProgram->id,
    ]);


});

describe('StudyProgram Service - GetAll Method', function () {
    it('can get all study programs with default parameters', function () {
        StudyProgram::factory()->count(5)->create(['department_id' => $this->department->id]);

        $result = $this->service->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBe(6) // 5 new + 1 from beforeEach
            ->and($result->perPage())->toBe(config('pagination.default'));
    });

    it('can get all study programs as collection when pagination is disabled', function () {
        StudyProgram::factory()->count(3)->create(['department_id' => $this->department->id]);

        $result = $this->service->getAll([], 'name', 'asc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBe(4); // 3 new + 1 from beforeEach
    });

    it('can filter study programs by search term', function () {
        StudyProgram::factory()->create(['name' => 'Artificial Intelligence', 'code' => 'AI', 'department_id' => $this->department->id]);
        StudyProgram::factory()->create(['name' => 'Data Science', 'code' => 'DS', 'department_id' => $this->department->id]);

        $result = $this->service->getAll(['search' => 'Intelligence']);

        expect($result->count())->toBe(1)
            ->and($result->first()->name)->toBe('Artificial Intelligence');
    });

    it('can filter study programs by code search', function () {
        StudyProgram::factory()->create(['name' => 'Test Program', 'code' => 'TEST123', 'department_id' => $this->department->id]);
        StudyProgram::factory()->create(['name' => 'Another Program', 'code' => 'OTHER', 'department_id' => $this->department->id]);

        $result = $this->service->getAll(['search' => 'TEST']);

        expect($result->count())->toBe(1)
            ->and($result->first()->code)->toBe('TEST123');
    });

    it('can filter active study programs', function () {
        $deletedProgram = StudyProgram::factory()->create(['department_id' => $this->department->id]);
        $deletedProgram->delete();

        $result = $this->service->getAll(['status' => 'active']);

        expect($result->count())->toBe(1) // 1 from beforeEach + 1 active
            ->and($result->first()->trashed())->toBeFalse();
    });

    it('can filter deleted study programs', function () {
        $this->studyProgram->delete();

        $result = $this->service->getAll(['status' => 'deleted']);

        expect($result->count())->toBe(1)
            ->and($result->first()->trashed())->toBeTrue();
    });

    it('can sort study programs by different fields and directions', function () {
        StudyProgram::factory()->create(['name' => 'Accounting', 'code' => 'ACC', 'department_id' => $this->department->id]);
        StudyProgram::factory()->create(['name' => 'Business', 'code' => 'BUS', 'department_id' => $this->department->id]);

        $resultAsc = $this->service->getAll([], 'name', 'asc');
        $resultDesc = $this->service->getAll([], 'name', 'desc');

        expect($resultAsc->first()->name)->toBe('Accounting')
            ->and($resultDesc->first()->name)->toBe('Software Engineering');
    });

    it('can sort study programs by code', function () {
        StudyProgram::factory()->create(['name' => 'Program A', 'code' => 'ZCODE', 'department_id' => $this->department->id]);
        StudyProgram::factory()->create(['name' => 'Program B', 'code' => 'ACODE', 'department_id' => $this->department->id]);

        $result = $this->service->getAll([], 'code', 'asc');

        expect($result->first()->code)->toBe('ACODE');
    });

    it('can set custom pagination limit', function () {
        StudyProgram::factory()->count(10)->create(['department_id' => $this->department->id]);

        $result = $this->service->getAll([], 'name', 'asc', 5);

        expect($result->count())->toBe(5)
            ->and($result->perPage())->toBe(5);
    });

    it('respects maximum pagination limit', function () {
        $maxLimit = config('pagination.max_limit');
        $result = $this->service->getAll([], 'name', 'asc', $maxLimit + 100);

        expect($result->perPage())->toBe($maxLimit);
    });
});

describe('StudyProgram Service - FindById Method', function () {
    it('can find study program by id', function () {
        $found = $this->service->findById($this->studyProgram->id);

        expect($found->id)->toBe($this->studyProgram->id)
            ->and($found->name)->toBe('Software Engineering')
            ->and($found->code)->toBe('SP001');
    });

    it('can find study program with relationships', function () {
        $found = $this->service->findById($this->studyProgram->id, ['department', 'head']);

        expect($found->department)->toBeInstanceOf(Department::class)
            ->and($found->relationLoaded('department'))->toBeTrue();
    });

    it('can find study program with trashed records', function () {
        $this->studyProgram->delete();

        $found = $this->service->findById($this->studyProgram->id, [], true);

        expect($found->id)->toBe($this->studyProgram->id)
            ->and($found->trashed())->toBeTrue();
    });

    it('throws exception when study program not found', function () {
        $this->service->findById(999);
    })->throws(ModelNotFoundException::class);
});

describe('StudyProgram Service - Create Method', function () {
    it('can create a new study program without head', function () {
        $data = [
            'code' => 'SP002',
            'name' => 'Information Technology',
        ];

        $studyProgram = $this->service->create($data, null, $this->department->id);

        expect($studyProgram)->toBeInstanceOf(StudyProgram::class)
            ->and($studyProgram->code)->toBe('SP002')
            ->and($studyProgram->name)->toBe('Information Technology')
            ->and($studyProgram->department_id)->toBe($this->department->id)
            ->and($studyProgram->head_id)->toBeNull()
            ->and($studyProgram->exists)->toBeTrue();
    });

    it('can create a new study program with head', function () {
        $data = [
            'code' => 'SP003',
            'name' => 'Computer Engineering',
        ];

        $studyProgram = $this->service->create($data, $this->lecturer->id, $this->department->id);

        expect($studyProgram)->toBeInstanceOf(StudyProgram::class)
            ->and($studyProgram->head_id)->toBe($this->lecturer->id)
            ->and($studyProgram->head)->toBeInstanceOf(Lecturer::class)
            ->and($studyProgram->department_id)->toBe($this->department->id);
    });

    it('creates study program within transaction', function () {
        $data = ['code' => 'SP004', 'name' => 'Test Program'];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->create($data, null, $this->department->id);
    });
});

describe('StudyProgram Service - Update Method', function () {
    it('can update a study program without head', function () {
        $newData = [
            'code' => 'SP001-UPDATED',
            'name' => 'Software Engineering Updated',
        ];

        $updated = $this->service->update($this->studyProgram, $newData, null, $this->department->id);

        expect($updated->code)->toBe('SP001-UPDATED')
            ->and($updated->name)->toBe('Software Engineering Updated')
            ->and($updated->id)->toBe($this->studyProgram->id)
            ->and($updated->head_id)->toBeNull();
    });

    it('can update a study program with head', function () {
        $newData = [
            'name' => 'Study Program with Head',
        ];

        $updated = $this->service->update($this->studyProgram, $newData, $this->lecturer->id, $this->department->id);

        expect($updated->head_id)->toBe($this->lecturer->id)
            ->and($updated->head)->toBeInstanceOf(Lecturer::class)
            ->and($updated->department_id)->toBe($this->department->id);
    });

    // it(description: 'can remove head from study program', function () {
    //     // Create study program with head first
    //     $studyProgramWithHead = StudyProgram::factory()->create([
    //         'department_id' => $this->department->id,
    //         'head_id' => $this->lecturer->id,
    //     ]);

    //     $newData = ['name' => 'Study Program without Head'];

    //     $updated = $this->service->update($studyProgramWithHead, $newData, null, $this->department->id);

    //     expect($updated->head_id)->toBeNull();
    // });

    // it('can change study program head', function () {
    //     // Create another lecturer for the new head
    //     $newUser = User::factory()->lecturer()->create();
    //     $newLecturer = Lecturer::factory()->create([
    //         'user_id' => $newUser->id,
    //     ]);

    //     // Create study program with current head
    //     $studyProgram = StudyProgram::factory()->create([
    //         'department_id' => $this->department->id,
    //         'head_id' => $this->lecturer->id,
    //     ]);

    //     $newData = ['name' => 'Updated Study Program'];

    //     $updated = $this->service->update($studyProgram, $newData, $newLecturer->id, $this->department->id);

    //     expect($updated->head_id)->toBe($newLecturer->id)
    //         ->and($updated->head->id)->toBe($newLecturer->id);
    // });

    it('can change study program department', function () {
        $newDepartment = Department::factory()->create();
        $newData = ['name' => 'Study Program with New Department'];

        $updated = $this->service->update($this->studyProgram, $newData, null, $newDepartment->id);

        expect($updated->department_id)->toBe($newDepartment->id)
            ->and($updated->department->id)->toBe($newDepartment->id);
    });

    it('updates study program within transaction', function () {
        $newData = ['name' => 'Updated Name'];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->update($this->studyProgram, $newData, null, $this->department->id);
    });
});

describe('StudyProgram Service - Delete Method', function () {
    it('can soft delete a study program', function () {
        $result = $this->service->delete($this->studyProgram);

        expect($result)->toBeTrue()
            ->and($this->studyProgram->fresh())->not->toBeNull()
            ->and($this->studyProgram->fresh()->trashed())->toBeTrue()
            ->and(StudyProgram::find($this->studyProgram->id))->toBeNull()
            ->and(StudyProgram::withTrashed()->find($this->studyProgram->id))->not->toBeNull();
    });

    it('deletes study program within transaction', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->delete($this->studyProgram);
    });
});

describe('StudyProgram Service - Restore Method', function () {
    it('can restore a soft deleted study program', function () {
        $this->studyProgram->delete();

        $result = $this->service->restore($this->studyProgram->id);

        expect($result)->toBeTrue()
            ->and($this->studyProgram->fresh()->trashed())->toBeFalse();
    });

    it('throws exception when restoring non-existent study program', function () {
        $this->service->restore(999);
    })->throws(ModelNotFoundException::class);
});

describe('StudyProgram Service - ForceDelete Method', function () {
    it('can force delete a study program', function () {
        $result = $this->service->forceDelete($this->studyProgram->id);

        expect($result)->toBeTrue()
            ->and(StudyProgram::withTrashed()->find($this->studyProgram->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent study program', function () {
        $this->service->forceDelete(999);
    })->throws(ModelNotFoundException::class);
});

describe('StudyProgram Service - Bulk Operations', function () {
    beforeEach(function () {
        $this->studyPrograms = StudyProgram::factory()->count(3)->create([
            'department_id' => $this->department->id,
        ]);
    });

    it('can bulk delete study programs', function () {
        $ids = $this->studyPrograms->pluck('id')->toArray();

        $count = $this->service->bulkDelete($ids);

        expect($count)->toBe(3)
            ->and(StudyProgram::find($ids[0]))->toBeNull()
            ->and(StudyProgram::withTrashed()->find($ids[0]))->not->toBeNull();
    });

    it('can bulk force delete study programs', function () {
        $ids = $this->studyPrograms->pluck('id')->take(2)->toArray();

        $count = $this->service->bulkForceDelete($ids);

        expect($count)->toBe(2)
            ->and(StudyProgram::find($ids[0]))->toBeNull()
            ->and(StudyProgram::withTrashed()->find($ids[0]))->toBeNull()
            ->and(StudyProgram::find($this->studyPrograms[2]->id))->not->toBeNull();
    });

    it('can bulk restore study programs', function () {
        $ids = $this->studyPrograms->pluck('id')->toArray();
        StudyProgram::whereIn('id', $ids)->delete();

        $count = $this->service->bulkRestore($ids);

        expect($count)->toBe(3)
            ->and(StudyProgram::find($ids[0]))->not->toBeNull()
            ->and($this->studyPrograms[0]->fresh()->trashed())->toBeFalse();
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

describe('StudyProgram Service - Error Handling', function () {
    // it('handles database errors gracefully during getAll', function () {
    //     $this->expectException(QueryException::class);
    //     $this->service->getAll(sortField: 'invalid_field');
    // });

    // it('handles connection timeout during create', function () {
    //     $this->expectException(QueryException::class);

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andThrow(new QueryException('', [], new Exception('Connection timeout')));

    //     $this->service->create(['name' => 'Test'], null, $this->department->id);
    // });

    it('handles database constraint violations', function () {
        $this->expectException(QueryException::class);

        // Try to create study program with non-existent department
        $this->service->create(['name' => 'Test'], null, 9999);
    });
});
