<?php

namespace App\Livewire\Feature\AcademicClass\Tables;

use App\Services\AcademicClassService;
use App\Services\StudentService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\WithPagination;
class AddStudentTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected AcademicClassService $acService;
    protected StudentService $stService;

    #[Locked]
    public $spId;

    #[Locked]
    public $acId;

    protected $listeners = [
        'openAddStudentModal' => 'openModal',
        'closeAddStudentModal' => 'closeModal',
    ];

    public function boot(AcademicClassService $acService, StudentService $stService)
    {
        $this->acService = $acService;
        $this->stService = $stService;
    }

    public function mount($spId, $acId)
    {
        $this->spId = $spId;
        $this->acId = $acId;

        $this->sortField = 'nim';
        $this->sortDirection = 'asc';
    }

    // Main Actions

    public function addSelected()
    {
        $ac = $this->acService->findById($this->acId);

        if (empty($this->selected)) {
            return;
        }

        try {
            $this->acService->bulkAddStudent($ac, $this->selected);

            $this->clearSelection();
            $this->dispatch('StudentAdded');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menambahkan mahasiswa terpilih');
        }
    }

    public function getItemsForBulkSelection()
    {
        return $this->students;
    }

    public function getStudentsProperty()
    {
        return $this->stService->getAll(
            ['user'],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage,
            classId: $this->acId,
            isExcludeSameClass: true
        );
    }

    public function render()
    {
        return view('livewire.feature.academic-class.tables.add-student-table', [
            'students' => $this->students
        ]);
    }
}
