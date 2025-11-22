<?php

use App\Models\Department;
use App\Models\StudyProgram;
use App\Services\StudyProgramService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new StudyProgramService();
});

// Helper functions - DIPERBAIKI
function createStudyProgram(array $attributes = []): StudyProgram
{
    // ✅ PASTIKAN department_id ADA
    if (!isset($attributes['department_id'])) {
        $attributes['department_id'] = createDepartment()->id;
    }

    return StudyProgram::factory()->create($attributes);
}

function createDepartment(): Department
{
    return Department::factory()->create();
}

describe('GET ALL', function () {
    beforeEach(function () {
        // ✅ RESET DAN PASTIKAN CLEAN STATE
        StudyProgram::query()->forceDelete();
        Department::query()->forceDelete();
    });

    it('returns paginated results by default', function () {
        $department = createDepartment();

        // ✅ CREATE DENGAN DEPARTMENT YANG VALID
        StudyProgram::factory()->count(15)->create(['department_id' => $department->id]);

        $result = $this->service->getAll();

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(15)
            ->and($result->perPage())->toBe(config('pagination.default'));
    });

    it('returns collection when pagination is disabled', function () {
         $department = createDepartment();

        // ✅ PASTIKAN HANYA 3 DATA
        StudyProgram::factory()->count(3)->create(['department_id' => $department->id]);

        $result = $this->service->getAll([], 'name', 'asc', null, false);

        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(3);
    });

    it('filters by search term', function () {
        // ✅ GUNAKAN HELPER YANG SUDAH DIPERBAIKI
        $program1 = createStudyProgram(['name' => 'Computer Science']);
        $program2 = createStudyProgram(['name' => 'Business Administration']);

        $result = $this->service->getAll(['search' => 'Computer']);

        expect($result)->toHaveCount(1)
            ->and($result->first()->id)->toBe($program1->id);
    });

    it('filters active study programs', function () {
        $activeProgram = createStudyProgram();
        $deletedProgram = createStudyProgram();
        $deletedProgram->delete();

        $result = $this->service->getAll(['status' => 'active']);

        expect($result)->toHaveCount(1)
            ->and($result->first()->id)->toBe($activeProgram->id);
    });

    it('filters deleted study programs', function () {
        $activeProgram = createStudyProgram();
        $deletedProgram = createStudyProgram();
        $deletedProgram->delete();

        $result = $this->service->getAll(['status' => 'deleted']);

        expect($result)->toHaveCount(1)
            ->and($result->first()->id)->toBe($deletedProgram->id);
    });

    it('sorts by field and direction', function () {
        $programA = createStudyProgram(['name' => 'Alpha']);
        $programB = createStudyProgram(['name' => 'Beta']);
        $programC = createStudyProgram(['name' => 'Gamma']);

        $result = $this->service->getAll([], 'name', 'desc', 10, false);

        expect($result->first()->name)->toBe('Gamma')
            ->and($result->last()->name)->toBe('Alpha');
    });

    it('uses custom per page value', function () {
        $department = createDepartment();

        StudyProgram::factory()->count(25)->create(['department_id' => $department->id]);

        $result = $this->service->getAll([], 'name', 'asc', 10);

        expect($result->perPage())->toBe(10)
            ->and($result->lastPage())->toBe(3);
    });
});

