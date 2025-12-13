<?php

namespace App\Livewire\Feature\Schedule\Lists;

use App\Services\ScheduleRequestService;
use App\Enums\ScheduleRequest\StatusEnum;
use Livewire\Component;
use Livewire\WithPagination;

class RequestList extends Component
{
    use WithPagination;
    protected ScheduleRequestService $srService;

    public $tab = 'masuk'; // masuk | disetujui | ditolak
    public $perPage = 10;

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
            ['user_id' => auth()->id(), 'sr_status' => $srStatus],
            'created_at',
            'desc',
            $this->perPage
        );
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
            $this->srService->update($sr, ['status' => StatusEnum::APPROVED->value ?? StatusEnum::APPROVED]);

            if (method_exists($this, 'dispatch')) {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Request disetujui.']);
            } else {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Request disetujui.']);
            }

            $this->dispatch('refresh-requests');
            $this->resetPage();
        } catch (\Throwable $e) {
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menyetujui request.']);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menyetujui request.']);
            }
            throw $e;
        }
    }

    public function reject(int $id)
    {
        try {
            $sr = $this->srService->findById($id);
            $this->srService->update($sr, ['status' => StatusEnum::REJECTED->value ?? StatusEnum::REJECTED]);

            if (method_exists($this, 'dispatch')) {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Request ditolak.']);
            } else {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Request ditolak.']);
            }

            $this->dispatch('refresh-requests');
            $this->resetPage();
        } catch (\Throwable $e) {
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menolak request.']);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menolak request.']);
            }
            throw $e;
        }
    }

    public function cancel(int $id)
    {
        try {
            $sr = $this->srService->findById($id);
            // set back to pending
            $this->srService->update($sr, ['status' => StatusEnum::PENDING->value ?? StatusEnum::PENDING]);

            if (method_exists($this, 'dispatch')) {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Status request dibatalkan (kembali ke pending).']);
            } else {
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Status request dibatalkan (kembali ke pending).']);
            }

            $this->dispatch('refresh-requests');
            $this->resetPage();
        } catch (\Throwable $e) {
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal membatalkan request.']);
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal membatalkan request.']);
            }
            throw $e;
        }
    }
}
