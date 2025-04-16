<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'web'])->group(function () {
    // Ubah route ini untuk menggunakan DashboardController
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', App\Http\Controllers\ProfileController::class)->name('profile');
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('roles', App\Http\Controllers\RoleAndPermissionController::class);
});

Route::resource('jenis-material', App\Http\Controllers\JenisMaterialController::class)->middleware('auth');
Route::resource('unit-satuan', App\Http\Controllers\UnitSatuanController::class)->middleware('auth');
Route::resource('setting-aplikasi', App\Http\Controllers\SettingAplikasiController::class)->middleware('auth');
Route::resource('backup-database', App\Http\Controllers\BackupDatabaseController::class)->middleware('auth');
Route::get('/backup/download', [App\Http\Controllers\BackupDatabaseController::class, 'downloadBackup'])->name('backup.download');

// Barang Routes
Route::get('/barang/export-pdf', [App\Http\Controllers\BarangController::class, 'exportPdf'])->name('barang.exportPdf')->middleware('auth'); // Letakkan sebelum resource
Route::resource('barang', App\Http\Controllers\BarangController::class)->middleware('auth');

// Transaksi Stock In Routes
Route::get('/transaksi-stock-in/export-pdf', [App\Http\Controllers\TransaksiStockInController::class, 'exportPdf'])->name('transaksi-stock-in.exportPdf')->middleware('auth');
Route::get('/transaksi-stock-in/{id}/export-pdf', [App\Http\Controllers\TransaksiStockInController::class, 'exportItemPdf'])->name('transaksi-stock-in.exportItemPdf')->middleware('auth');
Route::resource('transaksi-stock-in', App\Http\Controllers\TransaksiStockInController::class)->middleware('auth');

// Transaksi Stock Out Routes (akan ditambahkan nanti)
Route::get('/transaksi-stock-out/export-pdf', [App\Http\Controllers\TransaksiStockOutController::class, 'exportPdf'])->name('transaksi-stock-out.exportPdf')->middleware('auth');
Route::get('/transaksi-stock-out/{id}/export-pdf', [App\Http\Controllers\TransaksiStockOutController::class, 'exportItemPdf'])->name('transaksi-stock-out.exportItemPdf')->middleware('auth');
Route::resource('transaksi-stock-out', App\Http\Controllers\TransaksiStockOutController::class)->middleware('auth');

Route::get('/listDataBarang', [App\Http\Controllers\BarangController::class, 'listDataBarang'])->name('listDataBarang');
