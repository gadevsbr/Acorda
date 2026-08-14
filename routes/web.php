<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdentityCandidateController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProcurementPublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/fontes', [SourceController::class, 'index'])->name('sources.index');
Route::get('/orgaos', [OrganizationController::class, 'index'])->name('organizations.index');
Route::get('/orgao/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
Route::get('/pessoas', [PersonController::class, 'index'])->name('people.index');
Route::get('/pessoa/{person}', [PersonController::class, 'show'])->name('people.show');
Route::get('/contratos', [ProcurementPublicController::class, 'contracts'])->name('contracts.index');
Route::get('/contrato/{contract}', [ProcurementPublicController::class, 'contract'])->name('contracts.show');
Route::get('/licitacoes', [ProcurementPublicController::class, 'procurements'])->name('procurements.index');
Route::get('/licitacao/{procurement}', [ProcurementPublicController::class, 'procurement'])->name('procurements.show');
Route::get('/fornecedores', [ProcurementPublicController::class, 'suppliers'])->name('suppliers.index');
Route::get('/fornecedor/{supplier}', [ProcurementPublicController::class, 'supplier'])->name('suppliers.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin/candidatos-identidade', [IdentityCandidateController::class, 'index'])->middleware('verified')->name('identity-candidates.index');
    Route::patch('/admin/candidatos-identidade/{identityCandidate}', [IdentityCandidateController::class, 'update'])->middleware('verified')->name('identity-candidates.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
