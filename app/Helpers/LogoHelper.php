<?php // app/Helpers/LogoHelper.php

use App\Models\Company;
use Illuminate\Support\Facades\Log;

if (!function_exists('get_company_logo_base64')) {
    /**
     * Mendapatkan logo perusahaan dalam format base64.
     *
     * @param Company|null $activeCompany
     * @return string|null URL base64 logo atau null jika tidak ada/error.
     */
    function get_company_logo_base64(?Company $activeCompany): ?string
    {
        $logoFilename = $activeCompany?->logo_perusahaan;
        $logoPath = $logoFilename ? storage_path('app/public/uploads/logo-perusahaans/' . $logoFilename) : null;

        if ($logoPath && file_exists($logoPath)) {
            try {
                $mime = mime_content_type($logoPath);
                if (str_starts_with($mime, 'image/')) {
                    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                }
            } catch (\Exception $e) {
                Log::warning("Gagal memproses logo company {$logoFilename}: " . $e->getMessage());
            }
        }

        // Jika semua gagal
        return null;
    }
}
