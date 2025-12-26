<?php

namespace App\Livewire\Feature\Dashboard;

use App\Models\Schedule;
use App\Models\ScheduleRequest;
use App\Models\User;
use App\Services\ScheduleRequestService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LabHeadDashboard extends Component
{
    public $user;
    public $pendingRequests = [];
    public $approvedRequests = [];
    public $rejectedRequests = [];
    public $todaySchedules = [];
    public $upcomingSchedules = [];
    public $stats = [];
    public $isLoading = true;
    public $selectedRequest = null;
    public $showModal = false;
    public $approvalNote = '';

    protected $scheduleRequestService;

    public function boot(ScheduleRequestService $scheduleRequestService)
    {
        $this->scheduleRequestService = $scheduleRequestService;
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        // Load pending schedule requests
        $this->pendingRequests = ScheduleRequest::where('status', 0) // Assuming 0 = pending
            ->with(['user', 'lecturer.user', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Load approved requests (last 7 days)
        $this->approvedRequests = ScheduleRequest::where('status', 1) // Assuming 1 = approved
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->with(['user', 'lecturer.user', 'schedules'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Load rejected requests (last 7 days)
        $this->rejectedRequests = ScheduleRequest::where('status', 2) // Assuming 2 = rejected
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->with(['user', 'lecturer.user'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Load today's schedules
        $this->todaySchedules = Schedule::whereDate('start_date', now()->format('Y-m-d'))
            ->with(['course', 'rooms', 'schedule_request.user', 'schedule_request.lecturer.user'])
            ->orderBy('time')
            ->get();

        // Load upcoming schedules (next 3 days)
        $this->upcomingSchedules = Schedule::whereDate('start_date', '>=', now()->format('Y-m-d'))
            ->whereDate('start_date', '<=', now()->addDays(3)->format('Y-m-d'))
            ->with(['course', 'rooms', 'schedule_request.user'])
            ->orderBy('start_date')
            ->orderBy('time')
            ->limit(8)
            ->get();

        // Load statistics
        $this->loadStatistics();

        $this->isLoading = false;
    }

    private function loadStatistics()
    {
        $totalRequests = ScheduleRequest::count();
        $pendingRequests = ScheduleRequest::where('status', 0)->count();
        $approvedRequests = ScheduleRequest::where('status', 1)->count();
        $rejectedRequests = ScheduleRequest::where('status', 2)->count();

        $todaySchedulesCount = Schedule::whereDate('start_date', now()->format('Y-m-d'))->count();
        $tomorrowSchedulesCount = Schedule::whereDate('start_date', now()->addDay()->format('Y-m-d'))->count();

        $recentApprovedCount = ScheduleRequest::where('status', 1)
            ->whereDate('updated_at', '>=', now()->subDays(7))
            ->count();

        $this->stats = [
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'approved_requests' => $approvedRequests,
            'rejected_requests' => $rejectedRequests,
            'today_schedules' => $todaySchedulesCount,
            'tomorrow_schedules' => $tomorrowSchedulesCount,
            'recent_approved' => $recentApprovedCount,
            'approval_rate' => $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 2) : 0,
            'pending_rate' => $totalRequests > 0 ? round(($pendingRequests / $totalRequests) * 100, 2) : 0,
        ];
    }

    public function selectRequest($requestId)
    {
        $this->selectedRequest = ScheduleRequest::with([
            'user',
            'lecturer.user',
            'schedules.course',
            'schedules.rooms'
        ])->find($requestId);

        $this->approvalNote = '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedRequest = null;
        $this->approvalNote = '';
    }

    public function approveRequest()
    {
        $this->validate([
            'approvalNote' => 'nullable|string|max:500',
        ]);

        try {
            // Update schedule request status to approved
            $this->selectedRequest->update([
                'status' => 1, // Approved
                'information' => $this->selectedRequest->information . "\n\n[Disetujui oleh " . $this->user->name . " pada " . now()->format('d/m/Y H:i') . "]" .
                               ($this->approvalNote ? "\nCatatan: " . $this->approvalNote : '')
            ]);

            // Create schedules based on repeat_count
            if ($this->selectedRequest->repeat_count > 0) {
                $this->createSchedulesFromRequest($this->selectedRequest);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Permintaan jadwal berhasil disetujui'
            ]);

            $this->closeModal();
            $this->loadDashboardData();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal menyetujui permintaan: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectRequest()
    {
        $this->validate([
            'approvalNote' => 'required|string|max:500',
        ]);

        try {
            $this->selectedRequest->update([
                'status' => 2, // Rejected
                'information' => $this->selectedRequest->information . "\n\n[Ditolak oleh " . $this->user->name . " pada " . now()->format('d/m/Y H:i') . "]\nAlasan: " . $this->approvalNote
            ]);

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Permintaan jadwal telah ditolak'
            ]);

            $this->closeModal();
            $this->loadDashboardData();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal menolak permintaan: ' . $e->getMessage()
            ]);
        }
    }

    private function createSchedulesFromRequest($scheduleRequest)
    {
        // This method would create actual schedule records based on the request
        // You need to implement the logic based on your requirements
        // For example, creating weekly schedules for the number of repeats

        // Example implementation:
        /*
        $startDate = now()->addDays(1); // Start tomorrow
        $schedules = [];

        for ($i = 0; $i < $scheduleRequest->repeat_count; $i++) {
            $scheduleDate = $startDate->copy()->addWeeks($i);

            $schedule = Schedule::create([
                'sr_id' => $scheduleRequest->id,
                'course_id' => $scheduleRequest->course_id, // You might need to add this field
                'start_date' => $scheduleDate,
                'time' => $scheduleRequest->time, // You might need to add this field
                'status' => 1, // Active
                'is_open' => true,
                'information' => "Jadwal dari permintaan #{$scheduleRequest->id}"
            ]);

            $schedules[] = $schedule;
        }

        return $schedules;
        */
    }

    public function getRequestStatusBadge($status)
    {
        $statusMap = [
            0 => ['class' => 'badge-warning', 'text' => 'Menunggu', 'icon' => 'clock'],
            1 => ['class' => 'badge-success', 'text' => 'Disetujui', 'icon' => 'check-circle'],
            2 => ['class' => 'badge-error', 'text' => 'Ditolak', 'icon' => 'times-circle'],
            3 => ['class' => 'badge-info', 'text' => 'Diproses', 'icon' => 'sync'],
        ];

        return $statusMap[$status->value] ?? $statusMap[0];
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

    public function getCategoryBadge($category)
    {
        $categoryMap = [
            0 => ['class' => 'badge-info', 'text' => 'Reguler'],
            1 => ['class' => 'badge-primary', 'text' => 'Ujian'],
            2 => ['class' => 'badge-secondary', 'text' => 'Remedial'],
            3 => ['class' => 'badge-accent', 'text' => 'Tambahan'],
        ];

        return $categoryMap[$category->value] ?? $categoryMap[0];
    }

    public function refresh()
    {
        $this->isLoading = true;
        $this->loadDashboardData();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard berhasil diperbarui'
        ]);
    }

    public function render()
    {
        return view('livewire.feature.dashboard.lab-head-dashboard');
    }
}
