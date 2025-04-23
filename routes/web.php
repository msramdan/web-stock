<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    UserController,
    RoleAndPermissionController,
    JenisMaterialController,
    UnitSatuanController,
    SettingAplikasiController,
    BackupDatabaseController,
    BarangController,
    TransaksiStockInController,
    TransaksiStockOutController,
    LaporanController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'web'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Fallback ke dashboard jika hanya akses root setelah login
    Route::get('/', [DashboardController::class, 'index']);

    // Profile
    Route::get('/profile', ProfileController::class)->name('profile');

    // Users & Roles
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleAndPermissionController::class);

    // Master Data & Settings (kecuali Barang)
    Route::resource('jenis-material', JenisMaterialController::class);
    Route::resource('unit-satuan', UnitSatuanController::class);
    Route::resource('backup-database', BackupDatabaseController::class)->only(['index', 'create']); // Sesuaikan jika perlu action lain
    Route::get('/backup/download', [BackupDatabaseController::class, 'downloadBackup'])->name('backup.download');

    // Barang Routes
    Route::get('/barang/export-pdf', [BarangController::class, 'exportPdf'])->name('barang.exportPdf');
    Route::resource('barang', BarangController::class);
    Route::get('/listDataBarang', [BarangController::class, 'listDataBarang'])->name('listDataBarang'); // Helper untuk transaksi

    // Transaksi Stock In Routes
    Route::get('/transaksi-stock-in/export-pdf', [TransaksiStockInController::class, 'exportPdf'])->name('transaksi-stock-in.exportPdf');
    Route::get('/transaksi-stock-in/{transaksiStockIn}/export-item-pdf', [TransaksiStockInController::class, 'exportItemPdf'])->name('transaksi-stock-in.exportItemPdf'); // Ubah {id} -> {transaksiStockIn}
    Route::resource('transaksi-stock-in', TransaksiStockInController::class);

    // Transaksi Stock Out Routes
    Route::get('/transaksi-stock-out/export-pdf', [TransaksiStockOutController::class, 'exportPdf'])->name('transaksi-stock-out.exportPdf');
    Route::get('/transaksi-stock-out/{transaksiStockOut}/export-item-pdf', [TransaksiStockOutController::class, 'exportItemPdf'])->name('transaksi-stock-out.exportItemPdf'); // Ubah {id} -> {transaksiStockOut}
    Route::resource('transaksi-stock-out', TransaksiStockOutController::class);

    // Laporan Routes
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])
            ->name('index')
            ->middleware('permission:laporan view');

        Route::post('/export', [LaporanController::class, 'exportExcel'])
            ->name('exportExcel')
            ->middleware('permission:laporan export excel');
    });
});
Route::resource('bom', App\Http\Controllers\BomController::class)->middleware('auth');
Route::resource('company', App\Http\Controllers\CompanyController::class)->middleware('auth');
