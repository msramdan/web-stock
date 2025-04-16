<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\Barang;
use App\Models\JenisMaterial;
use App\Models\UnitSatuan;
use App\Models\User;
use Illuminate\Support\Facades\DB; // <-- Import DB Facade

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan data ringkasan.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        // Hitung data ringkasan
        $totalBarang = Barang::count();
        $totalJenisMaterial = JenisMaterial::count();
        $totalUnitSatuan = UnitSatuan::count();
        $totalUser = User::count();

        // Ambil 5 transaksi terakhir (gabungan In & Out)
        $latestTransactions = DB::table('transaksi')
            ->select('transaksi.id', 'transaksi.no_surat', 'transaksi.tanggal', 'transaksi.type', 'users.name as user_name')
            ->join('users', 'users.id', '=', 'transaksi.user_id')
            ->orderByDesc('transaksi.tanggal') // Urutkan berdasarkan tanggal terbaru
            ->limit(5) // Ambil 5 teratas
            ->get();

        // Kirim semua data ke view
        return view('dashboard', compact(
            'totalBarang',
            'totalJenisMaterial',
            'totalUnitSatuan',
            'totalUser',
            'latestTransactions' // <-- Kirim data transaksi terakhir
        ));
    }
}
