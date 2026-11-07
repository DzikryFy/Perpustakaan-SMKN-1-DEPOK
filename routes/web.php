<?php

use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SuggestionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberLoanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Perpustakaan Digital SMKN 1 Depok
|--------------------------------------------------------------------------
*/

// Public
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/koleksi', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/koleksi/{book}', [CatalogController::class, 'show'])->name('catalog.show');

// Auth Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout']);

    // Member Circulation & Interaction
    Route::get('/pinjaman-saya', [MemberLoanController::class, 'myLoans'])->name('member.loans');
    Route::post('/koleksi/{book}/request', [MemberLoanController::class, 'requestBorrow'])->name('member.borrow.request');
    Route::delete('/request/{borrowRequest}', [MemberLoanController::class, 'cancelRequest'])->name('member.borrow.cancel');
    Route::get('/saran-buku', [MemberLoanController::class, 'mySuggestions'])->name('member.suggestions');
    Route::post('/saran-buku', [MemberLoanController::class, 'storeSuggestion'])->name('member.suggestions.store');

    // Admin & Staff Routes (Pustakawan & Kepala Perpustakaan)
    Route::prefix('admin')->name('admin.')->middleware('role:pustakawan,kepala_perpustakaan')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Circulation & Loans
        Route::get('/peminjaman', [LoanController::class, 'index'])->name('loans.index');
        Route::post('/peminjaman/direct', [LoanController::class, 'createDirectLoan'])->name('loans.direct');
        Route::post('/request/{borrowRequest}/setujui', [LoanController::class, 'approveRequest'])->name('requests.approve');
        Route::post('/request/{borrowRequest}/tolak', [LoanController::class, 'rejectRequest'])->name('requests.reject');
        Route::post('/peminjaman/{loan}/kembali', [LoanController::class, 'processReturn'])->name('loans.return');

        // Book Suggestions Review
        Route::get('/saran', [SuggestionController::class, 'index'])->name('suggestions.index');
        Route::put('/saran/{bookSuggestion}', [SuggestionController::class, 'updateStatus'])->name('suggestions.update');

        // Reports
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/cetak', [ReportController::class, 'print'])->name('reports.print');

        // Catalog Management
        Route::resource('books', BookController::class);

        // User Management (Pustakawan only)
        Route::resource('users', UserController::class)->middleware('role:pustakawan');
    });
});
