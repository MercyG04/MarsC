<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ValuationsController;
use App\Http\Controllers\AddOnController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\PolicyCancellationController;
use App\Http\Controllers\PolicyEndorsementController;
use App\Http\Controllers\VehicleDriverController;



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

        Route::get('drivers/create', [VehicleDriverController::class, 'create'])->name('vehicles.drivers.create');
        Route::post('drivers', [VehicleDriverController::class, 'store'])->name('vehicles.drivers.store');

        // Show
        Route::get('drivers/{driver}', [VehicleDriverController::class, 'show'])->name('vehicles.drivers.show');

        // Update — only is_primary
        Route::put('drivers/{driver}', [VehicleDriverController::class, 'update'])->name('vehicles.drivers.update');
        Route::delete('drivers/{driver}', [VehicleDriverController::class, 'destroy'])->name('vehicles.drivers.destroy');
   

    Route::get('valuations',              [ValuationsController::class, 'index'])->name('valuations.index');
    Route::get('vehicles/{vehicle}/valuations/create', [ValuationsController::class, 'create'])->name('valuations.create');
    Route::post('vehicles/{vehicle}/valuations',        [ValuationsController::class, 'store'])->name('valuations.store');
    Route::get('valuations/{valuation}',  [ValuationsController::class, 'show'])->name('valuations.show');


    Route::resource('add-ons', AddOnController::class);
    //Route::middleware(['auth', 'role:admin'])->group(function () {
    //Route::resource('add-ons', AddOnController::class)->except(['show']);
//});
    // Cancellation workflow
    Route::get('policies/{policy}/cancel',  [PolicyCancellationController::class, 'create'])->name('policies.cancel');
    Route::post('policies/{policy}/cancel', [PolicyCancellationController::class, 'store'])->name('policies.cancel.store');
    Route::post('policies/{policy}/certificate-surrendered', [PolicyCancellationController::class, 'markCertificateSurrendered'])->name('policies.certificate.surrendered');

    Route::get('policies', [PolicyController::class, 'index'])->name('policies.index');
    Route::get('vehicles/{vehicle}/policies/create', [PolicyController::class, 'create'])->name('policies.create');
    Route::post('vehicles/{vehicle}/policies', [PolicyController::class, 'store'])->name('policies.store');
    Route::get('policies/{policy}', [PolicyController::class, 'show'])->name('policies.show');

    Route::get('policies/{policy}/endorse',  [PolicyEndorsementController::class, 'create'])->name('policies.endorse');
    Route::post('policies/{policy}/endorse', [PolicyEndorsementController::class, 'store'])->name('policies.endorse.store');
    Route::get('policies/{policy}/endorsements/{endorsement}',   [PolicyEndorsementController::class, 'show']
)->name('policies.endorsements.show');
});

require __DIR__.'/auth.php';
