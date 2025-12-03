<?php

namespace App\Livewire\Feature\StudyProgram\Tables;

use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\Component;
use Livewire\WithPagination;

class StudyProgramTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected StudyProgramService $spService;

    public $selectedDepartment = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedDepartment' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
        'bulkDelete' => 'bulkDelete'
    ];

    public function boot(StudyProgramService $spService)
    {
        $this->spService = $spService;
    }

    /**
     * Override default sort field
     */
    protected function getDefaultSortField(): string
    {
        return 'name';
    }

    /**
     * Override default sort direction
     */
    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    /**
     * Override getFilters to include department filter
     */
    public function getFilters(): array
    {
        $filters = [];

        // Get search filter from trait
        if (!empty($this->search)) {
            $filters['search'] = $this->search;
        }

        // Add department filter
        if (!empty($this->selectedDepartment)) {
            $filters['department_id'] = $this->selectedDepartment;
        }

        return $filters;
    }

    /**
     * Override clearFilters to clear department filter
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->selectedDepartment = '';
        $this->resetPage();
    }

    /**
     * Override getFilterQueryString to include department filter
     */
    public function getFilterQueryString(): array
    {
        return [
            'search' => ['except' => ''],
            'selectedDepartment' => ['except' => ''],
        ];
    }

    public function getItemsForBulkSelection()
    {
        return $this->studyPrograms;
    }

    public function getDepartmentsProperty()
    {
        return \App\Models\Department::orderBy('name')->get();
    }

    public function editStudyProgram($studyProgramId)
    {
        $this->dispatch('showEditForm', studyProgramId: $studyProgramId);
    }

    public function deleteStudyProgram($studyProgramId)
    {
        try {
            $studyProgram = $this->spService->findById($studyProgramId);
            $this->spService->delete($studyProgram);

            $this->showSuccessAlert('Data program studi berhasil dihapus.');
            $this->dispatch('studyProgramDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus program studi: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStudyProgram($studyProgramId)
    {
        $this->showConfirmAlert(
            message: 'Apakah Anda yakin ingin menghapus data program studi terpilih?',
            title: 'Konfirmasi Hapus',
            actionText: 'Ya, Hapus',
            cancelText: 'Batal',
            actionMethod: 'deleteStudyProgram(' . $studyProgramId . ')',
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
            $this->spService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data program studi terpilih berhasil dihapus.');
            $this->dispatch('studyProgramDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus program studi terpilih: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        $this->showConfirmAlert(
            message: 'Apakah Anda yakin ingin menghapus data program studi terpilih?',
            title: 'Konfirmasi Hapus',
            actionText: 'Ya, Hapus',
            cancelText: 'Batal',
            actionMethod: 'bulkDelete',
        );
    }

    public function getStudyProgramsProperty()
    {
        return $this->spService->getAll(
            ['department'],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage
        );
    }

    public function getActiveFilterCountProperty()
    {
        $count = 0;
        if (!empty($this->selectedDepartment)) {
            $count++;
        }
        return $count;
    }

    public function render()
    {
        return view('livewire.feature.study-program.tables.study-program-table', [
            'studyPrograms' => $this->studyPrograms,
            'departments' => $this->departments,
            'activeFilterCount' => $this->activeFilterCount,
        ]);
    }
}

