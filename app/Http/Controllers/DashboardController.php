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

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan data ringkasan dan chart.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): View
    {
        $companyId = session('sessionCompany');

        // Jika tidak ada company terpilih, tampilkan pesan atau data kosong
        if (!$companyId) {
            // Opsi 1: Tampilkan view dengan pesan error/warning
            // return view('dashboard-no-company'); // Buat view ini jika perlu

            // Opsi 2: Tetap tampilkan dashboard tapi dengan data 0 atau kosong
            $totalBarang = 0;
            $totalJenisMaterial = 0;
            $totalUnitSatuan = 0;
            $totalUser = User::count(); // User mungkin global
            $latestTransactions = collect(); // Collection kosong
            $chartMonths = [];
            $chartStockIn = [];
            $chartStockOut = [];

            return view('dashboard', compact(
                'totalBarang',
                'totalJenisMaterial',
                'totalUnitSatuan',
                'totalUser',
                'latestTransactions',
                'chartMonths',
                'chartStockIn',
                'chartStockOut'
            ))->with('warning', 'Silakan pilih perusahaan untuk melihat data.'); // Kirim pesan
        }

        // Hitung data ringkasan BERDASARKAN COMPANY ID
        $totalBarang = Barang::where('company_id', $companyId)->count();
        $totalJenisMaterial = JenisMaterial::where('company_id', $companyId)->count();
        $totalUnitSatuan = UnitSatuan::where('company_id', $companyId)->count();
        $totalUser = User::count(); // Asumsi user global, jika tidak perlu join assign_company

        // Ambil 5 transaksi terakhir BERDASARKAN COMPANY ID
        $latestTransactions = DB::table('transaksi')
            ->select('transaksi.id', 'transaksi.no_surat', 'transaksi.tanggal', 'transaksi.type', 'users.name as user_name')
            ->join('users', 'users.id', '=', 'transaksi.user_id')
            ->where('transaksi.company_id', $companyId) // <<<---- FILTER COMPANY
            ->orderByDesc('transaksi.tanggal')
            ->limit(5)
            ->get();

        // --- Data Chart BERDASARKAN COMPANY ID ---
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();

        $monthlyTransactions = DB::table('transaksi')
            ->select(
                DB::raw("DATE_FORMAT(tanggal, '%Y-%m') as month"),
                // DB::raw("DATE_FORMAT(tanggal, '%b %Y') as month_name"), // Tidak perlu group by month_name
                'type',
                DB::raw('COUNT(*) as count')
            )
            ->where('company_id', $companyId) // <<<---- FILTER COMPANY
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('month', 'type') // Group by month (YYYY-MM) dan type
            ->orderBy('month')
            ->get()
            ->keyBy(fn($item) => $item->month . '-' . $item->type); // Kunci: 2025-04-In

        // Siapkan array untuk chart
        $chartMonths = [];
        $chartStockIn = [];
        $chartStockOut = [];

        $currentMonth = $startDate->copy();
        for ($i = 0; $i < 12; $i++) {
            $monthKey = $currentMonth->format('Y-m');
            try {
                $monthName = $currentMonth->translatedFormat('M Y');
            } catch (\Exception $e) {
                $monthName = $currentMonth->format('M Y'); // Fallback
            }

            $chartMonths[] = $monthName;

            $keyIn = $monthKey . '-In';
            $chartStockIn[] = $monthlyTransactions->get($keyIn)?->count ?? 0; // Gunakan get() untuk safety

            $keyOut = $monthKey . '-Out';
            $chartStockOut[] = $monthlyTransactions->get($keyOut)?->count ?? 0; // Gunakan get() untuk safety

            $currentMonth->addMonth();
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
