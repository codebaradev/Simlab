<?php

namespace App\Livewire\Feature\StudyProgram\Pages;

use App\Models\StudyProgram;
use App\Services\StudyProgramService;
use Livewire\Component;

class StudyProgramFormPage extends Component
{
    protected StudyProgramService $spService;
    // protected $studentId;

    public ?StudyProgram $studyProgram = null;

    public function boot(StudyProgramService $spService)
    {
        $this->spService = $spService;
    }

    public function mount($spId = null)
    {
        if ($spId) {
            $this->studyProgram = $this->spService->findById($spId, with: []);
        }
    }

    public function render()
    {
        return view('livewire.feature.study-program.pages.study-program-form-page');
    }
}
