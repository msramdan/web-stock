<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/', fn () => view('dashboard'));
    Route::get('/dashboard', fn () => view('dashboard'));
    Route::get('/profile', App\Http\Controllers\ProfileController::class)->name('profile');
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('roles', App\Http\Controllers\RoleAndPermissionController::class);
});

Route::resource('jenis-material', App\Http\Controllers\JenisMaterialController::class)->middleware('auth');
Route::resource('unit-satuan', App\Http\Controllers\UnitSatuanController::class)->middleware('auth');
Route::resource('setting-aplikasi', App\Http\Controllers\SettingAplikasiController::class)->middleware('auth');
Route::resource('backup-database', App\Http\Controllers\BackupDatabaseController::class)->middleware('auth');
Route::get('/backup/download', [App\Http\Controllers\BackupDatabaseController::class, 'downloadBackup'])->name('backup.download');
Route::resource('barang', App\Http\Controllers\BarangController::class)->middleware('auth');
Route::resource('transaksi-stock-in', App\Http\Controllers\TransaksiStockInController::class)->middleware('auth');
Route::resource('transaksi-stock-out', App\Http\Controllers\TransaksiStockOutController::class)->middleware('auth');
Route::get('/listDataBarang', [App\Http\Controllers\BarangController::class, 'listDataBarang'])->name('listDataBarang');
