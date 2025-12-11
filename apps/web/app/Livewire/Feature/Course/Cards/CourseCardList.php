<?php

namespace App\Livewire\Feature\Course\Cards;

use App\Services\CourseService;
use App\Traits\Livewire\WithTableFeatures;
use App\Traits\Livewire\WithAlertModal;
use App\Traits\Livewire\WithBulkActions;
use App\Traits\Livewire\WithFilters;
use App\Traits\Livewire\WithSorting;
use Livewire\Component;
use Livewire\WithPagination;


class CourseCardList extends Component
{
    use WithPagination, WithFilters, WithBulkActions, WithSorting, WithAlertModal, WithTableFeatures;

    protected CourseService $cService;
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

    public function boot(CourseService $cService)
    {
        $this->cService = $cService;
    }

    public function mount()
    {
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    public function getItemsForBulkSelection()
    {
        return $this->courses;
    }

    public function edit($id)
    {
        $this->redirectRoute('course.edit', ['courseId' => $id], navigate: true);
    }

    public function delete($id)
    {
        try {
            $course = $this->cService->findById($id);
            $this->cService->delete($course);

            $this->showSuccessAlert('Data matakuliah berhasil dihapus.');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus matakuliah');
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
            $this->cService->bulkDelete($this->selected);

            $this->clearSelection();

            $this->showSuccessAlert('Data matakuliah terpilih berhasil dihapus.');
            $this->dispatch('courseDeleted');
        } catch (\Exception $e) {
            $this->showErrorAlert('Gagal menghapus matakuliah terpilih');
        }
    }

    public function deleteSelected()
    {
        $this->bulkDelete();
    }

    public function getCoursesProperty()
    {
        return $this->cService->getAll(
            ['lecturers', 'academic_classes'],
            $this->getFilters(),
            $this->sortField,
            $this->sortDirection,
            $this->perPage,
        );
    }

    public function render()
    {
        return view('livewire.feature.course.cards.course-card-list', [
            'courses' => $this->courses
        ]);
    }
}