describe('FIND BY ID', function () {
    it('finds study program by id', function () {
        $program = createStudyProgram();

        $result = $this->service->findById($program->id);

        expect($result)->toBeInstanceOf(StudyProgram::class)
            ->and($result->id)->toBe($program->id);
    });

    it('finds with trashed study program', function () {
        $program = createStudyProgram();
        $program->delete();

        $result = $this->service->findById($program->id, [], true);

        expect($result)->toBeInstanceOf(StudyProgram::class)
            ->and($result->id)->toBe($program->id)
            ->and($result->deleted_at)->not->toBeNull();
    });

    it('finds with relationships', function () {
        $program = createStudyProgram();

        $result = $this->service->findById($program->id, ['department']);

        expect($result->relationLoaded('department'))->toBeTrue();
    });

    it('throws exception for non-existent study program', function () {
        expect(fn() => $this->service->findById(999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('CREATE', function () {
    it('creates study program successfully', function () {
        $department = createDepartment();
        $data = [
            'name' => 'Software Engineering',
            'code' => 'SE',
        ];

        $result = $this->service->create(null, $department->id, $data);

        expect($result)->toBeInstanceOf(StudyProgram::class)
            ->and($result->name)->toBe('Software Engineering')
            ->and($result->code)->toBe('SE')
            ->and($result->department_id)->toBe($department->id)
            ->and($result->head_id)->toBeNull();

        $this->assertDatabaseHas('study_programs', [
            'name' => 'Software Engineering',
            'code' => 'SE',
            'department_id' => $department->id,
        ]);
    });

    it('runs within database transaction', function () {
        $department = createDepartment();
        $data = ['name' => 'Test', 'code' => 'TST'];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn($callback) => $callback());

        $result = $this->service->create(null, $department->id, $data);

        expect($result)->toBeInstanceOf(StudyProgram::class);
    });
});

describe('UPDATE', function () {
    it('updates study program successfully', function () {
        $program = createStudyProgram();
        $newDepartment = createDepartment();
        $data = ['name' => 'Updated Name', 'code' => 'UPD'];

        $result = $this->service->update($program, null, $newDepartment->id, $data);

        expect($result->name)->toBe('Updated Name')
            ->and($result->code)->toBe('UPD')
            ->and($result->department_id)->toBe($newDepartment->id)
            ->and($result->head_id)->toBeNull();
    });

    it('updates study program with same department', function () {
        $department = createDepartment();
        $program = createStudyProgram(['department_id' => $department->id]);
        $data = ['name' => 'Updated Name', 'code' => 'UPD'];

        $result = $this->service->update($program, null, $department->id, $data);

        expect($result->name)->toBe('Updated Name')
            ->and($result->department_id)->toBe($department->id);
    });
});

describe('DELETE OPERATIONS', function () {
    it('soft deletes study program', function () {
        $program = createStudyProgram();

        $result = $this->service->delete($program);

        expect($result)->toBeTrue();
        $this->assertSoftDeleted($program);
    });

    it('restores soft deleted study program', function () {
        $program = createStudyProgram();
        $program->delete();

        $result = $this->service->restore($program->id);

        expect($result)->toBeTrue();
        $this->assertDatabaseHas('study_programs', [
            'id' => $program->id,
            'deleted_at' => null,
        ]);
    });

    it('force deletes study program', function () {
        $program = createStudyProgram();

        $result = $this->service->forceDelete($program->id);

        expect($result)->toBeTrue();
        $this->assertDatabaseMissing('study_programs', ['id' => $program->id]);
    });

    it('throws exception when restoring non-existent study program', function () {
        expect(fn() => $this->service->restore(999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('throws exception when force deleting non-existent study program', function () {
        expect(fn() => $this->service->forceDelete(999))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('BULK OPERATIONS', function () {
    beforeEach(function () {
        StudyProgram::query()->forceDelete();
    });

    it('bulk deletes multiple study programs', function () {
        $program1 = createStudyProgram();
        $program2 = createStudyProgram();
        $program3 = createStudyProgram();

        $result = $this->service->bulkDelete([$program1->id, $program2->id]);

        expect($result)->toBe(2);
        $this->assertSoftDeleted($program1);
        $this->assertSoftDeleted($program2);
        $this->assertNotSoftDeleted($program3);
    });

    it('bulk force deletes multiple study programs', function () {
        $program1 = createStudyProgram();
        $program2 = createStudyProgram();

        $result = $this->service->bulkForceDelete([$program1->id, $program2->id]);

        expect($result)->toBe(2);
        $this->assertDatabaseMissing('study_programs', ['id' => $program1->id]);
        $this->assertDatabaseMissing('study_programs', ['id' => $program2->id]);
    });

    it('bulk restores multiple study programs', function () {
        $program1 = createStudyProgram();
        $program2 = createStudyProgram();
        $program1->delete();
        $program2->delete();

        $result = $this->service->bulkRestore([$program1->id, $program2->id]);

        expect($result)->toBe(2);
        $this->assertDatabaseHas('study_programs', [
            'id' => $program1->id,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('study_programs', [
            'id' => $program2->id,
            'deleted_at' => null,
        ]);
    });

    it('handles empty arrays in bulk operations', function () {
        expect($this->service->bulkDelete([]))->toBe(0)
            ->and($this->service->bulkForceDelete([]))->toBe(0)
            ->and($this->service->bulkRestore([]))->toBe(0);
    });
});

describe('CONFIGURATION & EDGE CASES', function () {
    it('uses config pagination', function () {
        config(['pagination.default' => 25]);
        $service = new StudyProgramService();

        $result = $service->getAll();

        expect($result->perPage())->toBe(25);
    });

    it('handles invalid sort field gracefully', function () {
        createStudyProgram(['name' => 'Test']);

        $result = $this->service->getAll([], 'invalid_field', 'asc');

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });

    it('handles case insensitive status filter', function () {
        createStudyProgram();

        $result = $this->service->getAll(['status' => 'ACTIVE']);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    });
});

describe('DATASET DRIVEN TESTS', function () {
    beforeEach(function () {
        StudyProgram::query()->forceDelete();
    });

    it('handles various search terms', function (string $searchTerm, int $expectedCount) {
        createStudyProgram(['name' => 'Computer Science']);
        createStudyProgram(['name' => 'Information Technology']);
        createStudyProgram(['name' => 'Business Administration']);

        $result = $this->service->getAll(['search' => $searchTerm]);

        expect($result)->toHaveCount($expectedCount);
    })->with([
        ['computer', 1],
        ['science', 1],
        ['technology', 1],
        ['business', 1],
        ['admin', 1],
        ['nonexistent', 0],
    ]);

    it('handles different pagination scenarios', function (int $totalItems, int $perPage, int $expectedPages) {
        $department = createDepartment();
        StudyProgram::factory()->count($totalItems)->create(['department_id' => $department->id]);

        $result = $this->service->getAll([], 'name', 'asc', $perPage);

        expect($result->lastPage())->toBe($expectedPages);
    })->with([
        [15, 10, 2],
        [25, 10, 3],
        [5, 10, 1],
        [0, 10, 1],
    ]);
});
