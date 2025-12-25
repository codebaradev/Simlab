<?php

namespace App\Livewire\Feature\Dashboard;

use Livewire\Component;

use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Support\Facades\Auth;

class StudentDashboard extends Component
{
    public $student;
    public $todaySchedules = [];
    public $isLoading = true;

    protected $scheduleService;

    public function boot(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function mount()
    {
        $this->loadStudentData();
        $this->loadTodaySchedules();
    }

    public function loadStudentData()
    {
        $user = Auth::user();
        $this->student = $user->student()
            ->with([
                'study_program.department',
                'academic_classes'
            ])
            ->first();

        if (!$this->student) {
            abort(403, 'Anda bukan mahasiswa');
        }
    }

    public function loadTodaySchedules()
    {
        $today = now()->format('Y-m-d');

        $this->todaySchedules = Schedule::whereDate('start_date', $today)
            ->where(function($query) {
                // Get schedules where student is enrolled through academic classes
                $query->whereHas('course.academic_classes', function($q) {
                    $q->whereHas('students', function($subQ) {
                        $subQ->where('id', $this->student->id);
                    });
                })
                ->orWhereHas('attendances', function($q) {
                    $q->where('user_id', Auth::id());
                });
            })
            ->with([
                'course',
                'rooms',
                'attendance_monitoring',
                'attendances' => function($query) {
                    $query->where('user_id', Auth::id());
                }
            ])
            ->orderBy('time')
            ->get();

        $this->isLoading = false;
    }

    public function getFormattedTime($timeEnum)
    {
        $timeMap = [
            1 => '08:00 - 09:40',
            2 => '10:00 - 11:40',
            3 => '13:00 - 14:40',
            4 => '15:00 - 16:40',
            5 => '18:30 - 20:10',
            6 => '20:20 - 22:00',
        ];

        return $timeMap[$timeEnum->value] ?? 'Waktu tidak diketahui';
    }

    public function refresh()
    {
        $this->isLoading = true;
        $this->loadTodaySchedules();
    }

    public function render()
    {
        return view('livewire.feature.dashboard.student-dashboard');
    }
}
