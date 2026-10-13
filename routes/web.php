<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ValuationController;
use App\Http\Controllers\AddOnController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ClaimDocumentControler;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\PolicyCancellationController;
use App\Http\Controllers\PolicyEndorsementController;
use App\Http\Controllers\VehicleDriverController;



Route::get('/', function () {
    
    return redirect()->route('clients.index');
});

//Route::get('/dashboard', function () {
    //return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //clients
    Route::resource('clients', ClientController::class);

    //vehicles
    Route::prefix('clients/{client}')->group(function () {
        Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('vehicles',       [VehicleController::class, 'store'])->name('vehicles.store');
    });
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    //vehicle-driver
    Route::prefix('vehicles/{vehicle}')->group(function () {
    Route::get('drivers/create',      [VehicleDriverController::class, 'create'])->name('vehicles.drivers.create');
    Route::post('drivers',            [VehicleDriverController::class, 'store'])->name('vehicles.drivers.store');
    Route::get('drivers/{driver}',    [VehicleDriverController::class, 'show'])->name('vehicles.drivers.show');
    Route::put('drivers/{driver}',    [VehicleDriverController::class, 'update'])->name('vehicles.drivers.update');
    Route::delete('drivers/{driver}', [VehicleDriverController::class, 'destroy'])->name('vehicles.drivers.destroy');
    });

    //policy routes accessible to all users 
    Route::get('policies', [PolicyController::class, 'index'])->name('policies.index');
    Route::get('vehicles/{vehicle}/policies/create', [PolicyController::class, 'create'])->name('policies.create');
    Route::post('vehicles/{vehicle}/policies', [PolicyController::class, 'store'])->name('policies.store');
    Route::get('policies/{policy}', [PolicyController::class, 'show'])->name('policies.show');

    //admin and agent routes to handle quotations

    Route::middleware('role:agent,admin')->group(function () {
    Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
    Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    
    // Status transitions for quotations 
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'markSent'])->name('quotations.mark-sent');
    Route::post('quotations/{quotation}/decline', [QuotationController::class, 'markDeclined'])->name('quotations.mark-declined');
    });


    //Add on routes handled stricyly by admin

    Route::middleware('role:admin')->group(function () {
        Route::resource('add-ons', AddOnController::class)->except(['show']);
        // Cancellation workflow handled strictly by admin
    Route::get('policies/{policy}/cancel',  [PolicyCancellationController::class, 'create'])->name('policies.cancel');
    Route::post('policies/{policy}/cancel', [PolicyCancellationController::class, 'store'])->name('policies.cancel.store');
    Route::post('policies/{policy}/certificate-surrendered', [PolicyCancellationController::class, 'markCertificateSurrendered'])->name('policies.certificate.surrendered');

    
    Route::get('policies/{policy}/endorse',  [PolicyEndorsementController::class, 'create'])->name('policies.endorse');
    Route::post('policies/{policy}/endorse', [PolicyEndorsementController::class, 'store'])->name('policies.endorse.store');
    Route::get('policies/{policy}/endorsements/{endorsement}',   [PolicyEndorsementController::class, 'show']
    )->name('policies.endorsements.show');

    });

    //claims routes handled by claims officer,underwriter and admin
    Route::middleware('role:claims_officer,underwriter,admin')->group(function () {
    Route::get('claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('policies/{policy}/claims/create', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('policies/{policy}/claims',       [ClaimController::class, 'store'])->name('claims.store');
    Route::get('claims/{claim}',            [ClaimController::class, 'show'])->name('claims.show');
    Route::post('claims/{claim}/acknowledge',  [ClaimController::class, 'acknowledge'])->name('claims.acknowledge');
    Route::post('claims/{claim}/begin-review',  [ClaimController::class, 'beginReview'])->name('claims.begin-review');
    Route::post('claims/{claim}/record-assessment',[ClaimController::class, 'recordAssessment'])->name('claims.record-assessment');

    //document uploads
    Route::post('claims/{claim}/documents',                     [ClaimDocumentController::class, 'store'])->name('claims.documents.store');
    Route::delete('claims/{claim}/documents/{document}',        [ClaimDocumentController::class, 'destroy'])->name('claims.documents.destroy');
    Route::get('claims/{claim}/documents/{document}/download',  [ClaimDocumentController::class, 'download'])->name('claims.documents.download');
    });

    Route::middleware('role:underwriter,admin')->group(function () {
    Route::post('claims/{claim}/approve',    [ClaimController::class, 'approve'])->name('claims.approve');

    Route::post('claims/{claim}/reject',    [ClaimController::class, 'reject'])->name('claims.reject');

    Route::post('claims/{claim}/mark-paid',    [ClaimController::class, 'markPaid'])->name('claims.mark-paid');

    //valuations accessed by underwriters and admins
    Route::get('valuations',  [ValuationController::class, 'index'])->name('valuations.index');
    Route::get('vehicles/{vehicle}/valuations/create', [ValuationController::class, 'create'])->name('valuations.create');
    Route::post('vehicles/{vehicle}/valuations',        [ValuationController::class, 'store'])->name('valuations.store');
    Route::get('valuations/{valuation}',  [ValuationController::class, 'show'])->name('valuations.show');
    });

    

    
});

require __DIR__.'/auth.php';
