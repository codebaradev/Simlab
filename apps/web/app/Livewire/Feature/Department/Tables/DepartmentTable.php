<?php

namespace App\Livewire\Feature\Department\Tables;

use App\Services\DepartmentService;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected DepartmentService $dpService;

    // public $selectedStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
    ];

    public function boot(DepartmentService $dpService)
    {
        $this->dpService = $dpService;
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

    public function getItemsForBulkSelection()
    {
        return $this->departments;
    }

    public function editDepartment($departmentId)
    {
        $this->dispatch('showEditForm', departmentId: $departmentId);
    }

    public function deleteDepartment($departmentId)
    {
        try {
            $department = $this->dpService->findById($departmentId);
            $this->dpService->delete($department);

            $this->showSuccessAlert('Data jurusan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus jurusan: ' . $e->getMessage());
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
        dd("testig");

        try {
            $this->dpService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data jurusan terpilih berhasil dihapus.');
            $this->dispatch('departmentDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus jurusan terpilih: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        $this->showConfirmAlert(
            message: 'Apakah Anda yakin ingin menghapus data jurusan terpilih?',
            title: 'Konfirmasi Hapus',
            actionText: 'Ya, Hapus',
            cancelText: 'Batal',
            actionMethod: 'bulkDelete',
        );
    }

    public function getDepartmentsProperty()
    {

        return $this->dpService->getAll(
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage
        );
    }

    public function render()
    {
        return view('livewire.feature.department.tables.department-table', [
            'departments' => $this->departments,
        ]);
    }
}
