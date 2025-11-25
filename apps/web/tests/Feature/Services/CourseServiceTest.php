<?php

use App\Models\AcademicClass;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new CourseService();
    $this->courseData = [
        'name' => 'Mathematics',
        'year' => 2024,
        'sks' => 3,
        'semester' => 1,
    ];
});

describe('CourseService', function () {
    describe('Constructor', function () {
        it('should initialize with correct pagination values', function () {
            expect($this->service)->toBeInstanceOf(CourseService::class);
        });
    });

    describe('getAll', function () {
        beforeEach(function () {
            Course::factory()->create(['name' => 'Physics', 'year' => 2023]);
            Course::factory()->create(['name' => 'Chemistry', 'year' => 2024]);
            Course::factory()->create(['name' => 'Biology', 'year' => 2024]);
        });

        it('should return paginated results by default', function () {
            $result = $this->service->getAll();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
            expect($result->count())->toBe(3);
        });

        it('should return collection when isPaginated is false', function () {
            $result = $this->service->getAll([], 'name', 'asc', null, false);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });

        it('should apply search filter', function () {
            Course::query()->delete();
            Course::factory()->create(['name' => 'Advanced Physics']);

            $result = $this->service->getAll(['search' => 'Physics']);

            expect($result->count())->toBe(1);
            expect($result->first()->name)->toBe('Advanced Physics');
        });

        it('should apply active status filter', function () {
            $course = Course::factory()->create();
            $course->delete();

            $result = $this->service->getAll(['status' => 'active']);

            expect($result->count())->toBe(3);
            expect($result->pluck('name'))->toContain('Physics', 'Chemistry', 'Biology');
        });

        it('should apply deleted status filter', function () {
            $course = Course::factory()->create(['name' => 'Deleted Course']);
            $course->delete();

            $result = $this->service->getAll(['status' => 'deleted']);

            expect($result->count())->toBe(1);
            expect($result->first()->name)->toBe('Deleted Course');
        });

        it('should sort by specified field and direction', function () {
            $result = $this->service->getAll([], 'year', 'desc');

            expect($result->first()->year)->toBe(2024);
            expect($result->last()->year)->toBe(2023);
        });

        it('should respect perPage parameter', function () {
            $result = $this->service->getAll([], 'name', 'asc', 2);

            expect($result->count())->toBe(2);
            expect($result->perPage())->toBe(2);
        });
    });

    describe('findById', function () {
        it('should find course by id', function () {
            $course = Course::factory()->create($this->courseData);

            $found = $this->service->findById($course->id);

            expect($found->id)->toBe($course->id);
            expect($found->name)->toBe('Mathematics');
        });

        it('should find course with relationships', function () {
            $course = Course::factory()->create();

            $found = $this->service->findById($course->id, ['lecturers']);

            expect($found)->toBeInstanceOf(Course::class);
            expect($found->relationLoaded('lecturers'))->toBeTrue();
        });

        it('should find trashed course when withTrashed is true', function () {
            $course = Course::factory()->create();
            $course->delete();

            $found = $this->service->findById($course->id, [], true);

            expect($found->id)->toBe($course->id);
            expect($found->trashed())->toBeTrue();
        });

        it('should throw exception when course not found', function () {
            expect(fn() => $this->service->findById(999))
                ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        });
    });

    describe('create', function () {
        it('should create a new course', function () {
            $course = $this->service->create($this->courseData);

            expect($course)->toBeInstanceOf(Course::class);
            expect($course->name)->toBe('Mathematics');
            expect($course->year)->toBe(2024);
            expect($course->sks)->toBe(3);
        });

        it('should persist course in database', function () {
            $course = $this->service->create($this->courseData);

            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'name' => 'Mathematics',
                'year' => 2024
            ]);
        });

        it('should run in transaction', function () {
            DB::shouldReceive('transaction')
                ->once()
                ->andReturnUsing(fn($callback) => $callback());

            $this->service->create($this->courseData);
        });
    });

    describe('update', function () {
        it('should update existing course', function () {
            $course = Course::factory()->create(['name' => 'Old Name']);
            $updateData = ['name' => 'Updated Name', 'year' => 2025];

            $updated = $this->service->update($course, $updateData);

            expect($updated->name)->toBe('Updated Name');
            expect($updated->year)->toBe(2025);
        });

        it('should persist changes in database', function () {
            $course = Course::factory()->create(['name' => 'Old Name']);

            $this->service->update($course, ['name' => 'New Name']);

            $this->assertDatabaseHas('courses', [
                'id' => $course->id,
                'name' => 'New Name'
            ]);
        });
    });

    describe('lecturer management', function () {
        beforeEach(function () {
            $this->course = Course::factory()->create();
            $this->lecturer = Lecturer::factory()->create(); // Assuming User model for lecturers
        });

        it('should add lecturer to course', function () {
            $result = $this->service->addLecturer($this->course, $this->lecturer->id);

            expect($result)->toBeTrue();
            expect($this->course->lecturers)->toHaveCount(1);
            expect($this->course->lecturers->first()->id)->toBe($this->lecturer->id);
        });

        it('should remove lecturer from course', function () {
            $this->course->lecturers()->attach($this->lecturer->id);

            $result = $this->service->removeLecturer($this->course, $this->lecturer->id);

            expect($result)->toBeTrue();
            expect($this->course->lecturers)->toHaveCount(0);
        });
    });

    describe('delete operations', function () {
        it('should soft delete course', function () {
            $course = Course::factory()->create();

            $result = $this->service->delete($course);

            expect($result)->toBeTrue();
            expect(Course::find($course->id))->toBeNull();
            expect(Course::withTrashed()->find($course->id))->not->toBeNull();
        });

        it('should restore soft deleted course', function () {
            $course = Course::factory()->create();
            $course->delete();

            $result = $this->service->restore($course->id);

            expect($result)->toBeTrue();
            expect(Course::find($course->id))->not->toBeNull();
        });

        it('should force delete course', function () {
            $course = Course::factory()->create();

            $result = $this->service->forceDelete($course->id);

            expect($result)->toBeTrue();
            expect(Course::withTrashed()->find($course->id))->toBeNull();
        });
    });

    describe('bulk operations', function () {
        beforeEach(function () {
            $this->courses = Course::factory()->count(3)->create();
            $this->courseIds = $this->courses->pluck('id')->toArray();
        });

        it('should bulk delete courses', function () {
            $result = $this->service->bulkDelete($this->courseIds);

            expect($result)->toBe(3);
            expect(Course::count())->toBe(0);
            expect(Course::withTrashed()->count())->toBe(3);
        });

        it('should bulk restore courses', function () {
            // First delete all courses
            Course::whereIn('id', $this->courseIds)->delete();

            $result = $this->service->bulkRestore($this->courseIds);

            expect($result)->toBe(3);
            expect(Course::count())->toBe(3);
        });

        it('should bulk force delete courses', function () {
            $result = $this->service->bulkForceDelete($this->courseIds);

            expect($result)->toBe(3);
            expect(Course::count())->toBe(0);
            expect(Course::withTrashed()->count())->toBe(0);
        });
    });

    describe('addAcademicClass method', function () {
        it('can add academic classes to course', function () {
            // Arrange
            $course = Course::factory()->create();
            $academicClass1 = AcademicClass::factory()->create();
            $academicClass2 = AcademicClass::factory()->create();
            $academicClassIds = [$academicClass1->id, $academicClass2->id];

            // Act
            $result = $this->service->addAcademicClass($course, $academicClassIds);

            // Assert
            expect($result)->toBeTrue()
                ->and($course->academic_classes)->toHaveCount(2)
                ->and($course->academic_classes->contains($academicClass1))->toBeTrue()
                ->and($course->academic_classes->contains($academicClass2))->toBeTrue();
        });

        it('returns true when adding empty academic classes array', function () {
            // Arrange
            $course = Course::factory()->create();
            $emptyAcademicClassIds = [];

            // Act
            $result = $this->service->addAcademicClass($course, $emptyAcademicClassIds);

            // Assert
            expect($result)->toBeTrue()
                ->and($course->academic_classes)->toHaveCount(0);
        });

        it('runs in database transaction when adding academic classes', function () {
            // Arrange
            $course = Course::factory()->create();
            $academicClass = AcademicClass::factory()->create();
            $academicClassIds = [$academicClass->id];

            DB::shouldReceive('transaction')
                ->once()
                ->andReturnUsing(fn($callback) => $callback());

            // Act
            $result = $this->service->addAcademicClass($course, $academicClassIds);

            // Assert
            expect($result)->toBeTrue();
        });

        it('throws exception when adding academic class with invalid foreign key', function () {
            // Arrange
            $course = Course::factory()->create();
            $invalidAcademicClassIds = [999, 1000]; // IDs yang tidak ada

            // Act & Assert
            expect(fn() => $this->service->addAcademicClass($course, $invalidAcademicClassIds))
                ->toThrow(\Illuminate\Database\QueryException::class)
                ->and(fn() => $this->service->addAcademicClass($course, $invalidAcademicClassIds))
                ->toThrow(\Exception::class, 'SQLSTATE[23000]');
        });
    });

    describe('removeAcademicClass method', function () {
        it('can remove academic classes from course', function () {
            // Arrange
            $course = Course::factory()->create();
            $academicClass1 = AcademicClass::factory()->create();
            $academicClass2 = AcademicClass::factory()->create();
            $academicClass3 = AcademicClass::factory()->create();

            // Attach classes first
            $course->academic_classes()->attach([$academicClass1->id, $academicClass2->id, $academicClass3->id]);

            $academicClassIdsToRemove = [$academicClass1->id, $academicClass2->id];

            // Act
            $result = $this->service->removeAcademicClass($course, $academicClassIdsToRemove);

            // Assert
            expect($result)->toBeTrue()
                ->and($course->fresh()->academic_classes)->toHaveCount(1)
                ->and($course->academic_classes->contains($academicClass1))->toBeFalse()
                ->and($course->academic_classes->contains($academicClass2))->toBeFalse()
                ->and($course->academic_classes->contains($academicClass3))->toBeTrue();
        });

        it('returns true when removing empty academic classes array', function () {
            // Arrange
            $course = Course::factory()->create();
            $academicClass = AcademicClass::factory()->create();
            $course->academic_classes()->attach($academicClass->id);

            $emptyAcademicClassIds = [];

            // Act
            $result = $this->service->removeAcademicClass($course, $emptyAcademicClassIds);

            // Assert
            expect($result)->toBeTrue()
                ->and($course->fresh()->academic_classes)->toHaveCount(1);
        });

        it('runs in database transaction when removing academic classes', function () {
            // Arrange
            $course = Course::factory()->create();
            $academicClass = AcademicClass::factory()->create();
            $course->academic_classes()->attach($academicClass->id);

            $academicClassIds = [$academicClass->id];

            DB::shouldReceive('transaction')
                ->once()
                ->andReturnUsing(fn($callback) => $callback());

            // Act
            $result = $this->service->removeAcademicClass($course, $academicClassIds);

            // Assert
            expect($result)->toBeTrue();
        });

        it('handles non-existent academic classes when removing', function () {
            // Arrange
            $course = Course::factory()->create();
            $academicClass = AcademicClass::factory()->create();

            $nonExistentIds = [999, 1000]; // IDs that don't exist

            // Act
            $result = $this->service->removeAcademicClass($course, $nonExistentIds);

            // Assert
            expect($result)->toBeTrue()
                ->and($course->academic_classes)->toHaveCount(0);
        });
    });
});
