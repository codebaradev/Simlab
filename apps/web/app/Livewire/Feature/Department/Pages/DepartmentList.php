<?php

namespace App\Livewire\Feature\Department\Pages;

use App\Services\DepartmentService;
use App\Traits\Livewire\WithAlertModal;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentList extends Component
{
    use WithAlertModal;

    protected DepartmentService $dpService;
    public $showFormModal = false;
    public $editingDepartment = null;
    public $formData = [];

    protected $listeners = [
        'departmentSaved' => 'handleDepartmentSaved',
        'departmentDeleted' => 'handleDepartmentDeleted',
        'showEditForm' => 'showEditForm',
        'showCreateForm' => 'showCreateForm'
    ];

    public function boot(DepartmentService $dpService)
    {
        $this->dpService = $dpService;
    }

    public function mount()
    {
        $this->formData = $this->emptyForm();
    }

    public function showCreateForm()
    {
        $this->editingDepartment = null;
        $this->formData = $this->emptyForm();
        $this->showFormModal = true;
    }

    public function showEditForm($departmentId)
    {
        $this->editingDepartment = $departmentId;
        $department = $this->dpService->findById($departmentId);

        $this->formData = [
            'code' => $department->code,
            'name' => $department->name,
        ];
        $this->showFormModal = true;
    }

    public function closeFormModal()
    {
        $this->showFormModal = false;
        $this->editingDepartment = null;
        $this->formData = $this->emptyForm();
    }

    private function emptyForm()
    {
        return [
            'code' => '',
            'name' => '',
        ];
    }

    public function handleDepartmentSaved()
    {
        $this->closeFormModal();
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data jurusan berhasil disimpan.');
    }

    public function handleDepartmentDeleted()
    {
        $this->dispatch('refresh-table');
        $this->showSuccessAlert('Data jurusan berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.feature.department.pages.department-list');
    }
}
