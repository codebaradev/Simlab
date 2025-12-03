<?php

use App\Livewire\Feature\Department\Pages\DepartmentList;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Pagination\LengthAwarePaginator;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders department list page', function () {
    $user = \App\Models\User::factory()->create();

    actingAs($user);

    get(route('department.index') ?? '/departments')
        ->assertStatus(200);
});

it('can open create department form modal', function () {
    $component = \Livewire\Livewire::test(DepartmentList::class)
        ->call('showCreateForm')
        ->assertSet('editingDepartment', null)
        ->assertSet('showFormModal', true);
});

it('can open edit department form modal with data', function () {
    $department = Department::factory()->create([
        'code' => 'TEST001',
        'name' => 'Test Department',
    ]);

    // Bind mock ke container sebelum Livewire::test
    $service = mock(DepartmentService::class, function ($mock) use ($department) {
        $paginator = new LengthAwarePaginator(
            collect([$department]),
            1, // total
            10, // per page
            1  // current page
        );

        $mock->shouldReceive('getAll')
            ->zeroOrMoreTimes()
            ->andReturn($paginator);

        $mock->shouldReceive('findById')
            ->once()
            ->with($department->id)
            ->andReturn($department);
    });

    app()->instance(DepartmentService::class, $service);

    \Livewire\Livewire::test(DepartmentList::class)
        ->call('showEditForm', $department->id)
        ->assertSet('editingDepartment', $department->id)
        ->assertSet('showFormModal', true)
        ->assertSet('formData.code', $department->code)
        ->assertSet('formData.name', $department->name);
});


