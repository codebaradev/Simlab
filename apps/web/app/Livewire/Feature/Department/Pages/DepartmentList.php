<?php

namespace App\Livewire\Feature\Department\Pages;

use App\Services\DepartmentService;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentList extends Component
{
    public $showFormModal = false;
    public $editingDepartment = null;
    public $formData = [];

    protected $listeners = [
        'departmentSaved' => 'handleDepartmentSaved',
        'departmentDeleted' => 'handleDepartmentDeleted',
        'showEditForm' => 'showEditForm',
        'showCreateForm' => 'showCreateForm'
    ];

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
        $departmentService = app(DepartmentService::class);
        $department = \App\Models\Department::find($departmentId);

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
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Data jurusan berhasil disimpan.'
        ]);
    }

    public function handleDepartmentDeleted()
    {
        $this->dispatch('refresh-table');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Data jurusan berhasil dihapus.'
        ]);
    }

    public function render()
    {
        return view('livewire.feature.department.pages.department-list');
    }
}
