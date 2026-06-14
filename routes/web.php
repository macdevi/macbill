<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OdpController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Collector\DashboardController as CollectorDashboard;
use App\Http\Controllers\Collector\InvoiceController as CollectorInvoiceController;
use App\Http\Controllers\Technician\DashboardController as TechnicianDashboard;





// MAC SAFE RESET CUSTOMER ROUTES START
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/pengaturan/reset-data-pelanggan', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/pengaturan/reset-pelanggan', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/reset-data-pelanggan', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/settings/reset-customers', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/settings/reset-data', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/settings/reset-data-pelanggan', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
\Illuminate\Support\Facades\Route::match(['GET','POST','DELETE'], '/admin/settings/reset-data/customers', [\App\Http\Controllers\Admin\ResetCustomerDataController::class, 'handle'])->middleware(['auth']);
// MAC SAFE RESET CUSTOMER ROUTES END

/* MACSERVICE STABLE LOGIN ROUTES START */
Route::get('/', fn () => view('landing'))->name('landing');
Route::get('/login', fn () => redirect('/'))->name('login');

// FINAL ROLE LOGIN ROUTES - bersih
Route::get('/admin/login', function () {
    return view('auth.role-login', [
        'title' => 'Login Admin',
        'roleLabel' => 'ADMIN ACCESS',
        'role' => 'admin',
        'postUrl' => '/admin/login',
    ]);
})->name('admin.login');

Route::post('/admin/login', function () {
    return app(\App\Http\Controllers\Auth\RoleAuthController::class)->login(request(), 'admin');
})->name('admin.login.submit')->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/collector/login', function () {
    return view('auth.role-login', [
        'title' => 'Login Kasir',
        'roleLabel' => 'COLLECTOR / KASIR ACCESS',
        'role' => 'collector',
        'postUrl' => '/collector/login',
    ]);
})->name('collector.login');

Route::post('/collector/login', function () {
    return app(\App\Http\Controllers\Auth\RoleAuthController::class)->login(request(), 'collector');
})->name('collector.login.submit')->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/technician/login', function () {
    return view('auth.role-login', [
        'title' => 'Login Teknisi',
        'roleLabel' => 'TECHNICIAN ACCESS',
        'role' => 'technician',
        'postUrl' => '/technician/login',
    ]);
})->name('technician.login');

Route::post('/technician/login', function () {
    return app(\App\Http\Controllers\Auth\RoleAuthController::class)->login(request(), 'technician');
})->name('technician.login.submit')->withoutMiddleware([VerifyCsrfToken::class]);


Route::post('/logout', [\App\Http\Controllers\Auth\RoleAuthController::class, 'logout'])->name('logout');
Route::post('/admin/logout', [\App\Http\Controllers\Auth\RoleAuthController::class, 'logout'])->name('admin.logout');
Route::post('/collector/logout', [\App\Http\Controllers\Auth\RoleAuthController::class, 'logout'])->name('collector.logout');
Route::post('/technician/logout', [\App\Http\Controllers\Auth\RoleAuthController::class, 'logout'])->name('technician.logout');
/* MACSERVICE STABLE LOGIN ROUTES END */

