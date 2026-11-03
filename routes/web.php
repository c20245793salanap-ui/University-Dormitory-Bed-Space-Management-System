<?php

use App\Http\Controllers\BedSlotController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DormBuildingController;
use App\Http\Controllers\DormContractController;
use App\Http\Controllers\DormitoryController;
use App\Http\Controllers\DormRoomController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', [DormitoryController::class, 'dashboard'])->name('dormitory.dashboard');
    Route::resource('students', StudentController::class)
        ->except(['show'])
        ->names('dormitory.students');
    Route::resource('buildings', DormBuildingController::class)
        ->except(['show'])
        ->parameters(['buildings' => 'building'])
        ->names('dormitory.buildings');
    Route::resource('rooms', DormRoomController::class)
        ->except(['show'])
        ->parameters(['rooms' => 'room'])
        ->names('dormitory.rooms');
    Route::resource('bed-slots', BedSlotController::class)
        ->except(['show'])
        ->parameters(['bed-slots' => 'bedSlot'])
        ->names('dormitory.bed-slots');
    Route::resource('contracts', DormContractController::class)
        ->except(['show'])
        ->parameters(['contracts' => 'contract'])
        ->names('dormitory.contracts');
});
