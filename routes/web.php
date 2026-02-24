<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::prefix('admin')
        ->middleware('role:SUPER_ADMIN|ADMIN|BENDAHARA|MANAGER|PETUGAS')
        ->group(function () {
            Route::view('/home', 'admin.home')->name('admin.home');
        });
});

require __DIR__ . '/auth.php';
