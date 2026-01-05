<?php

namespace App\Livewire\Feature\Computer\Tables;

use App\Models\Room;
use App\Services\ComputerService;
use App\Services\RoomService;
use Livewire\Component;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\WithPagination;

class ComputerTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected RoomService $rService;
    protected ComputerService $cpService;

    public Room $room;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
        'bulkDelete' => 'bulkDelete',
    ];

    public function boot(RoomService $rService, ComputerService $cpService)
    {
        $this->rService = $rService;
        $this->cpService = $cpService;
    }

    public function mount($room)
    {
        $this->room = $room;
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    protected function getDefaultSortField(): string
    {
        return 'name';
    }

    public function getItemsForBulkSelection()
    {
        return $this->computers;
    }

    public function addComputer()
    {
        $this->redirectRoute('room.computer.add',  navigate: true);
    }

    public function editComputer($computerId)
    {
        $this->redirectRoute('room.computer.edit', ['roomId' => $this->room->id, 'computerId' => $computerId], navigate: true);
    }

    public function deleteComputer($computerId)
    {
        try {
            $computer = $this->cpService->findById($computerId);
            $this->cpService->delete($computer);

            $this->showSuccessAlert('Data komputer berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus komputer: ');
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
            $this->cpService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data komputer terpilih berhasil dihapus.');
            $this->dispatch('computerDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus komputer terpilih: ');
        }
    }

    public function deleteSelected()
    {
        $this->bulkDelete();
    }

    public function getComputersProperty()
    {
        return $this->cpService->getAll(
            $this->room->id,
            [],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage
        );
    }

    public function render()
    {
        return view('livewire.feature.computer.tables.computer-table', [
            'computers' => $this->computers
        ]);
    }
}
