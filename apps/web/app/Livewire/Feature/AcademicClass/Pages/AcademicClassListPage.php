<?php

namespace App\Livewire\Feature\AcademicClass\Pages;

use App\Models\StudyProgram;
use App\Services\StudyProgramService;
use Livewire\Component;

class AcademicClassListPage extends Component
{
    protected StudyProgramService $spService;

    public StudyProgram $studyProgram;

    public function boot(StudyProgramService $spService)
    {
        $this->spService = $spService;
    }

    public function mount(int $spId)
    {
        $this->studyProgram = $this->spService->findById($spId);
    }

    public function render()
    {
        return view('livewire.feature.academic-class.pages.academic-class-list-page');
    }
}
