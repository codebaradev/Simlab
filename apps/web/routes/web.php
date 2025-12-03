<?php

use App\Http\Middleware\GuestOnlyMiddleware;
use App\Http\Middleware\LbrOnlyMiddleware;
use App\Http\Middleware\UserOnlyMiddleware;
use App\Livewire\Feature\Auth\Login;
use App\Livewire\Feature\Dashboard\Index as Dashboard;
use App\Livewire\Feature\Department\Pages\DepartmentList;
use App\Livewire\Feature\Student\Pages\StudentList;
use App\Livewire\Feature\StudyProgram\Pages\StudyProgramList;
use Illuminate\Support\Facades\Route;

Route::get('/', Login::class)->middleware([GuestOnlyMiddleware::class])->name('login');

Route::middleware([UserOnlyMiddleware::class])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/jurusan', DepartmentList::class)->name('department.index');
    Route::get('/prodi', StudyProgramList::class)->name('study-program.index');
});