Route::middleware('guest')->group(function () {
    
    
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    

    Route::get('/admin/dashboard', [AdminDashboard::class, 'index']);
    Route::get('/admin/expenses', [ExpenseController::class, 'index']);
    Route::get('/admin/expenses/create', [ExpenseController::class, 'create']);
    Route::post('/admin/expenses', [ExpenseController::class, 'store']);
    Route::get('/admin/expenses/{expense}/edit', [ExpenseController::class, 'edit']);
    Route::put('/admin/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/admin/expenses/{expense}', [ExpenseController::class, 'destroy']);

    Route::get('/admin/reports/finance', [ReportController::class, 'finance']);
    Route::get('/admin/reports/finance/export', [ReportController::class, 'financeExport']);

    Route::resource('/admin/packages', PackageController::class)->except(['show']);
    Route::get('/admin/odps-map', [OdpController::class, 'map']);
    Route::get('/admin/odps/{odp}/ports', [OdpController::class, 'ports']);
    Route::post('/admin/odps/{odp}/ports', [OdpController::class, 'updatePorts']);
    Route::resource('/admin/odps', OdpController::class)->except(['show']);

    Route::get('/admin/customers/{customer}/detail', [CustomerController::class, 'detail']);
    Route::get('/admin/customers/template', [CustomerController::class, 'template']);
    Route::get('/admin/customers/export', [CustomerController::class, 'export']);
    Route::get('/admin/customers/import', [CustomerController::class, 'importForm']);
    Route::post('/admin/customers/import', [CustomerController::class, 'import']);
    Route::resource('/admin/customers', CustomerController::class)->except(['show']);

    Route::get('/admin/invoices/preview', [AdminInvoiceController::class, 'preview']);
    Route::get('/admin/invoices/early', [AdminInvoiceController::class, 'early']);
    Route::get('/admin/invoices/schedule', [AdminInvoiceController::class, 'schedule']);
    Route::get('/admin/invoices/aging', [AdminInvoiceController::class, 'aging']);
    Route::post('/admin/invoices/early-generate-selected', [AdminInvoiceController::class, 'generateEarlySelected']);
    Route::post('/admin/invoices/generate-selected', [AdminInvoiceController::class, 'generateSelected']);
    Route::get('/admin/invoices', [AdminInvoiceController::class, 'index']);
    Route::post('/admin/invoices/sync', [AdminInvoiceController::class, 'sync']);
    Route::get('/admin/invoices/sync', fn () => redirect('/admin/invoices')->with('error', 'Sinkron status harus dijalankan dari tombol aksi, bukan lewat URL.'));
    Route::post('/admin/invoices/auto-run', [AdminInvoiceController::class, 'autoRun']);
    Route::get('/admin/invoices/auto-run', fn () => redirect('/admin/invoices')->with('error', 'Auto billing manual harus dijalankan dari tombol aksi, bukan lewat URL.'));
    Route::post('/admin/invoices/customer/{customer}/reset-data', [\App\Http\Controllers\Admin\InvoiceController::class, 'resetCustomerInvoiceData'])->name('admin.invoices.customer.reset-data');
    Route::get('/admin/invoices/{invoice}/detail', [AdminInvoiceController::class, 'detail']);
    Route::post('/admin/invoices/{invoice}/reset', [\App\Http\Controllers\Admin\InvoiceController::class, 'resetInvoice'])->name('admin.invoices.reset');
    Route::get('/admin/invoices/{invoice}/print', [AdminInvoiceController::class, 'printInvoice']);
    Route::get('/admin/payments/{payment}/receipt', [CollectorInvoiceController::class, 'receipt']);

    Route::get('/collector/dashboard', [CollectorDashboard::class, 'index']);
    Route::get('/collector/percobaan', [\App\Http\Controllers\Collector\PercobaanController::class, 'index']);
    Route::get('/collector/percobaan/dashboard', [\App\Http\Controllers\Collector\PercobaanController::class, 'index']);
    Route::get('/collector/pemasukan-bulan-ini', [\App\Http\Controllers\Collector\FinanceSummaryController::class, 'incomeMonth']);
    Route::get('/collector/profit-bulan-ini', [\App\Http\Controllers\Collector\FinanceSummaryController::class, 'profitMonth']);
    Route::get('/collector/expenses', [ExpenseController::class, 'index']);
    Route::get('/collector/expenses/create', [ExpenseController::class, 'create']);
    Route::post('/collector/expenses', [ExpenseController::class, 'store']);
    Route::get('/collector/expenses/{expense}/edit', [ExpenseController::class, 'edit']);
    Route::put('/collector/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/collector/expenses/{expense}', [ExpenseController::class, 'destroy']);

    Route::get('/collector/invoices', [CollectorInvoiceController::class, 'index']);
    Route::get('/collector/customer/{customer}', [CollectorInvoiceController::class, 'customer']);
    Route::get('/collector/customer/{customer}/view', \App\Http\Controllers\Collector\CustomerViewController::class);
    Route::post('/collector/pay-selected', [CollectorInvoiceController::class, 'paySelected']);
    Route::post('/collector/pay-all/{customer}', [CollectorInvoiceController::class, 'payAll']);
    Route::get('/collector/history', [CollectorInvoiceController::class, 'history']);
    Route::get('/collector/payments/{payment}/receipt', [CollectorInvoiceController::class, 'receipt']);

    Route::get('/technician/dashboard', [TechnicianDashboard::class, 'index']);
});


