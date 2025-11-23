<?php

use App\Models\Department;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new StudentService();

    // Create department
    $this->department = Department::factory()->create();

    // Create study program
    $this->studyProgram = StudyProgram::factory()->create([
        'department_id' => $this->department->id,
    ]);

    // Create user with student role
    $this->user = User::factory()->student()->create();

    // Create student
    $this->student = Student::factory()->create([
        'user_id' => $this->user->id,
        'sp_id' => $this->studyProgram->id,
        'generation' => 2022,
    ]);
});

describe('Student Service - GetAll Method', function () {
    it('can get all students with default parameters', function () {
        Student::factory()->count(5)->create();

        $result = $this->service->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBe(6) // 5 new + 1 from beforeEach
            ->and($result->perPage())->toBe(config('pagination.default'));
    });

    it('can get all students as collection when pagination is disabled', function () {
        Student::factory()->count(3)->create();

        $result = $this->service->getAll([], 'nim', 'asc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBe(4); // 3 new + 1 from beforeEach
    });

    it('can filter students by search term using nim', function () {
        Student::factory()->create(['nim' => '202201011']);
        Student::factory()->create(['nim' => '202201022']);

        $result = $this->service->getAll(['search' => '202201011']);

        expect($result->count())->toBe(1)
            ->and($result->first()->nim)->toBe('202201011');
    });

    it('can filter students by search term using generation', function () {
        Student::factory()->create(['generation' => 2023, 'nim' => '202301001']);
        Student::factory()->create(['generation' => 2024, 'nim' => '202401001']);

        $result = $this->service->getAll(['search' => '2023']);

        expect($result->count())->toBe(1)
            ->and($result->first()->generation)->toBe(2023);
    });

    it('can filter active students', function () {
        $deletedStudent = Student::factory()->create();
        $deletedStudent->delete();

        $result = $this->service->getAll(['status' => 'active']);

        expect($result->count())->toBe(1) // 1 from beforeEach + 1 active
            ->and($result->first()->trashed())->toBeFalse();
    });

    it('can filter deleted students', function () {
        $this->student->delete();

        $result = $this->service->getAll(['status' => 'deleted']);

        expect($result->count())->toBe(1)
            ->and($result->first()->trashed())->toBeTrue();
    });

    it('can sort students by nim', function () {
        Student::query()->delete();
        Student::factory()->create(['nim' => '202201001', 'generation' => 2022]);
        Student::factory()->create(['nim' => '202201002', 'generation' => 2022]);

        $result = $this->service->getAll([], 'nim', 'asc');

        expect($result->first()->nim)->toBe('202201001');
    });

    it('can sort students by generation', function () {
        Student::factory()->create(['generation' => 2021, 'nim' => '202101001']);
        Student::factory()->create(['generation' => 2023, 'nim' => '202301001']);

        $result = $this->service->getAll([], 'generation', 'desc');

        expect($result->first()->generation)->toBe(2023);
    });

    it('can sort students by created_at', function () {
        Student::query()->delete();
        $oldStudent = Student::factory()->create(['nim' => 'OLD001', 'created_at' => now()->subDays(2)]);
        $newStudent = Student::factory()->create(['nim' => 'NEW001', 'created_at' => now()]);

        $result = $this->service->getAll([], 'created_at', 'desc');

        expect($result->first()->nim)->toBe('NEW001');
    });

    it('can set custom pagination limit', function () {
        Student::factory()->count(10)->create();

        $result = $this->service->getAll([], 'nim', 'asc', 5);

        expect($result->count())->toBe(5)
            ->and($result->perPage())->toBe(5);
    });

    it('respects maximum pagination limit', function () {
        $maxLimit = config('pagination.max_limit');
        $result = $this->service->getAll([], 'nim', 'asc', $maxLimit + 100);

        expect($result->perPage())->toBe($maxLimit);
    });
});

describe('Student Service - FindById Method', function () {
    it('can find student by id', function () {
        $found = $this->service->findById($this->student->id);

        expect($found->id)->toBe($this->student->id)
            ->and($found->nim)->toBe($this->student->nim)
            ->and($found->generation)->toBe(2022)
            ->and($found->user_id)->toBe($this->user->id)
            ->and($found->sp_id)->toBe($this->studyProgram->id);
    });

    it('can find student with relationships', function () {
        $found = $this->service->findById($this->student->id, ['user', 'study_program']);

        expect($found->user)->toBeInstanceOf(User::class)
            ->and($found->study_program)->toBeInstanceOf(StudyProgram::class)
            ->and($found->relationLoaded('user'))->toBeTrue()
            ->and($found->relationLoaded('study_program'))->toBeTrue();
    });

    it('can find student with study program department relationship', function () {
        $found = $this->service->findById($this->student->id, ['study_program.department']);

        expect($found->study_program->department)->toBeInstanceOf(Department::class)
            ->and($found->study_program->relationLoaded('department'))->toBeTrue();
    });

    it('can find student with trashed records', function () {
        $this->student->delete();

        $found = $this->service->findById($this->student->id, [], true);

        expect($found->id)->toBe($this->student->id)
            ->and($found->trashed())->toBeTrue();
    });

    it('throws exception when student not found', function () {
        $this->service->findById(999);
    })->throws(ModelNotFoundException::class);
});

describe('Student Service - Create Method', function () {
    it('can create a new student', function () {
        $data = [
            'nim' => '202301001',
            'generation' => 2023,
        ];

        $newUser = User::factory()->student()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        $student = $this->service->create($newUser->id, $newStudyProgram->id, $data);

        expect($student)->toBeInstanceOf(Student::class)
            ->and($student->nim)->toBe('202301001')
            ->and($student->generation)->toBe(2023)
            ->and($student->user_id)->toBe($newUser->id)
            ->and($student->sp_id)->toBe($newStudyProgram->id)
            ->and($student->exists)->toBeTrue();
    });

    it('creates student within transaction', function () {
        $data = ['nim' => '202401001', 'generation' => 2024];
        $newUser = User::factory()->student()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->create($newUser->id, $newStudyProgram->id, $data);
    });

    it('requires valid user_id and sp_id', function () {
        $data = ['nim' => '202301001', 'generation' => 2023];

        $student = $this->service->create($this->user->id, $this->studyProgram->id, $data);

        expect($student->user_id)->toBe($this->user->id)
            ->and($student->sp_id)->toBe($this->studyProgram->id);
    });
});

describe('Student Service - Update Method', function () {
    it('can update a student', function () {
        $newData = [
            'nim' => '202201002',
            'generation' => 2023,
        ];

        $newUser = User::factory()->student()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        $updated = $this->service->update($this->student, $newUser->id, $newStudyProgram->id, $newData);

        expect($updated->nim)->toBe('202201002')
            ->and($updated->generation)->toBe(2023)
            ->and($updated->user_id)->toBe($newUser->id)
            ->and($updated->sp_id)->toBe($newStudyProgram->id)
            ->and($updated->id)->toBe($this->student->id);
    });

    it('can update student with same user and study program', function () {
        $newData = [
            'nim' => '202201003',
            'generation' => 2024,
        ];

        $updated = $this->service->update($this->student, $this->user->id, $this->studyProgram->id, $newData);

        expect($updated->nim)->toBe('202201003')
            ->and($updated->generation)->toBe(2024)
            ->and($updated->user_id)->toBe($this->user->id)
            ->and($updated->sp_id)->toBe($this->studyProgram->id);
    });

    it('updates student within transaction', function () {
        $newData = ['nim' => '202201004'];
        $newUser = User::factory()->student()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->update($this->student, $newUser->id, $newStudyProgram->id, $newData);
    });
});

describe('Student Service - Delete Method', function () {
    it('can soft delete a student', function () {
        $result = $this->service->delete($this->student);

        expect($result)->toBeTrue()
            ->and($this->student->fresh())->not->toBeNull()
            ->and($this->student->fresh()->trashed())->toBeTrue()
            ->and(Student::find($this->student->id))->toBeNull()
            ->and(Student::withTrashed()->find($this->student->id))->not->toBeNull();
    });

    it('deletes student within transaction', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->delete($this->student);
    });
});

