<?php

namespace App\Livewire\Feature\Dashboard;

use Livewire\Component;

use App\Models\AcademicClass;
use App\Models\Attendance;
use App\Models\Computer;
use App\Models\Course;
use App\Models\Department;
use App\Models\Lecturer;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LbrDashboard extends Component
{
    public $stats = [];
    public $recentActivities = [];
    public $topRoomsBySchedule = [];
    public $upcomingSchedules = [];
    public $todaySchedules = [];
    public $roomStatusSummary = [];
    public $computerStats = [];
    public $isLoading = true;

    public function mount()
    {
        $this->loadStatistics();
        $this->loadRecentActivities();
        $this->loadTopRooms();
        $this->loadUpcomingSchedules();
        $this->loadTodaySchedules();
        $this->loadRoomStatusSummary();
        $this->loadComputerStats();

        $this->isLoading = false;
    }

    private function loadStatistics()
    {
        // Count semua data penting
        $this->stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'total_lecturers' => Lecturer::count(),
            'total_courses' => Course::count(),
            'total_departments' => Department::count(),
            'total_study_programs' => StudyProgram::count(),
            'total_academic_classes' => AcademicClass::count(),
            'total_rooms' => Room::count(),
            'total_computers' => Computer::sum('computer_count'),
            'total_schedules' => Schedule::count(),
            'active_schedules_today' => Schedule::whereDate('start_date', now()->format('Y-m-d'))->count(),
        ];
    }

    private function loadRecentActivities()
    {
        // Ambil aktivitas terkini dari berbagai tabel
        $this->recentActivities = collect();

        // Recent schedules
        $recentSchedules = Schedule::with(['course', 'rooms'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($schedule) {
                return [
                    'type' => 'schedule',
                    'title' => 'Jadwal Baru',
                    'description' => "{$schedule->course->name} di " . ($schedule->rooms->pluck('name')->join(', ') ?: 'Ruangan belum ditentukan'),
                    'time' => $schedule->created_at->diffForHumans(),
                    'icon' => 'calendar-alt',
                    'color' => 'text-primary',
                ];
            });

        // Recent students
        $recentStudents = Student::with(['user', 'study_program'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($student) {
                return [
                    'type' => 'student',
                    'title' => 'Mahasiswa Baru',
                    'description' => "{$student->user->name} ({$student->nim}) - {$student->study_program->name}",
                    'time' => $student->created_at->diffForHumans(),
                    'icon' => 'user-graduate',
                    'color' => 'text-success',
                ];
            });

        // Recent rooms
        $recentRooms = Room::orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($room) {
                return [
                    'type' => 'room',
                    'title' => 'Ruangan Baru',
                    'description' => "{$room->name} ({$room->code})",
                    'time' => $room->created_at->diffForHumans(),
                    'icon' => 'door-closed',
                    'color' => 'text-warning',
                ];
            });

        $this->recentActivities = $recentSchedules
            ->concat($recentStudents)
            ->concat($recentRooms)
            ->sortByDesc(fn($item) => $item['time'])
            ->take(8);
    }

    private function loadTopRooms()
    {
        // Ruangan dengan jadwal terbanyak
        $this->topRoomsBySchedule = Room::withCount('schedules')
            ->orderBy('schedules_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($room) {
                return [
                    'name' => $room->name,
                    'code' => $room->code,
                    'schedule_count' => $room->schedules_count,
                    'status' => $room->status->value,
                ];
            });
    }

    private function loadUpcomingSchedules()
    {
        $this->upcomingSchedules = Schedule::with(['course', 'rooms'])
            ->whereDate('start_date', '>=', now()->format('Y-m-d'))
            ->whereDate('start_date', '<=', now()->addDays(7)->format('Y-m-d'))
            ->orderBy('start_date')
            ->orderBy('time')
            ->limit(6)
            ->get();
    }

    private function loadTodaySchedules()
    {
        $this->todaySchedules = Schedule::with(['course', 'rooms', 'attendances'])
            ->whereDate('start_date', now()->format('Y-m-d'))
            ->orderBy('time')
            ->get();
    }

    private function loadRoomStatusSummary()
    {
        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 1)->count(); // Asumsi status 1 = available
        $occupiedRooms = Room::where('status', 0)->count(); // Asumsi status 0 = occupied
        $maintenanceRooms = Room::where('status', 2)->count(); // Asumsi status 2 = maintenance

        $this->roomStatusSummary = [
            'total' => $totalRooms,
            'available' => $availableRooms,
            'occupied' => $occupiedRooms,
            'maintenance' => $maintenanceRooms,
            'available_percentage' => $totalRooms > 0 ? round(($availableRooms / $totalRooms) * 100, 2) : 0,
        ];
    }

    private function loadComputerStats()
    {
        $totalComputers = Computer::sum('computer_count');
        $totalRam = Computer::sum('ram_capacity');
        $totalStorage = Computer::sum('storage_capacity');
        $avgRam = $totalComputers > 0 ? round($totalRam / $totalComputers, 2) : 0;
        $avgStorage = $totalComputers > 0 ? round($totalStorage / $totalComputers, 2) : 0;

        // Komputer berdasarkan OS
        $osDistribution = Computer::select('os', DB::raw('count(*) as count'))
            ->groupBy('os')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->os->value => $item->count];
            })
            ->toArray();

        // Komputer berdasarkan kategori
        $categoryDistribution = Computer::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->category->value => $item->count];
            })
            ->toArray();

        $this->computerStats = [
            'total' => $totalComputers,
            'total_ram' => $totalRam,
            'total_storage' => $totalStorage,
            'avg_ram' => $avgRam,
            'avg_storage' => $avgStorage,
            'os_distribution' => $osDistribution,
            'category_distribution' => $categoryDistribution,
        ];
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

    public function getAttendancePercentage($schedule)
    {
        $totalAttendances = $schedule->attendances->count();
        $presentAttendances = $schedule->attendances->where('status.value', 1)->count();

        return $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 2) : 0;
    }

    public function refresh()
    {
        $this->isLoading = true;
        $this->loadStatistics();
        $this->loadRecentActivities();
        $this->loadTopRooms();
        $this->loadUpcomingSchedules();
        $this->loadTodaySchedules();
        $this->loadRoomStatusSummary();
        $this->loadComputerStats();
        $this->isLoading = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard berhasil diperbarui'
        ]);
    }

    public function render()
    {
        return view('livewire.feature.dashboard.lbr-dashboard', [
            'title' => 'Dashboard'
        ]);
    }
}
