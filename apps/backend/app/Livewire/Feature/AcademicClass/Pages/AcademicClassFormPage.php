<?php

namespace App\Livewire\Feature\AcademicClass\Pages;

use App\Models\AcademicClass;
use App\Models\StudyProgram;
use App\Services\AcademicClassService;
use App\Services\StudyProgramService;
use Livewire\Component;

class AcademicClassFormPage extends Component
{
    protected StudyProgramService $spService;
    protected AcademicClassService $acService;
    // protected $studentId;

    public StudyProgram $studyProgram;
    public ?AcademicClass $academicClass = null;

    public function boot(StudyProgramService $spService, AcademicClassService $acService)
    {
        $this->spService = $spService;
        $this->acService = $acService;
    }

    public function mount($spId, $classId = null)
    {
        $this->studyProgram = $this->spService->findById($spId, with: []);

        if ($classId) {
            $this->academicClass = $this->acService->findById($classId);
        }
    }

    public function render()
    {
        return view('livewire.feature.academic-class.pages.academic-class-form-page');
    }
}
