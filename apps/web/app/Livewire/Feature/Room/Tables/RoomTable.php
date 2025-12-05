<?php

namespace App\Livewire\Feature\Room\Tables;

use App\Services\RoomService;
use Livewire\Component;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\WithPagination;

class RoomTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected RoomService $rService;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'code'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
        'bulkDelete' => 'bulkDelete',
    ];

    public function boot(RoomService $rService)
    {
        $this->rService = $rService;
    }

    public function mount()
    {
        $this->sortField = 'code';
        $this->sortDirection = 'asc';
    }

    protected function getDefaultSortField(): string
    {
        return 'code';
    }

    public function getItemsForBulkSelection()
    {
        return $this->rooms;
    }

    public function addRoom()
    {
        $this->redirectRoute('room.add',  navigate: true);
    }

    public function editRoom($roomId)
    {
        $this->redirectRoute('room.edit', ['roomId' => $roomId], navigate: true);
    }

    public function deleteRoom($roomId)
    {
        try {
            $room = $this->rService->findById($roomId);
            $this->rService->delete($room);

            $this->showSuccessAlert('Data ruangan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus ruangan: ');
        }
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
            $this->rService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data ruangan terpilih berhasil dihapus.');
            $this->dispatch('roomDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus ruangan terpilih: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        $this->bulkDelete();
    }

    public function getRoomsProperty()
    {
        return $this->rService->getAll(
            [],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage
        );
    }

    public function render()
    {
        return view('livewire.feature.room.tables.room-table', [
            'rooms' => $this->rooms
        ]);
    }
}
