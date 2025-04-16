<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Carbon\Carbon;

class BackupDatabaseController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:backup database view', only: ['index', 'show']),
        ];
    }

    public function index()
    {
        return view('backup-database.index');
    }

    public function downloadBackup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        $filename = Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path('app/backups/' . $filename);

        // Ensure the backup directory exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0777, true);
        }

        // Command to backup the database
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$filePath}";

        $returnVar = null;
        $output = null;

        // Execute the command
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json(['error' => 'Gagal mencadangkan basis data.'], 500);
        }
        // Send the file as a download response
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
