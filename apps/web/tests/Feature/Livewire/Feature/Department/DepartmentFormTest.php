<?php

use App\Livewire\Feature\Department\Forms\DepartmentForm;
use App\Models\Department;
use App\Services\DepartmentService;
use Livewire\Livewire;

it('renders department form component', function () {
    Livewire::test(DepartmentForm::class)
        ->assertStatus(200);
});

it('validates required fields on save', function () {
    Livewire::test(DepartmentForm::class)
        ->set('code', '')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['code', 'name']);
});

it('can create a department through service', function () {
    $service = mock(DepartmentService::class, function ($mock) {
        $mock->shouldReceive('create')
            ->once()
            ->with([
                'code' => 'IF',
                'name' => 'Informatika',
            ]);
    });

    \Illuminate\Support\Facades\App::instance(DepartmentService::class, $service);

    Livewire::test(DepartmentForm::class)
        ->set('code', 'IF')
        ->set('name', 'Informatika')
        ->call('save')
        ->assertHasNoErrors();
});

it('can update a department through service', function () {
    $department = Department::factory()->create([
        'code' => 'OLD',
        'name' => 'Old Name',
    ]);

    $service = mock(DepartmentService::class, function ($mock) use ($department) {
        $mock->shouldReceive('findById')
            ->once()
            ->with($department->id)
            ->andReturn($department);

        $mock->shouldReceive('update')
            ->once()
            ->with($department, [
                'code' => 'NEW',
                'name' => 'New Name',
            ]);
    });

    \Illuminate\Support\Facades\App::instance(DepartmentService::class, $service);

    Livewire::test(DepartmentForm::class, [
        'editingId' => $department->id,
        'formData' => [
            'code' => $department->code,
            'name' => $department->name,
        ],
    ])
        ->set('code', 'NEW')
        ->set('name', 'New Name')
        ->call('save')
        ->assertHasNoErrors();
});


