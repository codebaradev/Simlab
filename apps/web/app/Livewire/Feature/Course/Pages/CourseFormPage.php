<?php

namespace App\Livewire\Feature\Course\Pages;

use App\Models\Course;
use App\Services\CourseService;
use Livewire\Component;

class CourseFormPage extends Component
{
    protected CourseService $stService;
    // protected $courseId;

    public ?Course $course = null;

    public function boot(CourseService $stService)
    {
        $this->stService = $stService;
    }

    public function mount($courseId = null)
    {
        if ($courseId) {
            $this->course = $this->stService->findById($courseId, with: []);
        }
    }

    public function render()
    {
        return view('livewire.feature.course.pages.course-form-page');
    }
}
