<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SantriController;
use App\Http\Controllers\RekapitulasiController;
use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\WaliAuthController;
use App\Http\Controllers\WaliDashboardController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Login / Register)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Data Santri (CRUD)
    Route::prefix('santri')->name('santri.')->group(function () {
        Route::get('/', [SantriController::class, 'index'])->name('index');
        Route::post('/', [SantriController::class, 'store'])->name('store');
        Route::put('/{santri}', [SantriController::class, 'update'])->name('update');
        Route::delete('/{santri}', [SantriController::class, 'destroy'])->name('destroy');
        Route::delete('/{santri}/clear-fingerprint', [SantriController::class, 'clearFingerprint'])->name('clear_fingerprint');
    });

    // Rekapitulasi Kehadiran
    Route::get('/rekapitulasi', [RekapitulasiController::class, 'index'])->name('rekapitulasi');
});

// API Routes (Excluded from CSRF in bootstrap/app.php)
Route::prefix('api')->group(function () {
    Route::post('/attendance/scan', [AttendanceApiController::class, 'scan']);
    Route::get('/attendance/stream', [AttendanceApiController::class, 'stream']);
    
    // Fingerprint Sync / Deletion endpoints
    Route::get('/fingerprint/pending-deletions', [AttendanceApiController::class, 'getPendingDeletions']);
    Route::post('/fingerprint/confirm-deletion', [AttendanceApiController::class, 'confirmDeletion']);
    
    // Fingerprint Enrollment endpoints
    Route::get('/fingerprint/request-enroll', [AttendanceApiController::class, 'requestEnroll']);
    Route::get('/fingerprint/pending-enroll', [AttendanceApiController::class, 'getPendingEnroll']);
    Route::post('/fingerprint/confirm-enroll', [AttendanceApiController::class, 'confirmEnroll']);
    Route::get('/fingerprint/cancel-enroll', [AttendanceApiController::class, 'cancelEnroll']);
    Route::get('/fingerprint/check-enroll-status', [AttendanceApiController::class, 'checkEnrollStatus']);
});

// Wali Portal Routes (Guest / Auth)
Route::prefix('wali')->group(function () {
    Route::middleware('guest:wali')->group(function () {
        Route::get('/login', [WaliAuthController::class, 'showLogin'])->name('wali.login');
        Route::post('/login', [WaliAuthController::class, 'login']);
        Route::get('/register', [WaliAuthController::class, 'showRegister'])->name('wali.register');
        Route::post('/register', [WaliAuthController::class, 'register']);
    });

    Route::middleware('auth:wali')->group(function () {
        Route::get('/dashboard', [WaliDashboardController::class, 'index'])->name('wali.dashboard');
        Route::get('/santri/{santri}', [WaliDashboardController::class, 'showSantri'])->name('wali.santri.show');
        Route::post('/logout', [WaliAuthController::class, 'logout'])->name('wali.logout');
    });
});
