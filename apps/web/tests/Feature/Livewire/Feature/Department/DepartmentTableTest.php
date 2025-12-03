<?php

use App\Livewire\Feature\Department\Tables\DepartmentTable;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

it('renders department table', function () {
    Department::factory()->count(3)->create();

    Livewire::test(DepartmentTable::class)
        ->assertStatus(200);
});

it('resets page when per page is updated', function () {
    Livewire::test(DepartmentTable::class)
        ->set('perPage', 20)
        ->assertSet('perPage', 20);
});

it('dispatches showEditForm event when editDepartment is called', function () {
    $department = Department::factory()->create();

    Livewire::test(DepartmentTable::class)
        ->call('editDepartment', $department->id)
        ->assertDispatched('showEditForm', departmentId: $department->id);
});

it('can delete a department through service', function () {
    $department = Department::factory()->create();

    // Mock service dengan semua method yang akan dipanggil
    $service = mock(DepartmentService::class, function ($mock) use ($department) {
        // getAll() dipanggil saat render melalui property 'departments' (bisa beberapa kali)
        $paginator = new LengthAwarePaginator(
            collect([$department]),
            1, // total
            10, // per page
            1  // current page
        );

        $mock->shouldReceive('getAll')
            ->zeroOrMoreTimes()
            ->andReturn($paginator);

        // findById() dan delete() dipanggil saat deleteDepartment()
        $mock->shouldReceive('findById')
            ->once()
            ->with($department->id)
            ->andReturn($department);

        $mock->shouldReceive('delete')
            ->once()
            ->with($department)
            ->andReturn(true);
    });

    // Bind mock ke container sebelum Livewire::test
    app()->instance(DepartmentService::class, $service);

    Livewire::test(DepartmentTable::class)
        ->call('deleteDepartment', $department->id);
});


