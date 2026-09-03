<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GasChartController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MonthlyController;
use App\Http\Controllers\Admin\OtifController;
use App\Http\Controllers\Admin\SlideController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SlideshowController;
use Illuminate\Support\Facades\Route;

// -------- Public kiosk slideshow --------
Route::get('/', [SlideshowController::class, 'index'])->name('slideshow');

// -------- Auth --------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// -------- Admin --------
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Static slides + generated-slide display settings
    Route::get('/slides', [SlideController::class, 'index'])->name('slides.index');
    Route::get('/slides/create', [SlideController::class, 'create'])->name('slides.create');
    Route::post('/slides', [SlideController::class, 'store'])->name('slides.store');
    Route::post('/slides/reorder', [SlideController::class, 'reorder'])->name('slides.reorder');
    Route::get('/slides/{slide}/edit', [SlideController::class, 'edit'])->name('slides.edit');
    Route::put('/slides/{slide}', [SlideController::class, 'update'])->name('slides.update');
    Route::patch('/slides/{slide}/toggle', [SlideController::class, 'toggle'])->name('slides.toggle');
    Route::delete('/slides/{slide}', [SlideController::class, 'destroy'])->name('slides.destroy');

    // Monthly generated slides: data + backgrounds
    Route::get('/monthly', [MonthlyController::class, 'index'])->name('monthly.index');

    Route::post('/monthly/birthdays/import', [MonthlyController::class, 'importBirthdays'])->name('monthly.birthdays.import');
    Route::post('/monthly/birthdays', [MonthlyController::class, 'storeBirthday'])->name('monthly.birthdays.store');
    Route::put('/monthly/birthdays/{birthday}', [MonthlyController::class, 'updateBirthday'])->name('monthly.birthdays.update');
    Route::delete('/monthly/birthdays/{birthday}', [MonthlyController::class, 'destroyBirthday'])->name('monthly.birthdays.destroy');

    Route::post('/monthly/anniversaries/import', [MonthlyController::class, 'importAnniversaries'])->name('monthly.anniversaries.import');
    Route::post('/monthly/anniversaries', [MonthlyController::class, 'storeAnniversary'])->name('monthly.anniversaries.store');
    Route::put('/monthly/anniversaries/{anniversary}', [MonthlyController::class, 'updateAnniversary'])->name('monthly.anniversaries.update');
    Route::delete('/monthly/anniversaries/{anniversary}', [MonthlyController::class, 'destroyAnniversary'])->name('monthly.anniversaries.destroy');

    Route::put('/monthly/backgrounds/{month}/{kind}', [MonthlyController::class, 'updateBackground'])->name('monthly.backgrounds.update');

    // Gas card goal charts
    Route::get('/gas', [GasChartController::class, 'index'])->name('gas.index');
    Route::post('/gas', [GasChartController::class, 'store'])->name('gas.store');
    Route::put('/gas/{gasChart}', [GasChartController::class, 'update'])->name('gas.update');
    Route::delete('/gas/{gasChart}', [GasChartController::class, 'destroy'])->name('gas.destroy');

    // OTIF / order backlog
    Route::get('/otif', [OtifController::class, 'index'])->name('otif.index');
    Route::post('/otif/refresh', [OtifController::class, 'refresh'])->name('otif.refresh');

    // Display zones / locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

    // Admin users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
