<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * Download a database backup as a .sql file using mysqldump.
     */
    public function download()
    {
        $config = config('database.connections.mysql');
        $dumpPath = $this->findMysqldump();
        if (!$dumpPath) {
            return back()->with('error', 'mysqldump executable not found. Install MySQL client tools or use phpMyAdmin export.');
        }

        $file = storage_path('app/backups/backup-' . now()->format('Y-m-d-His') . '.sql');
        @mkdir(dirname($file), 0755, true);

        $passwordArg = $config['password'] !== '' && $config['password'] !== null
            ? '-p' . escapeshellarg($config['password'])
            : '';
        $cmd = sprintf(
            '%s -h %s -P %s -u %s %s %s --single-transaction --routines --triggers > %s 2>&1',
            escapeshellarg($dumpPath),
            escapeshellarg($config['host']),
            escapeshellarg((string) $config['port']),
            escapeshellarg($config['username']),
            $passwordArg,
            escapeshellarg($config['database']),
            escapeshellarg($file)
        );
        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($file) || filesize($file) === 0) {
            @unlink($file);
            return back()->with('error', 'Backup failed: ' . implode(' ', array_slice($output, 0, 3)));
        }

        AuditLog::record('backup', 'database', null, 'Downloaded a database backup');

        return response()->download($file)->deleteFileAfterSend(true);
    }

    private function findMysqldump(): ?string
    {
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\bin\\mysqldump.exe',
            'mysqldump',
        ];
        foreach ($candidates as $c) {
            if (is_file($c) || $c === 'mysqldump') {
                if ($c === 'mysqldump') {
                    exec('mysqldump --version 2>&1', $o, $code);
                    if ($code === 0) {
                        return $c;
                    }
                    continue;
                }
                return $c;
            }
        }
        return null;
    }
}
