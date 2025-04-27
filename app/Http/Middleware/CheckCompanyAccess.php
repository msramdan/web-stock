<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Checking company access', [
            'user_id' => Auth::id(),
            'session_company' => session('sessionCompany'),
            'path' => $request->path()
        ]);
        if (!Auth::check()) {
            return $next($request);
        }

        $activeCompanyId = session('sessionCompany');
        $userId = Auth::id();

        if (!$activeCompanyId) {
            Log::warning("User ID {$userId} mencoba akses tanpa sessionCompany.");
            // Kirim response view error, bukan abort
            return response()->view('errors.company-access-forbidden', [
                'message' => __('Silakan pilih perusahaan terlebih dahulu.'), // Pesan singkat
                'messageDetailed' => __('Anda harus memilih perusahaan dari dropdown di sidebar untuk dapat melanjutkan.') // Pesan detail
            ], 403); // Tetap kirim status 403
        }

        $hasAccess = DB::table('assign_company')
            ->where('user_id', $userId)
            ->where('company_id', $activeCompanyId)
            ->exists();

        if (!$hasAccess) {
            Log::warning("User ID {$userId} mencoba akses ke Company ID {$activeCompanyId} tanpa izin.");
            // Kirim response view error, bukan abort
            return response()->view('errors.company-access-forbidden', [
                'message' => __('Anda tidak memiliki akses ke perusahaan ini.'), // Pesan singkat
                'messageDetailed' => __('Anda tidak terdaftar pada perusahaan yang sedang aktif di sesi Anda. Pilih perusahaan lain atau hubungi administrator.') // Pesan detail
            ], 403); // Tetap kirim status 403
        }

        return $next($request);
    }
}
