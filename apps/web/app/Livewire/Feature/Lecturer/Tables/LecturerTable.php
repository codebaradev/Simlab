<?php

namespace App\Livewire\Feature\Lecturer\Tables;

use App\Services\LecturerService;
use Livewire\Component;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\WithPagination;

class LecturerTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected LecturerService $leService;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'generation'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
        'bulkDelete' => 'bulkDelete',
    ];

    public function boot(LecturerService $leService)
    {
        $this->leService = $leService;
    }

    public function mount()
    {
        $this->sortField = 'nip';
        $this->sortDirection = 'asc';
    }

    protected function getDefaultSortField(): string
    {
        return 'nip';
    }

    public function getItemsForBulkSelection()
    {
        return $this->lecturers;
    }

    public function editLecturer($lecterurId)
    {
        $this->redirectRoute('lecturer.edit', ['lecturerId' => $lecterurId], navigate: true);
    }

    public function deleteLecturer($lecterurId)
    {
        try {
            $lecturer = $this->leService->findById($lecterurId);
            $this->leService->delete($lecturer);

            $this->showSuccessAlert('Data dosen berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus dosen: ' . $e->getMessage());
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
            $this->leService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data dosen terpilih berhasil dihapus.');
            $this->dispatch('lecturerDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus dosen terpilih: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        $this->bulkDelete();
    }

    public function getLecturersProperty()
    {
        return $this->leService->getAll(
            ['user', 'study_program'],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage
        );
    }

    public function render()
    {
        return view('livewire.feature.lecturer.tables.lecturer-table', [
            'lecturers' => $this->lecturers,
        ]);
    }
}
