<?php

namespace App\Livewire\Feature\AcademicClass\Tables;

use App\Models\Room;
use App\Models\StudyProgram;
use App\Services\AcademicClassService;
use App\Services\StudyProgramService;
use Livewire\Component;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Livewire\WithPagination;

class AcademicClassTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected StudyProgramService $rService;
    protected AcademicClassService $acService;

    public StudyProgram $studyProgram;

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

    public function boot(StudyProgramService $rService, AcademicClassService $acService)
    {
        $this->rService = $rService;
        $this->acService = $acService;
    }

    public function mount($studyProgram)
    {
        $this->studyProgram = $studyProgram;
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    protected function getDefaultSortField(): string
    {
        return 'name';
    }

    public function getItemsForBulkSelection()
    {
        return $this->academicClasses;
    }

    public function add()
    {
        $this->redirectRoute('study-program.class.add',  navigate: true);
    }

    public function edit($acId)
    {
        $this->redirectRoute('study-program.class.edit', ['spId' => $this->studyProgram->id, 'classId' => $acId], navigate: true);
    }

    public function delete($acId)
    {
        try {
            $computer = $this->acService->findById($acId);
            $this->acService->delete($computer);

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
            $this->acService->bulkDelete($this->selected);

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

    public function getAcademicClassesProperty()
    {
        return $this->acService->getAll(
            [],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage,
            spId: $this->studyProgram->id
        );
    }

    public function render()
    {
        return view('livewire.feature.academic-class.tables.academic-class-table', [
            'academicClasses' => $this->academicClasses
        ]);
    }
}
