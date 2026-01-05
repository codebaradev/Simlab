<?php

namespace App\Livewire\Feature\Student\Pages;

use App\Models\Student;
use App\Services\StudentService;
use Livewire\Component;

class StudentFormPage extends Component
{
    protected StudentService $stService;
    // protected $studentId;

    public ?Student $student = null;

    public function boot(StudentService $stService)
    {
        $this->stService = $stService;
    }

    public function mount($studentId = null)
    {
        if ($studentId) {
            $this->student = $this->stService->findById($studentId, with: ['user']);
        }
    }

    public function render()
    {
        return view('livewire.feature.student.pages.student-form-page');
    }
}
