<?php

namespace App\Livewire\Feature\Schedule\Lists;

use App\Services\ScheduleRequestService;
use App\Enums\ScheduleRequest\StatusEnum;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;
use Livewire\WithPagination;

class RequestList extends Component
{
    use WithPagination, WithAlertModal;
    protected ScheduleRequestService $srService;

    public $tab = 'masuk'; // masuk | disetujui | ditolak
    public $perPage = 10;

    public $showSchedulesModal = false;
    public $selectedRequestId = null;
    public $selectedSchedules = [];

    protected $queryString = [
        'tab' => ['except' => 'masuk'],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['refresh-requests' => '$refresh'];

    public function boot(ScheduleRequestService $srService)
    {
        $this->srService = $srService;
    }

    public function updatingTab()
    {
        $this->resetPage();
    }

    public function getStatusFilter(): string
    {
        return $this->tab;
    }

    public function getRequestsProperty()
    {
        $statusMap = [
            'masuk' => 'pending',
            'disetujui' => 'approved',
            'ditolak' => 'rejected',
        ];

        $srStatus = $statusMap[$this->tab] ?? null;

        return $this->srService->getAll(
            ['sr_status' => $srStatus],
            'created_at',
            'desc',
            $this->perPage
        );
    }

    public function showSchedules(int $requestId)
    {
        $sr = $this->srService->findById($requestId);

        $this->selectedRequestId = $requestId;
        $this->selectedSchedules = $sr->schedules()
            ->with(['course', 'rooms'])
            ->orderBy('start_date')
            ->get();

        $this->showSchedulesModal = true;
    }

    public function closeSchedulesModal()
    {
        $this->reset([
            'showSchedulesModal',
            'selectedRequestId',
            'selectedSchedules',
        ]);
    }

    public function render()
    {
        return view('livewire.feature.schedule.lists.request-list', [
            'requests' => $this->requests,
        ]);
    }

    public function approve(int $id)
    {
        try {
            $sr = $this->srService->findById($id);
            $sr = $this->srService->update($sr, ['status' => StatusEnum::APPROVED->value ?? StatusEnum::APPROVED]);

            $sr->schedules()->update(['status' => StatusEnum::APPROVED]);

            $this->showSuccessAlert('Request jadwal berhasil disetujui');
            $this->resetPage();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function reject(int $id)
    {
        try {
            $sr = $this->srService->findById($id);
            $sr = $this->srService->update($sr, ['status' => StatusEnum::REJECTED->value ?? StatusEnum::REJECTED]);

            $sr->schedules()->update(['status' => StatusEnum::REJECTED]);

            $this->showSuccessAlert('Request jadwal berhasil ditolak');
            $this->resetPage();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function cancel(int $id)
    {
        try {
            $sr = $this->srService->findById($id);
            $sr = $this->srService->update($sr, ['status' => StatusEnum::PENDING->value ?? StatusEnum::PENDING]);

            $sr->schedules()->update(['status' => StatusEnum::PENDING]);

            $this->showSuccessAlert('Request berhasil dibatalkan');
            $this->resetPage();
        } catch (\Throwable $e) {

            throw $e;
        }
    }
}
