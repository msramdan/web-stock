<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

if (!function_exists('is_active_menu')) {
    function is_active_menu(string|array $route): string
    {
        $activeClass = ' active';

        if (is_string($route)) {
            if (request()->is(substr($route . '*', 1))) {
                return $activeClass;
            }

            if (request()->is(str($route)->slug() . '*')) {
                return $activeClass;
            }

            if (request()->segment(2) === str($route)->before('/')) {
                return $activeClass;
            }

            if (request()->segment(3) === str($route)->after('/')) {
                return $activeClass;
            }
        }

        if (is_array($route)) {
            foreach ($route as $value) {
                $actualRoute = str($value)->remove(' view')->plural();

                if (request()->is(substr($actualRoute . '*', 1))) {
                    return $activeClass;
                }

                if (request()->is(str($actualRoute)->slug() . '*')) {
                    return $activeClass;
                }

                if (request()->segment(2) === $actualRoute) {
                    return $activeClass;
                }

                if (request()->segment(3) === $actualRoute) {
                    return $activeClass;
                }
            }
        }

        return '';
    }
}

function is_active_submenu(string|array $route): string
{
    $activeClass = ' submenu-open';

    if (is_string($route)) {
        if (request()->is(substr($route . '*', 1))) {
            return $activeClass;
        }

        if (request()->is(str($route)->slug() . '*')) {
            return $activeClass;
        }

        if (request()->segment(2) === str($route)->before('/')) {
            return $activeClass;
        }

        if (request()->segment(3) === str($route)->after('/')) {
            return $activeClass;
        }
    }

    if (is_array($route)) {
        foreach ($route as $value) {
            $actualRoute = str($value)->remove(' view')->plural();

            if (request()->is(substr($actualRoute . '*', 1))) {
                return $activeClass;
            }

            if (request()->is(str($actualRoute)->slug() . '*')) {
                return $activeClass;
            }

            if (request()->segment(2) === $actualRoute) {
                return $activeClass;
            }

            if (request()->segment(3) === $actualRoute) {
                return $activeClass;
            }
        }
    }

    return '';
}

function cekAssign($company_id, $user_id)
{
    return DB::table('assign_company')
        ->where('company_id', $company_id)
        ->where('user_id', $user_id)
        ->count();
}

if (!function_exists('formatTanggalIndonesia')) {
    function formatTanggalIndonesia($tanggal, $withTime = true)
    {
        $carbon = Carbon::parse($tanggal)->locale('id');

        if ($withTime) {
            return $carbon->translatedFormat('j F Y H:i'); // contoh: 1 Mei 2025 18:34
        } else {
            return $carbon->translatedFormat('j F Y'); // contoh: 1 Mei 2025
        }
    }
}

function formatAngkaRibuan($number)
{
    // Pastikan input adalah angka
    if (!is_numeric($number)) {
        return '0.00';
    }

    // Format angka dengan 2 digit desimal dan pemisah ribuan
    return number_format((float)$number, 2, '.', ',');
}

if (!function_exists('formatAngkaDesimal')) {
    /**
     * Format angka dengan desimal dan pemisah ribuan Indonesia,
     * menghapus trailing zero yang tidak perlu.
     *
     * @param float|int|string $number Angka yang akan diformat.
     * @param int $maxDecimals Jumlah maksimal desimal yang ditampilkan.
     * @return string Angka yang sudah diformat.
     */
    function formatAngkaDesimal($number, $maxDecimals = 4)
    {
        if (!is_numeric($number)) {
            return '0'; // Atau return $number jika ingin menampilkan input asli
        }

        // Konversi ke float
        $floatNumber = (float) $number;

        // Format dengan jumlah desimal maksimal dan pemisah Indonesia
        $formatted = number_format($floatNumber, $maxDecimals, ',', '.');

        // Hapus trailing zero setelah koma, lalu hapus koma jika tidak ada desimal lagi
        $cleaned = rtrim(rtrim($formatted, '0'), ',');

        return $cleaned;
    }
}


function get_company_logo_base64(?Company $activeCompany): ?string
{
    if (!$activeCompany?->logo_perusahaan) return null;

    $logoPath = storage_path('app/public/uploads/logo-perusahaans/' . $activeCompany->logo_perusahaan);

    if (!file_exists($logoPath)) {
        Log::error("Logo file not found: " . $logoPath);
        return null;
    }

    try {
        $mime = mime_content_type($logoPath);
        $imageData = file_get_contents($logoPath);

        if ($imageData === false) {
            throw new \Exception("Failed to read file contents");
        }

        return 'data:' . $mime . ';base64,' . base64_encode($imageData);
    } catch (\Exception $e) {
        Log::error("Logo processing error: " . $e->getMessage());
        return null;
    }
}
