<?php

use App\Models\AcademicClass;
use App\Models\Student;
use App\Services\AcademicClassService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AcademicClassService::class);
    $this->academicClass = AcademicClass::factory()->create();
});

describe('getAll method', function () {
    it('returns paginated results by default', function () {
        // Arrange
        AcademicClass::factory()->count(15)->create();

        // Act
        $result = $this->service->getAll();

        // Assert
        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->count())->toBeGreaterThan(0);
    });

    it('returns collection when isPaginated is false', function () {
        // Arrange
        AcademicClass::factory()->count(5)->create();

        // Act
        $result = $this->service->getAll([], 'name', 'asc', null, false);

        // Assert
        expect($result)->toBeInstanceOf(Collection::class)
            ->and($result)->toHaveCount(6); // 5 baru + 1 dari beforeEach
    });

    it('filters by search term', function () {
        // Arrange
        $class1 = AcademicClass::factory()->create(['name' => 'Mathematics Class']);
        $class2 = AcademicClass::factory()->create(['name' => 'Physics Class']);
        $filters = ['search' => 'Math'];

        // Act
        $result = $this->service->getAll($filters, 'name', 'asc', null, false);

        // Assert
        expect($result)->toHaveCount(1)
            ->and($result->first()->name)->toBe('Mathematics Class');
    });

    it('filters by active status', function () {
        // Arrange
        $deletedClass = AcademicClass::factory()->create();
        $deletedClass->delete();
        $filters = ['status' => 'active'];

        // Act
        $result = $this->service->getAll($filters, 'name', 'asc', null, false);

        // Assert
        expect($result->every(fn($class) => $class->deleted_at === null))->toBeTrue();
    });

    it('filters by deleted status', function () {
        // Arrange
        $deletedClass = AcademicClass::factory()->create();
        $deletedClass->delete();
        $filters = ['status' => 'deleted'];

        // Act
        $result = $this->service->getAll($filters, 'name', 'asc', null, false);

        // Assert
        expect($result->every(fn($class) => $class->deleted_at !== null))->toBeTrue();
    });

    it('sorts by specified field and direction', function () {
        AcademicClass::query()->delete();
        // Arrange
        AcademicClass::factory()->create(['name' => 'Beta Class']);
        AcademicClass::factory()->create(['name' => 'Alpha Class']);

        // Act
        $result = $this->service->getAll([], 'name', 'asc', null, false);

        // Assert
        expect($result->first()->name)->toBe('Alpha Class')
            ->and($result->last()->name)->toBe('Beta Class');
    });

    it('respects perPage parameter', function () {
        // Arrange
        AcademicClass::factory()->count(10)->create();
        $perPage = 5;

        // Act
        $result = $this->service->getAll([], 'name', 'asc', $perPage);

        // Assert
        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->perPage())->toBe($perPage);
    });
});

describe('findById method', function () {
    it('finds academic class by id', function () {
        // Act
        $result = $this->service->findById($this->academicClass->id);

        // Assert
        expect($result->id)->toBe($this->academicClass->id)
            ->and($result->name)->toBe($this->academicClass->name);
    });

    it('throws exception when academic class not found', function () {
        // Act & Assert
        expect(fn() => $this->service->findById(999))
            ->toThrow(Exception::class);
    });

    it('finds with trashed academic class', function () {
        // Arrange
        $this->academicClass->delete();

        // Act
        $result = $this->service->findById($this->academicClass->id, [], true);

        // Assert
        expect($result->id)->toBe($this->academicClass->id)
            ->and($result->trashed())->toBeTrue();
    });

    it('loads relationships when with parameter provided', function () {
        // Arrange
        $with = ['students'];

        // Act
        $result = $this->service->findById($this->academicClass->id, $with);

        // Assert
        expect($result->relationLoaded('students'))->toBeTrue();
    });
});

describe('create method', function () {
    it('creates new academic class', function () {
        // Arrange
        $data = [
            'name' => 'New Class',
            'code' => 'NEW001',
            'year' => 2024,
            'semester' => 1,
        ];

        // Act
        $result = $this->service->create($data);

        // Assert
        expect($result)->toBeInstanceOf(AcademicClass::class)
            ->and($result->name)->toBe('New Class')
            ->and($result->code)->toBe('NEW001')
            ->and($result->exists)->toBeTrue();
    });

    it('creates academic class with cl_id', function () {
        // Arrange
        $data = ['name' => 'Class with CL', 'code' => 'CL010', 'year' => 2024, 'semester' => 1];
        $clId = Student::factory()->create()->id;

        // Act
        $result = $this->service->create($data, $clId);

        // Assert
        expect($result->cl_id)->toBe($clId);
    });

    it('runs in database transaction', function () {
        // Arrange
        $data = ['name' => 'Class with CL', 'code' => 'CL010', 'year' => 2024, 'semester' => 1];
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->create($data);

        // Assert
        expect($result)->toBeInstanceOf(AcademicClass::class);
    });
});

