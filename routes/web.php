<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentReportController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\HospitalSettingsController;
use App\Http\Controllers\SopPortalController;
use App\Http\Controllers\SuperAdminAiController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Public / Guest Routes
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/quick-switch', [AuthController::class, 'quickSwitch'])->name('auth.quick-switch');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Executive Analytics Center & Live Laboratory Market Chart
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics/lab-tests-chart', [DashboardController::class, 'labTestChartData'])->name('analytics.lab-tests.chart');

    // Department Daily Reports & Data Entry
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [DepartmentReportController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentReportController::class, 'create'])->name('create');
        Route::post('/', [DepartmentReportController::class, 'store'])->name('store');
        Route::post('/laboratory/parse-lms', [DepartmentReportController::class, 'parseLmsReport'])->name('lab.parse-lms');
        Route::get('/{report}', [DepartmentReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [DepartmentReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [DepartmentReportController::class, 'update'])->name('update');
        Route::post('/{report}/approve', [DepartmentReportController::class, 'approve'])->name('approve');
        Route::post('/{report}/return', [DepartmentReportController::class, 'returnForRevision'])->name('return');
        Route::get('/{report}/print', [DepartmentReportController::class, 'print'])->name('print');
        Route::get('/{report}/download', [DepartmentReportController::class, 'download'])->name('download');
    });

    // Governance: Audit Trail Explorer
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit.index');

    // Settings & Configuration
    Route::prefix('settings')->name('settings.')->group(function () {
        // Hospital Branding Profile
        Route::get('/hospital', [HospitalSettingsController::class, 'edit'])->name('hospital.edit');
        Route::put('/hospital', [HospitalSettingsController::class, 'update'])->name('hospital.update');

        // Programmable Form Builder & Cadence
        Route::get('/forms', [FormBuilderController::class, 'index'])->name('forms.index');
        Route::get('/forms/{department}/edit', [FormBuilderController::class, 'edit'])->name('forms.edit');
        Route::put('/forms/{department}', [FormBuilderController::class, 'update'])->name('forms.update');

        // RBAC User Management
        Route::resource('users', UserManagementController::class)->except(['show']);
    });

    // Standard Operating Procedures (SOPs) Portal
    Route::prefix('sops')->name('sops.')->group(function () {
        Route::get('/', [SopPortalController::class, 'index'])->name('index');
        Route::get('/create', [SopPortalController::class, 'create'])->name('create');
        Route::post('/', [SopPortalController::class, 'store'])->name('store');
        Route::get('/{sop}', [SopPortalController::class, 'show'])->name('show');
        Route::get('/{sop}/download', [SopPortalController::class, 'download'])->name('download');
        Route::get('/{sop}/file', [SopPortalController::class, 'file'])->name('file');
        Route::post('/{sop}/leaflets', [SopPortalController::class, 'uploadLeaflet'])->name('leaflets.upload');
        Route::match(['POST', 'PATCH'], '/{sop}/toggle-download', [SopPortalController::class, 'toggleDownload'])->name('toggle-download');
        Route::delete('/{sop}', [SopPortalController::class, 'destroy'])->name('destroy');
    });

    // Super Admin Secret Toggle for SOP Portal & Aliases
    Route::match(['POST', 'PATCH'], '/superadmin/sops/toggle-portal', [SopPortalController::class, 'togglePortal'])->name('superadmin.sops.toggle');
    Route::match(['POST', 'PATCH'], '/superadmin/sops/toggle', [SopPortalController::class, 'togglePortal']);
    Route::match(['POST', 'PATCH'], '/superadmin/sops/{sop}/toggle-download', [SopPortalController::class, 'toggleDownload'])->name('superadmin.sops.download.toggle');
    Route::delete('/superadmin/sops/{sop}', [SopPortalController::class, 'destroy'])->name('superadmin.sops.destroy');

    // Secret Super Admin DeepSeek AI Agent (Strictly invisible to other roles)
    Route::prefix('superadmin/deepseek')->name('superadmin.deepseek.')->group(function () {
        Route::get('/', [SuperAdminAiController::class, 'index'])->name('index');
        Route::post('/analyze', [SuperAdminAiController::class, 'analyze'])->name('analyze');
        Route::post('/publish', [SuperAdminAiController::class, 'publish'])->name('publish');
        Route::post('/config', [SuperAdminAiController::class, 'updateConfig'])->name('config');
    });
});
