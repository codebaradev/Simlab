<?php

namespace App\Livewire\Feature\Lecturer\Pages;

use App\Models\Lecturer;
use App\Services\LecturerService;
use Livewire\Component;

class LecturerFormPage extends Component
{
    protected LecturerService $stService;
    // protected $studentId;

    public ?Lecturer $lecturer = null;

    public function boot(LecturerService $stService)
    {
        $this->stService = $stService;
    }

    public function mount($lecturerId = null)
    {
        if ($lecturerId) {
            $this->lecturer = $this->stService->findById($lecturerId, with: ['user']);
        }
    }

    public function render()
    {
        return view('livewire.feature.lecturer.pages.lecturer-form-page');
    }
}
