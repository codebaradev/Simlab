<?php

use App\Livewire\Feature\StudyProgram\Pages\StudyProgramList;
use App\Models\Department;
use App\Models\StudyProgram;
use App\Services\StudyProgramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('can initialize study program list component', function () {
    Livewire::test(StudyProgramList::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.feature.study-program.pages.study-program-list');
});

describe('form modal operations', function () {
    beforeEach(function () {
        $this->department = Department::factory()->create();
        $this->studyProgram = StudyProgram::factory()->create(['department_id' => $this->department->id]);
        $this->mockService = mock(StudyProgramService::class);
    });

    it('shows empty form when creating new study program', function () {
        Livewire::test(StudyProgramList::class)
            ->assertSet('showFormModal', false)
            ->assertSet('editingStudyProgram', null)
            ->assertSet('formData.code', '')
            ->assertSet('formData.name', '')
            ->assertSet('formData.department_id', null)
            ->call('showCreateForm')
            ->assertSet('showFormModal', true)
            ->assertSet('editingStudyProgram', null)
            ->assertSet('formData.code', '')
            ->assertSet('formData.name', '')
            ->assertSet('formData.department_id', null)
            ->assertDispatched('showFormModal');
    });

    it('shows form with data when editing study program', function () {
        $this->mockService->shouldReceive('findById')
            ->with($this->studyProgram->id)
            ->once()
            ->andReturn($this->studyProgram);

        Livewire::test(StudyProgramList::class)
            ->call('showEditForm', 1)
            ->assertSet('showFormModal', true)
            ->assertSet('editingStudyProgram', 1)
            ->assertSet('formData.code', 'CS')
            ->assertSet('formData.name', 'Computer Science')
            ->assertSet('formData.department_id', 10)
            ->assertDispatched('showFormModal');
    });

    it('closes form modal and resets data', function () {
        Livewire::test(StudyProgramList::class)
            ->set('showFormModal', true)
            ->set('editingStudyProgram', 1)
            ->set('formData', [
                'code' => 'CS',
                'name' => 'Computer Science',
                'department_id' => 10,
            ])
            ->call('closeFormModal')
            ->assertSet('showFormModal', false)
            ->assertSet('editingStudyProgram', null)
            ->assertSet('formData.code', '')
            ->assertSet('formData.name', '')
            ->assertSet('formData.department_id', null)
            ->assertDispatched('closeFormModal');
    });
});

describe('event handlers', function () {
    it('handles study program saved event', function () {
        Livewire::test(StudyProgramList::class)
            ->set('showFormModal', true)
            ->set('editingStudyProgram', 1)
            ->call('handleStudyProgramSaved')
            ->assertSet('showFormModal', false)
            ->assertSet('editingStudyProgram', null)
            ->assertSet('formData.code', '')
            ->assertSet('formData.name', '')
            ->assertSet('formData.department_id', null)
            ->assertDispatched('closeFormModal')
            ->assertDispatched('refresh-table')
            ->assertSet('successMessage', 'Data program studi berhasil disimpan.')
            ->assertSet('showSuccessAlert', true);
    });

    it('handles study program deleted event', function () {
        Livewire::test(StudyProgramList::class)
            ->call('handleStudyProgramDeleted')
            ->assertDispatched('refresh-table')
            ->assertSet('successMessage', 'Data program studi berhasil dihapus.')
            ->assertSet('showSuccessAlert', true);
    });
});

describe('listeners', function () {
    it('responds to showEditForm event', function () {
        $studyProgram = (object) [
            'id' => 1,
            'code' => 'CS',
            'name' => 'Computer Science',
            'department_id' => 10,
        ];

        $mockService = mock(StudyProgramService::class);
        $mockService->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($studyProgram);

        Livewire::test(StudyProgramList::class)
            ->emit('showEditForm', 1)
            ->assertSet('editingStudyProgram', 1)
            ->assertSet('showFormModal', true);
    });

    it('responds to showCreateForm event', function () {
        Livewire::test(StudyProgramList::class)
            ->emit('showCreateForm')
            ->assertSet('editingStudyProgram', null)
            ->assertSet('showFormModal', true);
    });

    it('responds to closeFormModal event', function () {
        Livewire::test(StudyProgramList::class)
            ->set('showFormModal', true)
            ->set('editingStudyProgram', 1)
            ->emit('closeFormModal')
            ->assertSet('showFormModal', false)
            ->assertSet('editingStudyProgram', null);
    });

    it('responds to studyProgramSaved event', function () {
        Livewire::test(StudyProgramList::class)
            ->emit('studyProgramSaved')
            ->assertSet('showFormModal', false)
            ->assertSet('editingStudyProgram', null);
    });

    it('responds to studyProgramDeleted event', function () {
        Livewire::test(StudyProgramList::class)
            ->emit('studyProgramDeleted')
            ->assertDispatched('refresh-table');
    });
});

describe('mount method', function () {
    it('initializes with empty form data', function () {
        Livewire::test(StudyProgramList::class)
            ->assertSet('formData', [
                'code' => '',
                'name' => '',
                'department_id' => null,
            ]);
    });
});

describe('boot method', function () {
    it('injects StudyProgramService dependency', function () {
        // Since the service is injected via Laravel's container,
        // we just need to ensure the component boots without errors
        Livewire::test(StudyProgramList::class)
            ->assertStatus(200);
    });
});
