<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdentityCandidateController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/fontes', [SourceController::class, 'index'])->name('sources.index');
Route::get('/orgaos', [OrganizationController::class, 'index'])->name('organizations.index');
Route::get('/orgao/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');

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
