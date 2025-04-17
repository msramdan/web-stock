<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\Barang;
use App\Models\JenisMaterial;
use App\Models\UnitSatuan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan data ringkasan dan chart.
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

        // --- Data untuk Chart Transaksi Bulanan (12 Bulan Terakhir) ---
        $endDate = Carbon::now()->endOfDay(); // Sampai akhir hari ini
        $startDate = Carbon::now()->subMonths(11)->startOfMonth(); // Mulai dari 11 bulan lalu, awal bulan

        // Log untuk debugging
        Log::info('Rentang waktu untuk transaksi bulanan', [
            'startDate' => $startDate->toDateTimeString(),
            'endDate' => $endDate->toDateTimeString()
        ]);

        // Query data transaksi
        $monthlyTransactions = DB::table('transaksi')
            ->select(
                DB::raw("DATE_FORMAT(tanggal, '%Y-%m') as month"), // Format YYYY-MM
                DB::raw("DATE_FORMAT(tanggal, '%b %Y') as month_name"), // Format 'Jan 2025'
                'type',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('month', 'month_name', 'type')
            ->orderBy('month')
            ->get()
            ->keyBy(function ($item) {
                // Buat kunci unik per bulan dan tipe (misal: '2025-04-In')
                return $item->month . '-' . $item->type;
            });

        // Log hasil query untuk debugging
        Log::info('Data transaksi bulanan', [
            'transactions' => $monthlyTransactions->toArray()
        ]);

        // Siapkan array untuk chart
        $chartMonths = [];
        $chartStockIn = [];
        $chartStockOut = [];

        // Loop 12 bulan terakhir untuk memastikan semua bulan ada
        $currentMonth = $startDate->copy();
        for ($i = 0; $i < 12; $i++) {
            $monthKey = $currentMonth->format('Y-m'); // YYYY-MM
            // Format nama bulan sesuai locale aplikasi
            try {
                $monthName = $currentMonth->translatedFormat('M Y'); // Misal: Apr 2025
            } catch (\Exception $e) {
                $monthName = $currentMonth->format('M Y'); // Fallback: Apr 2025
            }

            $chartMonths[] = $monthName;

            // Cari data In untuk bulan ini
            $keyIn = $monthKey . '-In';
            $chartStockIn[] = $monthlyTransactions->has($keyIn) ? $monthlyTransactions[$keyIn]->count : 0;

            // Cari data Out untuk bulan ini
            $keyOut = $monthKey . '-Out';
            $chartStockOut[] = $monthlyTransactions->has($keyOut) ? $monthlyTransactions[$keyOut]->count : 0;

            $currentMonth->addMonth(); // Pindah ke bulan berikutnya
        }
        // --- Akhir Data Chart ---

        // Kirim semua data ke view
        return view('dashboard', compact(
            'totalBarang',
            'totalJenisMaterial',
            'totalUnitSatuan',
            'totalUser',
            'latestTransactions',
            'chartMonths',
            'chartStockIn',
            'chartStockOut'
        ));
    }
}
