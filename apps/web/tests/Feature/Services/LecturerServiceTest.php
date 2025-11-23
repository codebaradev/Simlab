<?php

use App\Models\Department;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\LecturerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new LecturerService();

    // Create department
    $this->department = Department::factory()->create();

    // Create study program
    $this->studyProgram = StudyProgram::factory()->create([
        'department_id' => $this->department->id,
    ]);

    // Create user with lecturer role
    $this->user = User::factory()->lecturer()->create();

    // Create lecturer
    $this->lecturer = Lecturer::factory()->create([
        'user_id' => $this->user->id,
        'sp_id' => $this->studyProgram->id,
        'nidn' => '12345678',
        'nip' => '87654321',
        'code' => 'LEC001',
    ]);
});

describe('Lecturer Service - GetAll Method', function () {
    it('can get all lecturers with default parameters', function () {
        Lecturer::factory()->count(5)->create();

        $result = $this->service->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBe(6) // 5 new + 1 from beforeEach
            ->and($result->perPage())->toBe(config('pagination.default'));
    });

    it('can get all lecturers as collection when pagination is disabled', function () {
        Lecturer::factory()->count(3)->create();

        $result = $this->service->getAll([], 'code', 'asc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result->count())->toBe(4); // 3 new + 1 from beforeEach
    });

    it('can filter lecturers by search term using nidn', function () {
        Lecturer::factory()->create(['nidn' => '11111111']);
        Lecturer::factory()->create(['nidn' => '22222222']);

        $result = $this->service->getAll(['search' => '11111111']);

        expect($result->count())->toBe(1)
            ->and($result->first()->nidn)->toBe('11111111');
    });

    it('can filter lecturers by search term using nip', function () {
        Lecturer::factory()->create(['nip' => '33333333']);
        Lecturer::factory()->create(['nip' => '44444444']);

        $result = $this->service->getAll(['search' => '33333333']);

        expect($result->count())->toBe(1)
            ->and($result->first()->nip)->toBe('33333333');
    });

    it('can filter lecturers by search term using code', function () {
        Lecturer::factory()->create(['code' => 'LEC002']);
        Lecturer::factory()->create(['code' => 'LEC003']);

        $result = $this->service->getAll(['search' => 'LEC002']);

        expect($result->count())->toBe(1)
            ->and($result->first()->code)->toBe('LEC002');
    });

    it('can filter active lecturers', function () {
        $deletedLecturer = Lecturer::factory()->create();
        $deletedLecturer->delete();

        $result = $this->service->getAll(['status' => 'active']);

        expect($result->count())->toBe(1) // 1 from beforeEach + 1 active
            ->and($result->first()->trashed())->toBeFalse();
    });

    it('can filter deleted lecturers', function () {
        $this->lecturer->delete();

        $result = $this->service->getAll(['status' => 'deleted']);

        expect($result->count())->toBe(1)
            ->and($result->first()->trashed())->toBeTrue();
    });

    it('can sort lecturers by nidn', function () {
        Lecturer::factory()->create(['nidn' => '00000001', 'code' => 'LEC002']);
        Lecturer::factory()->create(['nidn' => '00000002', 'code' => 'LEC003']);

        $result = $this->service->getAll([], 'nidn', 'asc');

        expect($result->first()->nidn)->toBe('00000001');
    });

    it('can sort lecturers by nip', function () {
        Lecturer::factory()->create(['nip' => '10000001', 'code' => 'LEC002']);
        Lecturer::factory()->create(['nip' => '10000002', 'code' => 'LEC003']);

        $result = $this->service->getAll([], 'nip', 'desc');

        expect($result->first()->nip)->toBe('87654321');
    });

    it('can sort lecturers by code', function () {
        Lecturer::factory()->create(['code' => 'ALEC01', 'nidn' => '11111111']);
        Lecturer::factory()->create(['code' => 'BLEC01', 'nidn' => '22222222']);

        $result = $this->service->getAll([], 'code', 'asc');

        expect($result->first()->code)->toBe('ALEC01');
    });

    it('can sort lecturers by created_at', function () {
        $oldLecturer = Lecturer::factory()->create(['code' => 'OLD', 'created_at' => now()->subDays(2)]);
        $newLecturer = Lecturer::factory()->create(['code' => 'NEW', 'created_at' => now()]);

        $result = $this->service->getAll([], 'created_at', 'desc');

        expect($result->first()->code)->toBe('LEC001');
    });

    it('can set custom pagination limit', function () {
        Lecturer::factory()->count(10)->create();

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

describe('Lecturer Service - FindById Method', function () {
    it('can find lecturer by id', function () {
        $found = $this->service->findById($this->lecturer->id);

        expect($found->id)->toBe($this->lecturer->id)
            ->and($found->code)->toBe('LEC001')
            ->and($found->nidn)->toBe('12345678')
            ->and($found->nip)->toBe('87654321');
    });

    it('can find lecturer with relationships', function () {
        $found = $this->service->findById($this->lecturer->id, ['study_program', 'head_of_department', 'head_of_sp']);

        expect($found->study_program)->toBeInstanceOf(StudyProgram::class)
            ->and($found->relationLoaded('study_program'))->toBeTrue();
    });

    it('can find lecturer with trashed records', function () {
        $this->lecturer->delete();

        $found = $this->service->findById($this->lecturer->id, [], true);

        expect($found->id)->toBe($this->lecturer->id)
            ->and($found->trashed())->toBeTrue();
    });

    it('throws exception when lecturer not found', function () {
        $this->service->findById(999);
    })->throws(ModelNotFoundException::class);
});

describe('Lecturer Service - Create Method', function () {
    it('can create a new lecturer', function () {
        $data = [
            'nidn' => '99999999',
            'nip' => '88888888',
            'code' => 'LEC999',
        ];

        $newUser = User::factory()->lecturer()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        $lecturer = $this->service->create($newUser->id, $newStudyProgram->id, $data);

        expect($lecturer)->toBeInstanceOf(Lecturer::class)
            ->and($lecturer->nidn)->toBe('99999999')
            ->and($lecturer->nip)->toBe('88888888')
            ->and($lecturer->code)->toBe('LEC999')
            ->and($lecturer->user_id)->toBe($newUser->id)
            ->and($lecturer->sp_id)->toBe($newStudyProgram->id)
            ->and($lecturer->exists)->toBeTrue();
    });

    it('creates lecturer within transaction', function () {
        $data = ['nidn' => '55555555', 'nip' => '66666666', 'code' => 'LEC555'];
        $newUser = User::factory()->lecturer()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->create($newUser->id, $newStudyProgram->id, $data);
    });
});

describe('Lecturer Service - Update Method', function () {
    it('can update a lecturer', function () {
        $newData = [
            'nidn' => '11111111',
            'nip' => '22222222',
            'code' => 'LEC111',
        ];

        $newUser = User::factory()->lecturer()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        $updated = $this->service->update($this->lecturer, $newUser->id, $newStudyProgram->id, $newData);

        expect($updated->nidn)->toBe('11111111')
            ->and($updated->nip)->toBe('22222222')
            ->and($updated->code)->toBe('LEC111')
            ->and($updated->user_id)->toBe($newUser->id)
            ->and($updated->sp_id)->toBe($newStudyProgram->id)
            ->and($updated->id)->toBe($this->lecturer->id);
    });

    it('updates lecturer within transaction', function () {
        $newData = ['nidn' => '77777777'];
        $newUser = User::factory()->lecturer()->create();
        $newStudyProgram = StudyProgram::factory()->create();

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->update($this->lecturer, $newUser->id, $newStudyProgram->id, $newData);
    });
});

describe('Lecturer Service - Delete Method', function () {
    it('can soft delete a lecturer', function () {
        $result = $this->service->delete($this->lecturer);

        expect($result)->toBeTrue()
            ->and($this->lecturer->fresh())->not->toBeNull()
            ->and($this->lecturer->fresh()->trashed())->toBeTrue()
            ->and(Lecturer::find($this->lecturer->id))->toBeNull()
            ->and(Lecturer::withTrashed()->find($this->lecturer->id))->not->toBeNull();
    });

    it('deletes lecturer within transaction', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $this->service->delete($this->lecturer);
    });
});

describe('Lecturer Service - Restore Method', function () {
    it('can restore a soft deleted lecturer', function () {
        $this->lecturer->delete();

        $result = $this->service->restore($this->lecturer->id);

        expect($result)->toBeTrue()
            ->and($this->lecturer->fresh()->trashed())->toBeFalse();
    });

    it('throws exception when restoring non-existent lecturer', function () {
        $this->service->restore(999);
    })->throws(ModelNotFoundException::class);
});

describe('Lecturer Service - ForceDelete Method', function () {
    it('can force delete a lecturer', function () {
        $result = $this->service->forceDelete($this->lecturer->id);

        expect($result)->toBeTrue()
            ->and(Lecturer::withTrashed()->find($this->lecturer->id))->toBeNull();
    });

    it('throws exception when force deleting non-existent lecturer', function () {
        $this->service->forceDelete(999);
    })->throws(ModelNotFoundException::class);
});

describe('Lecturer Service - Bulk Operations', function () {
    beforeEach(function () {
        $this->lecturers = Lecturer::factory()->count(3)->create([
            'sp_id' => $this->studyProgram->id,
        ]);
    });

    it('can bulk delete lecturers', function () {
        $ids = $this->lecturers->pluck('id')->toArray();

        $count = $this->service->bulkDelete($ids);

        expect($count)->toBe(3)
            ->and(Lecturer::find($ids[0]))->toBeNull()
            ->and(Lecturer::withTrashed()->find($ids[0]))->not->toBeNull();
    });

    it('can bulk force delete lecturers', function () {
        $ids = $this->lecturers->pluck('id')->take(2)->toArray();

        $count = $this->service->bulkForceDelete($ids);

        expect($count)->toBe(2)
            ->and(Lecturer::find($ids[0]))->toBeNull()
            ->and(Lecturer::withTrashed()->find($ids[0]))->toBeNull()
            ->and(Lecturer::find($this->lecturers[2]->id))->not->toBeNull();
    });

    it('can bulk restore lecturers', function () {
        $ids = $this->lecturers->pluck('id')->toArray();
        Lecturer::whereIn('id', $ids)->delete();

        $count = $this->service->bulkRestore($ids);

        expect($count)->toBe(3)
            ->and(Lecturer::find($ids[0]))->not->toBeNull()
            ->and($this->lecturers[0]->fresh()->trashed())->toBeFalse();
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

describe('Lecturer Service - Error Handling', function () {
    // it('handles database errors gracefully during getAll', function () {
    //     $this->expectException(QueryException::class);
    //     $this->service->getAll(sortField: 'invalid_field');
    // });

    // it('handles connection timeout during create', function () {
    //     $this->expectException(QueryException::class);

    //     DB::shouldReceive('transaction')
    //         ->once()
    //         ->andThrow(new QueryException('', [], new Exception('Connection timeout')));

    //     $newUser = User::factory()->lecturer()->create();
    //     $newStudyProgram = StudyProgram::factory()->create();
    //     $this->service->create($newUser->id, $newStudyProgram->id, ['nidn' => '123']);
    // });

    it('handles database constraint violations', function () {
        $this->expectException(QueryException::class);

        // Try to create lecturer with non-existent user or study program
        $this->service->create(9999, 9999, ['nidn' => '123', 'nip' => '456', 'code' => 'TEST']);
    });
});