describe('Student Service - Restore Method', function () {
    it('can restore a soft deleted student', function () {
        $this->student->delete();

        $result = $this->service->restore($this->student->id);

        expect($result)->toBeTrue()
            ->and($this->student->fresh()->trashed())->toBeFalse();
    });

    it('throws exception when restoring non-existent student', function () {
        $this->service->restore(999);
    })->throws(ModelNotFoundException::class);
});

describe('Student Service - ForceDelete Method', function () {
    it('can force delete a student', function () {
        $result = $this->service->forceDelete($this->student->id);

        expect($result)->toBeTrue()
            ->and(Student::withTrashed()->find($this->student->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent student', function () {
        $this->service->forceDelete(999);
    })->throws(ModelNotFoundException::class);
});

describe('Student Service - Bulk Operations', function () {
    beforeEach(function () {
        $this->students = Student::factory()->count(3)->create([
            'sp_id' => $this->studyProgram->id,
        ]);
    });

    it('can bulk delete students', function () {
        $ids = $this->students->pluck('id')->toArray();

        $count = $this->service->bulkDelete($ids);

        expect($count)->toBe(3)
            ->and(Student::find($ids[0]))->toBeNull()
            ->and(Student::withTrashed()->find($ids[0]))->not->toBeNull();
    });

    it('can bulk force delete students', function () {
        $ids = $this->students->pluck('id')->take(2)->toArray();

        $count = $this->service->bulkForceDelete($ids);

        expect($count)->toBe(2)
            ->and(Student::find($ids[0]))->toBeNull()
            ->and(Student::withTrashed()->find($ids[0]))->toBeNull()
            ->and(Student::find($this->students[2]->id))->not->toBeNull();
    });

    it('can bulk restore students', function () {
        $ids = $this->students->pluck('id')->toArray();
        Student::whereIn('id', $ids)->delete();

        $count = $this->service->bulkRestore($ids);

        expect($count)->toBe(3)
            ->and(Student::find($ids[0]))->not->toBeNull()
            ->and($this->students[0]->fresh()->trashed())->toBeFalse();
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

describe('Student Service - Error Handling', function () {
    // it('handles database errors gracefully during getAll', function () {
    //     $this->expectException(QueryException::class);
    //     $this->service->getAll(sortField: 'invalid_field');
    // });

    // it('handles connection timeout during create', function () {
    //     $this->expectException(QueryException::class);

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andThrow(new QueryException('', [], new Exception('Connection timeout')));

    //     $newUser = User::factory()->student()->create();
    //     $newStudyProgram = StudyProgram::factory()->create();
    //     $this->service->create($newUser->id, $newStudyProgram->id, ['nim' => '202501001']);
    // });

    it('handles database constraint violations', function () {
        $this->expectException(QueryException::class);

        // Try to create student with non-existent user or study program
        $this->service->create(9999, 9999, ['nim' => '202501001', 'generation' => 2025]);
    });
});