describe('update method', function () {
    it('updates academic class', function () {
        // Arrange
        $data = [
            'name' => 'Updated Class',
            'code' => 'UPD001',
            'year' => 2024,
            'semester' => 1,
        ];

        // Act
        $result = $this->service->update($this->academicClass, $data);

        // Assert
        expect($result->name)->toBe('Updated Class')
            ->and($result->code)->toBe('UPD001')
            ->and($result->fresh()->name)->toBe('Updated Class');
    });

    it('updates academic class with cl_id', function () {
        // Arrange
        $data = ['name' => 'Updated Class', 'code' => 'UPD001', 'year' => 2024, 'semester' => 1];
        $clId = Student::factory()->create()->id;

        // Act
        $result = $this->service->update($this->academicClass, $data, $clId);

        // Assert
        expect($result->cl_id)->toBe($clId);
    });

    it('runs in database transaction', function () {
        // Arrange
        $data = ['name' => 'Update Transaction Test'];
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->update($this->academicClass, $data);

        // Assert
        expect($result)->toBeInstanceOf(AcademicClass::class);
    });
});

describe('addStudent method', function () {
    it('adds students to academic class', function () {
        // Arrange
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        $studentIds = [$student1->id, $student2->id];

        // Act
        $result = $this->service->addStudent($this->academicClass, $studentIds);

        // Assert
        expect($result)->toBeTrue()
            ->and($this->academicClass->students)->toHaveCount(2)
            ->and($this->academicClass->students->contains($student1))->toBeTrue()
            ->and($this->academicClass->students->contains($student2))->toBeTrue();
    });

    it('runs in database transaction', function () {
        // Arrange
        $student = Student::factory()->create();
        $studentIds = [$student->id];
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->addStudent($this->academicClass, $studentIds);

        // Assert
        expect($result)->toBeTrue();
    });
});

describe('removeStudent method', function () {
    it('removes student from academic class', function () {
        // Arrange
        $student = Student::factory()->create();
        $this->academicClass->students()->attach($student->id);

        // Act
        $result = $this->service->removeStudent($this->academicClass, $student->id);

        // Assert
        expect($result)->toBeTrue()
            ->and($this->academicClass->fresh()->students)->toHaveCount(0);
    });

    it('runs in database transaction', function () {
        // Arrange
        $student = Student::factory()->create();
        $this->academicClass->students()->attach($student->id);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->removeStudent($this->academicClass, $student->id);

        // Assert
        expect($result)->toBeTrue();
    });
});

describe('delete method', function () {
    it('soft deletes academic class', function () {
        // Act
        $result = $this->service->delete($this->academicClass);

        // Assert
        expect($result)->toBeTrue()
            ->and($this->academicClass->fresh()->trashed())->toBeTrue();
    });

    it('runs in database transaction', function () {
        // Arrange
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->delete($this->academicClass);

        // Assert
        expect($result)->toBeTrue();
    });
});

describe('restore method', function () {
    it('restores soft deleted academic class', function () {
        // Arrange
        $this->academicClass->delete();

        // Act
        $result = $this->service->restore($this->academicClass->id);

        // Assert
        expect($result)->toBeTrue()
            ->and($this->academicClass->fresh()->trashed())->toBeFalse();
    });

    it('runs in database transaction', function () {
        // Arrange
        $this->academicClass->delete();
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->restore($this->academicClass->id);

        // Assert
        expect($result)->toBeTrue();
    });
});

describe('forceDelete method', function () {
    it('permanently deletes academic class', function () {
        // Arrange
        $classId = $this->academicClass->id;

        // Act
        $result = $this->service->forceDelete($classId);

        // Assert
        expect($result)->toBeTrue()
            ->and(AcademicClass::withTrashed()->find($classId))->toBeNull();
    });

    it('runs in database transaction', function () {
        // Arrange
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn($callback) => $callback());

        // Act
        $result = $this->service->forceDelete($this->academicClass->id);

        // Assert
        expect($result)->toBeTrue();
    });
});

describe('bulk operations', function () {
    beforeEach(function () {
        $this->classes = AcademicClass::factory()->count(3)->create();
        $this->classIds = $this->classes->pluck('id')->toArray();
    });

    it('performs bulk delete', function () {
        // Act
        $result = $this->service->bulkDelete($this->classIds);

        // Assert
        expect($result)->toBe(3)
            ->and(AcademicClass::whereIn('id', $this->classIds)->count())->toBe(0);
    });

    it('performs bulk force delete', function () {
        // Act
        $result = $this->service->bulkForceDelete($this->classIds);

        // Assert
        expect($result)->toBe(3)
            ->and(AcademicClass::withTrashed()->whereIn('id', $this->classIds)->count())->toBe(0);
    });

    it('performs bulk restore', function () {
        // Arrange
        AcademicClass::whereIn('id', $this->classIds)->delete();

        // Act
        $result = $this->service->bulkRestore($this->classIds);

        // Assert
        expect($result)->toBe(3)
            ->and(AcademicClass::whereIn('id', $this->classIds)->count())->toBe(3);
    });

    // it('runs bulk operations in database transaction', function () {
    //     // Arrange
    //     DB::shouldReceive('transaction')->times(3)->andReturnUsing(fn($callback) => $callback());

    //     // Act & Assert
    //     expect($this->service->bulkDelete($this->classIds))->toBe(3)
    //         ->and($this->service->bulkForceDelete($this->classIds))->toBe(3)
    //         ->and($this->service->bulkRestore($this->classIds))->toBe(3);
    // });
});
