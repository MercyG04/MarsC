<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;

//Route::get('/', function () {
    //return view('welcome');
//});

Route::get('/', fn () => redirect()->route('clients.index'));
Route::resource('clients', ClientController::class);

Route::prefix('clients/{client}')->group(function () {
    Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('vehicles',       [VehicleController::class, 'store'])->name('vehicles.store');
});

// Standalone: view a single vehicle by id
Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
