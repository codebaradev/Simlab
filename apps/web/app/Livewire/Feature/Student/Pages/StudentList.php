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

    public function boot(StudentService $stService)
    {
        $this->stService = $stService;
    }

    public function render()
    {
        return view('livewire.feature.student.pages.student-list');
    }
}
