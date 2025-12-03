<?php

namespace App\Livewire\Feature\StudyProgram\Pages;

use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;
use Livewire\WithPagination;

class StudyProgramList extends Component
{
    use WithAlertModal;

    protected StudyProgramService $spService;
    public $showFormModal = false;
    public $editingStudyProgram = null;
    public $formData = [];

    protected $listeners = [
        'studyProgramSaved' => 'handleStudyProgramSaved',
        'studyProgramDeleted' => 'handleStudyProgramDeleted',
        'showEditForm' => 'showEditForm',
        'showCreateForm' => 'showCreateForm',
        'closeFormModal' => 'closeFormModal'
    ];

    public function boot(StudyProgramService $spService)
    {
        $this->spService = $spService;
    }

    public function mount()
    {
        $this->formData = $this->emptyForm();
    }

    public function showCreateForm()
    {
        $this->editingDepartment = null;
        $this->formData = $this->emptyForm();
        $this->showFormModal = true;
    }

    public function showEditForm($studyProgramId)
    {
        $this->editingStudyProgram = $studyProgramId;
        $studyProgram = $this->spService->findById($studyProgramId);

        $this->formData = [
            'code' => $studyProgram->code,
            'name' => $studyProgram->name,
            'department_id' => $studyProgram->department_id,
        ];
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
        $this->editingStudyProgram = null;
        $this->formData = $this->emptyForm();
    }

    private function emptyForm()
    {
        return [
            'code' => '',
            'name' => '',
            'department_id' => null,
        ];
    }

    public function handleStudyProgramSaved()
    {
        $this->closeFormModal();
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data program studi berhasil disimpan.');
    }

    public function handleStudyProgramDeleted()
    {
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data program studi berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.feature.study-program.pages.study-program-list');
    }
}

