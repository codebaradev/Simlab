<?php

namespace App\Livewire\Feature\Department\Tables;

use App\Services\DepartmentService;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentTable extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedStatus = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $selected = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'refresh-table' => '$refresh',
    ];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->departments->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = false;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedStatus = '';
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function editDepartment($departmentId)
    {
        $this->dispatch('showEditForm', departmentId: $departmentId);
    }

    public function deleteDepartment($departmentId)
    {
        $departmentService = app(DepartmentService::class);

        try {
            $department = \App\Models\Department::find($departmentId);
            $departmentService->delete($department);

            $this->dispatch('showAlertModal', [
                'title' => 'Berhasil!',
                'message' => 'Data jurusan berhasil dihapus.',
                'type' => 'success',
                'actionText' => 'Tutup'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal menghapus jurusan: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        $departmentService = app(DepartmentService::class);

        try {
            $departmentService->bulkDelete($this->selected);

            $this->selected = [];
            $this->selectAll = false;

            $this->dispatch('departmentDeleted');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal menghapus jurusan terpilih: ' . $e->getMessage()
            ]);
        }
    }

    public function getDepartmentsProperty()
    {
        $departmentService = app(DepartmentService::class);

        $filters = [
            'search' => $this->search,
            'status' => $this->selectedStatus,
        ];

        return $departmentService->getAll(
            $filters,
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