/* MACSERVICE USER SETTINGS ROUTES START */
Route::middleware(['auth'])->prefix('admin/settings')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Admin\UserSettingController::class, 'index']);
    Route::get('/users/create', [\App\Http\Controllers\Admin\UserSettingController::class, 'create']);
    Route::post('/users', [\App\Http\Controllers\Admin\UserSettingController::class, 'store']);
    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserSettingController::class, 'edit']);
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserSettingController::class, 'update']);
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserSettingController::class, 'destroy']);
});
/* MACSERVICE USER SETTINGS ROUTES END */


/* MACSERVICE ADMIN SETTINGS DROPDOWN ROUTES START */
Route::middleware(['auth'])->prefix('admin/settings')->group(function () {
    Route::get('/general', [\App\Http\Controllers\Admin\SettingController::class, 'general']);
    Route::post('/general', [\App\Http\Controllers\Admin\SettingController::class, 'updateGeneral']);
    Route::get('/mikrotik', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotik']);
    Route::get('/olt', [\App\Http\Controllers\Admin\SettingController::class, 'olt']);
});
/* MACSERVICE ADMIN SETTINGS DROPDOWN ROUTES END */


/* MACSERVICE MIKROTIK SETTINGS CRUD ROUTES START */
Route::middleware(['auth'])->prefix('admin/settings')->group(function () {

    // MACSERVICE MIKROTIK SUBMENU ROUTES START
    Route::get('/mikrotik/profiles', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikProfiles']);
    Route::get('/mikrotik/secrets', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikSecrets']);
    Route::get('/mikrotik/active-sessions', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikActiveSessions']);
    // MACSERVICE MIKROTIK SUBMENU ROUTES END
    Route::get('/mikrotik/create', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikCreate']);
    Route::post('/mikrotik', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikStore']);
        Route::post('/mikrotik/{router}/test', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikTest']);
    Route::post('/mikrotik/{router}/sync-profiles', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikSyncProfiles']);
    Route::post('/mikrotik/{router}/sync-secrets', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikSyncSecrets']);
    Route::post('/mikrotik/{router}/sync-active', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikSyncActiveSessions']);
    Route::get('/mikrotik/{router}/edit', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikEdit']);
    Route::put('/mikrotik/{router}', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikUpdate']);
    Route::delete('/mikrotik/{router}', [\App\Http\Controllers\Admin\SettingController::class, 'mikrotikDestroy']);
});
/* MACSERVICE MIKROTIK SETTINGS CRUD ROUTES END */

/* MACSERVICE PPPOE SECRET AUTOFIND ROUTE START */
Route::middleware(['auth'])->get('/admin/pppoe-secrets/search', [\App\Http\Controllers\Admin\CustomerController::class, 'pppoeSecretSearch']);
/* MACSERVICE PPPOE SECRET AUTOFIND ROUTE END */

/* MACSERVICE CUSTOMER ODP PORT JSON ROUTE START */
Route::middleware(['auth'])->get('/admin/odps/{odp}/ports-json', [\App\Http\Controllers\Admin\CustomerController::class, 'odpPorts']);
/* MACSERVICE CUSTOMER ODP PORT JSON ROUTE END */

/* MACSERVICE CUSTOMER SYNC PPPOE SECRET ROUTE START */
Route::middleware(['auth'])->post('/admin/customers/{customer}/sync-pppoe-secret', [\App\Http\Controllers\Admin\CustomerController::class, 'syncPppoeSecret']);
/* MACSERVICE CUSTOMER SYNC PPPOE SECRET ROUTE END */

/* MACSERVICE PPPOE MONITORING ROUTE START */
Route::middleware(['auth'])->get('/admin/monitoring/pppoe', [\App\Http\Controllers\Admin\PppoeMonitorController::class, 'index']);
/* MACSERVICE PPPOE MONITORING ROUTE END */

/* MACSERVICE PPPOE RECONCILE ROUTE START */
Route::middleware(['auth'])->get('/admin/monitoring/pppoe/reconcile', [\App\Http\Controllers\Admin\PppoeMonitorController::class, 'reconcile']);
/* MACSERVICE PPPOE RECONCILE ROUTE END */

/* MACSERVICE IMPORT PPPOE SECRET TO CUSTOMER ROUTE START */
/* MACSERVICE IMPORT PPPOE SECRET TO CUSTOMER ROUTE END */


/* MACSERVICE SYSTEM HEALTH ROUTES START */

// admin-genieacs-routes-v1-start
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/genieacs', [\App\Http\Controllers\Admin\GenieAcsController::class, 'index']);
    Route::post('/admin/genieacs/save', [\App\Http\Controllers\Admin\GenieAcsController::class, 'save']);
    Route::post('/admin/genieacs/test', [\App\Http\Controllers\Admin\GenieAcsController::class, 'test']);
});
// admin-genieacs-routes-v1-end

Route::middleware(['auth'])->get('/admin/system/health', [\App\Http\Controllers\Admin\SystemHealthController::class, 'index']);
Route::middleware(['auth'])->post('/admin/system/health/refresh-pppoe', [\App\Http\Controllers\Admin\SystemHealthController::class, 'refreshPppoe']);
Route::middleware(['auth'])->post('/admin/system/health/run-billing', [\App\Http\Controllers\Admin\SystemHealthController::class, 'runBilling']);
Route::middleware(['auth'])->post('/admin/system/health/run-audit', [\App\Http\Controllers\Admin\SystemHealthController::class, 'runAudit']);
/* MACSERVICE SYSTEM HEALTH ROUTES END */


/* MACSERVICE DATABASE BACKUP ROUTES START */
Route::middleware(['auth'])->get('/admin/system/backups', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'index']);
Route::middleware(['auth'])->post('/admin/system/backups', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'create']);
Route::middleware(['auth'])->get('/admin/system/backups/{file}/download', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'download']);
Route::middleware(['auth'])->delete('/admin/system/backups/{file}', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'destroy']);
/* MACSERVICE DATABASE BACKUP ROUTES END */


// Kasir daftar semua pelanggan

// Kasir daftar semua pelanggan
Route::middleware(['auth'])->get('/collector/customers', [\App\Http\Controllers\Collector\CustomerListController::class, 'index'])->name('collector.customers.index');

// Admin reset data pelanggan
Route::middleware(['auth'])->get('/admin/settings/reset-data', [\App\Http\Controllers\Admin\DataResetController::class, 'index'])->name('admin.settings.reset-data');
Route::middleware(['auth'])->post('/admin/settings/reset-data', [\App\Http\Controllers\Admin\DataResetController::class, 'reset'])->name('admin.settings.reset-data.post');


// Alias Indonesia untuk Pengaturan Umum
Route::middleware(['auth'])->prefix('admin/pengaturan')->group(function () {
    Route::get('/umum', [\App\Http\Controllers\Admin\SettingController::class, 'general']);
    Route::post('/umum', [\App\Http\Controllers\Admin\SettingController::class, 'updateGeneral']);
    Route::get('/pengaturan-umum', [\App\Http\Controllers\Admin\SettingController::class, 'general']);
    Route::post('/pengaturan-umum', [\App\Http\Controllers\Admin\SettingController::class, 'updateGeneral']);
});


// Collector invoice generation - same logic as admin
Route::middleware(['auth'])->prefix('collector')->group(function () {
    Route::get('/invoices/preview', [\App\Http\Controllers\Admin\InvoiceController::class, 'preview']);
    Route::post('/invoices/generate-selected', [\App\Http\Controllers\Admin\InvoiceController::class, 'generateSelected']);
    Route::get('/invoices/schedule', [\App\Http\Controllers\Admin\InvoiceController::class, 'schedule']);
    Route::get('/invoices/auto-run', [\App\Http\Controllers\Admin\InvoiceController::class, 'autoRun']);
    Route::post('/invoices/auto-run', [\App\Http\Controllers\Admin\InvoiceController::class, 'autoRun']);
    Route::get('/invoices/early', [\App\Http\Controllers\Admin\InvoiceController::class, 'early']);
    Route::post('/invoices/early-generate-selected', [\App\Http\Controllers\Admin\InvoiceController::class, 'generateEarlySelected']);
    Route::get('/invoices/sync', [\App\Http\Controllers\Admin\InvoiceController::class, 'sync']);
    Route::post('/invoices/sync', [\App\Http\Controllers\Admin\InvoiceController::class, 'sync']);
});


// New Mikrotik module
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/mikrotik', [\App\Http\Controllers\Admin\MikrotikController::class, 'index']);
    Route::get('/admin/mikrotik/integrasi', [\App\Http\Controllers\Admin\MikrotikController::class, 'integration']);
    Route::post('/admin/mikrotik/integrasi', [\App\Http\Controllers\Admin\MikrotikController::class, 'storeIntegration']);
    Route::post('/admin/mikrotik/router/{router}/test', [\App\Http\Controllers\Admin\MikrotikController::class, 'testConnection']);
    Route::put('/admin/mikrotik/router/{router}', [\App\Http\Controllers\Admin\MikrotikController::class, 'updateIntegration']);
    Route::delete('/admin/mikrotik/router/{router}', [\App\Http\Controllers\Admin\MikrotikController::class, 'destroyIntegration']);

    Route::get('/admin/mikrotik/pppoe-active', [\App\Http\Controllers\Admin\MikrotikController::class, 'pppoeActive']);
    Route::get('/admin/mikrotik/pppoe-offline', [\App\Http\Controllers\Admin\MikrotikController::class, 'pppoeOffline']);
    Route::post('/admin/mikrotik/pppoe-active/refresh', [\App\Http\Controllers\Admin\MikrotikController::class, 'refreshPppoeActive']);

    Route::get('/admin/mikrotik/pppoe-secret', [\App\Http\Controllers\Admin\MikrotikController::class, 'pppoeSecrets']);
    Route::post('/admin/mikrotik/pppoe-secret/sync', [\App\Http\Controllers\Admin\MikrotikController::class, 'syncPppoeSecrets']);
    Route::post('/admin/mikrotik/pppoe-secret/auto-link', [\App\Http\Controllers\Admin\MikrotikController::class, 'autoLinkPppoeSecrets']);
    Route::get('/admin/mikrotik/pppoe-secret/{secret}/tautkan', [\App\Http\Controllers\Admin\MikrotikController::class, 'showPppoeSecretLinkCustomers']);
    Route::post('/admin/mikrotik/pppoe-secret/{secret}/tautkan/customer/{customer}', [\App\Http\Controllers\Admin\MikrotikController::class, 'linkPppoeSecretToSelectedCustomer']);

    Route::get('/admin/mikrotik/pppoe-profile', [\App\Http\Controllers\Admin\MikrotikController::class, 'pppoeProfiles']);
    Route::post('/admin/mikrotik/pppoe-profile/sync', [\App\Http\Controllers\Admin\MikrotikController::class, 'syncPppoeProfiles']);
});


Route::middleware(['auth'])->group(function () {
    Route::get('/collector/percobaan/tagihan', [\App\Http\Controllers\Collector\PercobaanController::class, 'tagihan']);
    Route::get('/collector/percobaan/bayar-gabungan', [\App\Http\Controllers\Collector\PercobaanController::class, 'bayarGabungan']);
    Route::get('/collector/percobaan/bayar/{invoice}', [\App\Http\Controllers\Collector\PercobaanController::class, 'bayar']);
    Route::post('/collector/percobaan/bayar/{invoice}', [\App\Http\Controllers\Collector\PercobaanController::class, 'bayar']);
    Route::get('/collector/percobaan/status-pelanggan', [\App\Http\Controllers\Collector\PercobaanController::class, 'statusPelanggan']);
    Route::get('/collector/percobaan/tagihan-manual', [\App\Http\Controllers\Collector\PercobaanController::class, 'manual']);
    Route::post('/collector/percobaan/tagihan-manual', [\App\Http\Controllers\Collector\PercobaanController::class, 'storeManual']);
    Route::get('/collector/percobaan/pengeluaran', [\App\Http\Controllers\Collector\PercobaanController::class, 'pengeluaran']);
    Route::post('/collector/percobaan/pengeluaran', [\App\Http\Controllers\Collector\PercobaanController::class, 'storePengeluaran']);
    Route::get('/collector/percobaan/riwayat', [\App\Http\Controllers\Collector\PercobaanController::class, 'riwayat']);
    Route::get('/collector/percobaan/profile', [\App\Http\Controllers\Collector\PercobaanController::class, 'profile']);
    Route::post('/collector/percobaan/profile', [\App\Http\Controllers\Collector\PercobaanController::class, 'updateProfile']);
});


// kasir-clean-url-v1-start
// URL resmi kasir baru: /kasir
// URL lama tetap diarahkan agar tidak 404.
Route::middleware(['auth'])->group(function () {
    Route::get('/kasir', [\App\Http\Controllers\Collector\PercobaanController::class, 'index']);
    Route::get('/kasir/dashboard', [\App\Http\Controllers\Collector\PercobaanController::class, 'index']);
    Route::get('/kasir/tagihan', [\App\Http\Controllers\Collector\PercobaanController::class, 'tagihan']);
    Route::get('/kasir/bayar-gabungan', [\App\Http\Controllers\Collector\PercobaanController::class, 'bayarGabungan']);
    Route::get('/kasir/bayar/{invoice}', [\App\Http\Controllers\Collector\PercobaanController::class, 'bayar']);
    Route::post('/kasir/bayar/{invoice}', [\App\Http\Controllers\Collector\PercobaanController::class, 'bayar']);
    Route::get('/kasir/tagihan-manual', [\App\Http\Controllers\Collector\PercobaanController::class, 'manual']);
    Route::post('/kasir/tagihan-manual', [\App\Http\Controllers\Collector\PercobaanController::class, 'storeManual']);
    Route::get('/kasir/pengeluaran', [\App\Http\Controllers\Collector\PercobaanController::class, 'pengeluaran']);
    Route::post('/kasir/pengeluaran', [\App\Http\Controllers\Collector\PercobaanController::class, 'storePengeluaran']);

    // kasir-expense-crud-routes-v1-start
    Route::post('/kasir/pengeluaran/{expense}/update', [\App\Http\Controllers\Collector\PercobaanController::class, 'updatePengeluaran']);
    Route::post('/kasir/pengeluaran/{expense}/delete', [\App\Http\Controllers\Collector\PercobaanController::class, 'deletePengeluaran']);
    // kasir-expense-crud-routes-v1-end

    Route::get('/kasir/riwayat', [\App\Http\Controllers\Collector\PercobaanController::class, 'riwayat']);
    Route::get('/kasir/status-pelanggan', [\App\Http\Controllers\Collector\PercobaanController::class, 'statusPelanggan']);
    Route::get('/kasir/profile', [\App\Http\Controllers\Collector\PercobaanController::class, 'profile']);
    Route::post('/kasir/profile', [\App\Http\Controllers\Collector\PercobaanController::class, 'updateProfile']);

    // Redirect route kasir aktif lama ke URL baru
    Route::get('/collector', fn () => redirect('/kasir'));
    Route::get('/collector/dashboard', fn () => redirect('/kasir'));
    Route::get('/collector/invoices', fn () => redirect('/kasir/tagihan'));
    Route::get('/collector/history', fn () => redirect('/kasir/riwayat'));
    Route::match(['GET','POST'], '/collector/expenses', fn () => redirect('/kasir/pengeluaran'));
    Route::get('/collector/expenses/create', fn () => redirect('/kasir/pengeluaran'));
    Route::get('/collector/customers', fn () => redirect('/kasir/status-pelanggan'));
    Route::get('/collector/customer/{customer}', fn () => redirect('/kasir/status-pelanggan'));
    Route::get('/collector/customer/{customer}/view', fn () => redirect('/kasir/status-pelanggan'));
    Route::match(['GET','POST'], '/collector/pay-selected', fn () => redirect('/kasir/tagihan'));
    Route::match(['GET','POST'], '/collector/pay-all/{customer}', fn () => redirect('/kasir/tagihan'));

    // Redirect URL percobaan lama ke URL baru
    Route::get('/collector/percobaan', fn () => redirect('/kasir'));
    Route::get('/collector/percobaan/dashboard', fn () => redirect('/kasir'));
    Route::get('/collector/percobaan/tagihan', fn () => redirect('/kasir/tagihan'));
    Route::get('/collector/percobaan/bayar-gabungan', fn () => redirect('/kasir/bayar-gabungan'));
    Route::get('/collector/percobaan/bayar/{invoice}', fn ($invoice) => redirect('/kasir/bayar/'.$invoice));
    Route::post('/collector/percobaan/bayar/{invoice}', fn ($invoice) => redirect('/kasir/bayar/'.$invoice));
    Route::get('/collector/percobaan/tagihan-manual', fn () => redirect('/kasir/tagihan-manual'));
    Route::post('/collector/percobaan/tagihan-manual', fn () => redirect('/kasir/tagihan-manual'));
    Route::get('/collector/percobaan/pengeluaran', fn () => redirect('/kasir/pengeluaran'));
    Route::post('/collector/percobaan/pengeluaran', fn () => redirect('/kasir/pengeluaran'));
    Route::get('/collector/percobaan/riwayat', fn () => redirect('/kasir/riwayat'));
    Route::get('/collector/percobaan/status-pelanggan', fn () => redirect('/kasir/status-pelanggan'));
    Route::get('/collector/percobaan/profile', fn () => redirect('/kasir/profile'));
    Route::post('/collector/percobaan/profile', fn () => redirect('/kasir/profile'));
});
// kasir-clean-url-v1-end

