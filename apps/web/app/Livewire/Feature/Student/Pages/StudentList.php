<?php

namespace App\Livewire\Feature\Student\Pages;

use App\Enums\User\UserGenderEnum;
use App\Services\StudentService;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;

class StudentList extends Component
{
    use WithAlertModal;

    protected StudentService $stService;
    public $showFormModal = false;
    public $editingDepartment = null;
    public $formData = [];

    protected $listeners = [
        'studyProgramSaved' => 'handleStudyProgramSaved',
        'studyProgramDeleted' => 'handleStudyProgramDeleted',
        'showEditForm' => 'showEditForm',
        'showCreateForm' => 'showCreateForm',
        'closeFormModal' => 'closeFormModal'
    ];

    public function boot(StudentService $stService)
    {
        $this->stService = $stService;
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

    public function showEditForm($departmentId)
    {
        $this->editingDepartment = $departmentId;
        $department = $this->stService->findById($departmentId);

        $this->formData = [
            'code' => $department->code,
            'name' => $department->name,
        ];
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
        $this->editingDepartment = null;
        $this->formData = $this->emptyForm();
    }

    private function emptyForm()
    {
        return [
            'name' => '',
            'username' => '',
            'password' => '',
            'generation' => '',
        ];
    }

    public function handleStudyProgramSaved()
    {
        $this->closeFormModal();
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data jurusan berhasil disimpan.');
    }

    public function handleStudyProgramDeleted()
    {
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data jurusan berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.feature.student.pages.student-list');
    }
}
