<?php

namespace App\Livewire\Feature\Student\Tables;

use App\Enums\User\UserGenderEnum;
use App\Models\StudyProgram;
use App\Services\StudentService;
use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Filament\Navigation\NavigationManager;
use Livewire\Component;
use Livewire\WithPagination;


class StudentTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected StudentService $stService;
    protected StudyProgramService $spService;

    // public $selectedStatus = '';

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

    public function boot(StudentService $stService, StudyProgramService $spService)
    {
        $this->stService = $stService;
        $this->spService = $spService;
    }

    public function mount()
    {
        $this->sortField = 'generation';
        $this->sortDirection = 'desc';
    }

    protected function getDefaultSortField(): string
    {
        return 'generation';
    }

    protected function getDefaultSortDirection(): string
    {
        return 'desc';
    }

    public function getItemsForBulkSelection()
    {
        return $this->students;
    }

    public function editStudent($studentId)
    {
        $this->redirectRoute('student.edit', ['studentId' => $studentId], navigate: true);
    }

    public function deleteStudent($studentId)
    {
        try {
            $student = $this->stService->findById($studentId);
            $this->stService->delete($student);

            $this->showSuccessAlert('Data mahasiswa berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus mahasiswa: ' . $e->getMessage());
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
            $this->stService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data mahasiswa terpilih berhasil dihapus.');
            $this->dispatch('StudentDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus mahasiswa terpilih: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        $this->bulkDelete();
    }

    public function getStudentsProperty()
    {
        return $this->stService->getAll(
            ['user', 'study_program'],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage
        );
    }

    public function render()
    {
        $studyPrograms = $this->spService->getAll(isPaginated: false);
        return view('livewire.feature.student.tables.student-table', [
            'students' => $this->students,
            'studyPrograms' => $studyPrograms
        ]);
    }
}
