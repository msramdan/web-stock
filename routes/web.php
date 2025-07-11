<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    UserController,
    RoleAndPermissionController,
    JenisMaterialController,
    UnitSatuanController,
    BackupDatabaseController,
    BarangController,
    TransaksiStockInController,
    TransaksiStockOutController,
    LaporanController,
    BomController,
    CompanyController,
    ProduksiController,
    PermintaanBarangController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('welcome');


// Grup utama untuk route yang memerlukan autentikasi (login)
Route::middleware(['auth', 'web'])->group(function () {

    // --- Route yang TIDAK bergantung pada company aktif ---
    Route::get('/profile', ProfileController::class)->name('profile');
    Route::post('/update-session', [CompanyController::class, 'updateSession'])->name('updateSession');

    // CRUD User, Role, Company (Asumsi Super Admin/Global)
    Route::resource('users', UserController::class)->middleware('permission:user view|user create|user edit|user delete');
    Route::resource('roles', RoleAndPermissionController::class)->middleware('permission:role & permission view|role & permission create|role & permission edit|role & permission delete');
    Route::resource('company', CompanyController::class)->middleware('permission:company view|company create|company edit|company delete');

    // Backup Database (Asumsi Global)
    Route::get('/backup-database', [BackupDatabaseController::class, 'index'])->name('backup-database.index')->middleware('permission:backup database view');
    Route::get('/backup/download', [BackupDatabaseController::class, 'downloadBackup'])->name('backup.download')->middleware('permission:download backup database');


    // --- Grup Route yang WAJIB memiliki company aktif dan user punya akses ---
    Route::middleware(['company.access'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [DashboardController::class, 'index'])->middleware('auth'); // Fallback jika akses root
        // === PINDAHKAN RUTE SPESIFIK BARANG KE SINI ===
        Route::get('/listDataBarang', [BarangController::class, 'listDataBarang'])->name('listDataBarang');
        Route::get('/barang/export-pdf', [BarangController::class, 'exportPdf'])->name('barang.exportPdf');
        Route::get('/barang/export-excel', [BarangController::class, 'exportExcel'])->name('barang.exportExcel');
        Route::get('permintaan-barang/print-blank-form', [PermintaanBarangController::class, 'printBlankForm'])->name('permintaan-barang.printBlankForm');
        Route::get('permintaan-barang/{permintaan_barang}/print', [PermintaanBarangController::class, 'printSpecificForm'])->name('permintaan-barang.printSpecific');
        Route::get('permintaan-barang/{permintaan_barang}/export-excel', [PermintaanBarangController::class, 'exportItemExcel'])->name('permintaan-barang.exportItemExcel');


        // Master Data Spesifik Company
        Route::resource('jenis-material', JenisMaterialController::class);
        Route::resource('unit-satuan', UnitSatuanController::class);
        Route::resource('barang', BarangController::class);
        Route::resource('bom', BomController::class);
        Route::resource('produksi', ProduksiController::class);
        Route::get('/get-boms-produksi', [ProduksiController::class, 'getBomProduksi'])->name('produksi.getBoms');
        Route::resource('permintaan-barang', PermintaanBarangController::class);


        // Transaksi Spesifik Company
        Route::prefix('transaksi-stock-in')->name('transaksi-stock-in.')->group(function () {
            Route::get('/', [TransaksiStockInController::class, 'index'])->name('index');
            Route::get('/export-pdf', [TransaksiStockInController::class, 'exportPdf'])->name('exportPdf');
            Route::get('/{transaksiStockIn}/export-item-pdf', [TransaksiStockInController::class, 'exportItemPdf'])->name('exportItemPdf');
            Route::get('/create', [TransaksiStockInController::class, 'create'])->name('create');
            Route::post('/', [TransaksiStockInController::class, 'store'])->name('store');
            Route::get('/{transaksiStockIn}', [TransaksiStockInController::class, 'show'])->name('show');
            Route::get('/{transaksiStockIn}/edit', [TransaksiStockInController::class, 'edit'])->name('edit');
            Route::put('/{transaksiStockIn}', [TransaksiStockInController::class, 'update'])->name('update');
            Route::delete('/{transaksiStockIn}', [TransaksiStockInController::class, 'destroy'])->name('destroy');
        }); // Middleware permission sudah ada di dalam controller

        Route::prefix('transaksi-stock-out')->name('transaksi-stock-out.')->group(function () {
            Route::get('/', [TransaksiStockOutController::class, 'index'])->name('index');
            Route::get('/export-pdf', [TransaksiStockOutController::class, 'exportPdf'])->name('exportPdf');
            Route::get('/{transaksiStockOut}/export-item-pdf', [TransaksiStockOutController::class, 'exportItemPdf'])->name('exportItemPdf');
            Route::get('/create', [TransaksiStockOutController::class, 'create'])->name('create');
            Route::post('/', [TransaksiStockOutController::class, 'store'])->name('store');
            Route::get('/{transaksiStockOut}', [TransaksiStockOutController::class, 'show'])->name('show');
            Route::get('/{transaksiStockOut}/edit', [TransaksiStockOutController::class, 'edit'])->name('edit');
            Route::put('/{transaksiStockOut}', [TransaksiStockOutController::class, 'update'])->name('update');
            Route::delete('/{transaksiStockOut}', [TransaksiStockOutController::class, 'destroy'])->name('destroy');
        }); // Middleware permission sudah ada di dalam controller

        // Laporan Spesifik Company
        Route::prefix('laporan')->name('laporan.')->middleware(['auth', 'company.access'])->group(function () {
            // Rute untuk Laporan Transaksi
            Route::get('/transaksi', [\App\Http\Controllers\LaporanController::class, 'indexTransaksi'])
                ->name('transaksi.index')
                ->middleware('permission:laporan transaksi view');
            Route::post('/transaksi/export', [\App\Http\Controllers\LaporanController::class, 'exportExcelTransaksi'])
                ->name('transaksi.exportExcel')
                ->middleware('permission:laporan transaksi export excel');

            // Rute untuk Laporan Stok Barang
            Route::get('/stock-barang', [\App\Http\Controllers\LaporanController::class, 'indexStockBarang'])
                ->name('stock-barang.index')
                ->middleware('permission:laporan stock view');
            Route::post('/stock-barang/export', [\App\Http\Controllers\LaporanController::class, 'exportExcelStockBarang'])
                ->name('stock-barang.exportExcel')
                ->middleware('permission:laporan stock export excel');
        });
    });
});
