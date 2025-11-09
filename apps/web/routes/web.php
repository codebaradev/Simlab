<?php

use App\Http\Middleware\GuestOnlyMiddleware;
use App\Http\Middleware\LbrOnlyMiddleware;
use App\Http\Middleware\UserOnlyMiddleware;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Index as Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', Login::class)->middleware([GuestOnlyMiddleware::class])->name('login');

Route::middleware([UserOnlyMiddleware::class])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
