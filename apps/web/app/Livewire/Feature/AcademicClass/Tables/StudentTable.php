<?php

namespace App\Livewire\Feature\AcademicClass\Tables;

use App\Enums\User\UserGenderEnum;
use App\Models\AcademicClass;
use App\Models\StudyProgram;
use App\Services\AcademicClassService;
use App\Services\StudentService;
use App\Services\StudyProgramService;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use App\Traits\Livewire\WithTableFeatures;
use Filament\Navigation\NavigationManager;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class StudentTable extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected AcademicClassService $acService;
    protected StudentService $stService;
    protected StudyProgramService $spService;
    public ?AcademicClass $academicClass;

    #[Locked]
    public $classId = null;

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

    public function boot(AcademicClassService $acService, StudentService $stService, StudyProgramService $spService)
    {
        $this->acService = $acService;
        $this->stService = $stService;
        $this->spService = $spService;
    }

    public function mount($academicClass)
    {
        $this->sortField = 'generation';
        $this->sortDirection = 'desc';
        $this->academicClass = $academicClass;

        $this->classId = $this->academicClass->id;
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

    public function deleteStudent($studentId)
    {
        try {
            $student = $this->stService->findById($studentId);
            $this->stService->delete($student);

            $this->showSuccessAlert('Data mahasiswa berhasil dihapus dikelas ini.');
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
            $this->acService->bulkDeleteStudent($this->academicClass, $this->selected);

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
            $this->perPage,
            classId: $this->classId
        );
    }

    public function render()
    {
        return view('livewire.feature.academic-class.tables.student-table', [
            'students' => $this->students,
        ]);
    }
}
