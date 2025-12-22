<?php

namespace App\Livewire\Feature\Dashboard;

use Livewire\Component;

class StudentDashboard extends Component
{
    public $activeTab = 'dashboard';
    public $selectedCourse = null;

    // Data untuk dashboard
    public $totalCredits = 18;
    public $totalCourses = 5;
    public $gpa = 3.45;

    public function mount()
    {
        // Cek jika ada parameter tab di URL
        if (request()->has('tab')) {
            $this->activeTab = request()->get('tab');
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->selectedCourse = null; // Reset selected course saat ganti tab
    }

    public function selectCourse($courseId)
    {
        $this->selectedCourse = $courseId;
    }

    public function backToCourses()
    {
        $this->selectedCourse = null;
    }

    public function render()
    {
        return view('livewire.feature.dashboard.student-dashboard');
    }
}
