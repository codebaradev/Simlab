<?php

namespace App\Livewire\Feature\Application\Tables;

use App\Models\Room;
use App\Services\ApplicationService;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\Component;
use Livewire\WithPagination;

class ApplicationTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected ApplicationService $appService;

    public Room $room;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
        'bulkDelete' => 'bulkDelete'
    ];

    public function boot(ApplicationService $appService)
    {
        $this->appService = $appService;
    }

    public function mount($room)
    {
        $this->room = $room;
    }

    public function getItemsForBulkSelection()
    {
        return $this->apps;
    }

    public function editApp($appId)
    {
        $this->dispatch('showEditForm', appId: $appId);
    }

    public function deleteApp($appId)
    {
        try {
            $app = $this->appService->findById($appId);
            $this->appService->delete($app);

            $this->showSuccessAlert('Data aplikasi berhasil dihapus.');
            $this->dispatch('appDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus aplikasi: ' . $e->getMessage());
        }
    }

    public function confirmDeleteApp($appId)
    {
        $this->showConfirmAlert(
            message: 'Apakah Anda yakin ingin menghapus data aplikasi terpilih?',
            title: 'Konfirmasi Hapus',
            actionText: 'Ya, Hapus',
            cancelText: 'Batal',
            actionMethod: 'deleteApp(' . $appId . ')',
        );
    }

    /**
     * Override bulkDelete from WithBulkActions trait
     */
    public function bulkDelete()
    {
        if (empty($this->selected)) {
            return;
        }

        try {
            $this->appService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data aplikasi terpilih berhasil dihapus.');
            $this->dispatch('appDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus aplikasi terpilih: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        $this->bulkDelete();
    }

    public function getAppsProperty()
    {
        return $this->appService->getAll(
            $this->room->id,
            [],
            sortField: $this->sortField,
            sortDirection: $this->sortDirection,
            perPage: $this->perPage
        );
    }

    public function render()
    {
        return view('livewire.feature.application.tables.application-table', [
            'apps' => $this->apps
        ]);
    }
}
