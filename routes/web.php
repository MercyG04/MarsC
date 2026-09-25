<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ValuationsController;
use App\Http\Controllers\AddOnController;


Route::get('/', function () {
    return view('clients.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::resource('clients', ClientController::class);


    Route::prefix('clients/{client}')->group(function () {
        Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('vehicles',       [VehicleController::class, 'store'])->name('vehicles.store');
    });
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    Route::get('valuations',              [ValuationsController::class, 'index'])->name('valuations.index');
    Route::get('vehicles/{vehicle}/valuations/create', [ValuationsController::class, 'create'])->name('valuations.create');
    Route::post('vehicles/{vehicle}/valuations',        [ValuationsController::class, 'store'])->name('valuations.store');
    Route::get('valuations/{valuation}',  [ValuationsController::class, 'show'])->name('valuations.show');


    Route::resource('add-ons', AddOnController::class);
});

require __DIR__.'/auth.php';
