<?php

namespace App\Livewire\Feature\AcademicClass\Pages;

use App\Models\AcademicClass;
use App\Models\StudyProgram;
use App\Services\AcademicClassService;
use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;

class StudentListPage extends Component
{
    use WithAlertModal;

    protected StudyProgramService $spService;
    protected AcademicClassService $acService;
    // protected $studentId;

    public StudyProgram $studyProgram;
    public ?AcademicClass $academicClass = null;

    // Modal Variable
    public $showFormModal = false;

    protected $listeners = [
        'StudentAdded' => 'StudentAdded',
        'showAddModal' => 'showFormModal',
        'closeFormModal' => 'closeFormModal',
    ];

    public function boot(StudyProgramService $spService, AcademicClassService $acService)
    {
        $this->spService = $spService;
        $this->acService = $acService;
    }

    public function mount($spId, $classId)
    {
        $this->studyProgram = $this->spService->findById($spId, with: []);
        $this->academicClass = $this->acService->findById($classId);
    }

    // Modal Actions

    public function showFormModal()
    {
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
    }

    public function StudentAdded()
    {
        $this->closeFormModal();
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Mahasiswa berhasil ditambahkan.');
    }

    public function render()
    {
        return view('livewire.feature.academic-class.pages.student-list-page');
    }
}
