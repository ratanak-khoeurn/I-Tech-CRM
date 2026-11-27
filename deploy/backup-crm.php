<?php

// Nightly CRM backup: database dump + uploaded files + .env (the .env holds APP_KEY, so a
// restore without it cannot decrypt sessions or encrypted columns).
//
// Run manually:  php deploy/backup-crm.php
// Writes outside the project folder so a stray "git add" can never commit student data.

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$base = rtrim(getenv('CRM_BACKUP_DIR') ?: 'D:/Company/I-TECH-MEDIA/CRM-BACKUPS', '/');
$stamp = date('Y-m-d_His');
$dest = $base.'/'.$stamp;
$keepDays = 14;

if (!is_dir($base) && !mkdir($base, 0777, true)) {
    fwrite(STDERR, "Cannot create $base\n");
    exit(1);
}
mkdir($dest, 0777, true);

function copyTree(string $from, string $to): int
{
    if (!is_dir($from)) {
        return 0;
    }

    @mkdir($to, 0777, true);
    $files = 0;

    foreach (scandir($from) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $src = $from.'/'.$entry;

        if (is_dir($src)) {
            $files += copyTree($src, $to.'/'.$entry);
            continue;
        }

        if (copy($src, $to.'/'.$entry)) {
            $files++;
        }
    }

    return $files;
}

function removeTree(string $dir): void
{
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $dir.'/'.$entry;
        is_dir($path) ? removeTree($path) : @unlink($path);
    }

    @rmdir($dir);
}

$errors = [];

// ---------------------------------------------------------------- database

$config = config('database.connections.'.config('database.default'));
$mysqldump = 'C:/xampp/mysql/bin/mysqldump.exe';

if (!is_file($mysqldump)) {
    $errors[] = "mysqldump not found at $mysqldump";
} elseif (($config['driver'] ?? 'mysql') !== 'mysql') {
    $errors[] = 'Unexpected database driver: '.($config['driver'] ?? '?');
} else {
    // Password goes through a temp defaults file, never the command line (it would show up in
    // the process list and in this script's log).
    $tmp = tempnam(sys_get_temp_dir(), 'crmdb');
    file_put_contents($tmp, "[client]\nhost=".($config['host'])."\nport=".($config['port']).
        "\nuser=".($config['username'])."\npassword=\"".str_replace('"', '\"', (string) $config['password'])."\"\n");

    $dump = $dest.'/database.sql';
    $cmd = escapeshellarg($mysqldump).' --defaults-extra-file='.escapeshellarg($tmp).
        ' --single-transaction --routines --triggers --default-character-set=utf8mb4'.
        ' --databases '.escapeshellarg($config['database']).' --result-file='.escapeshellarg($dump);

    exec($cmd, $out, $code);
    @unlink($tmp);

    if ($code !== 0 || !is_file($dump) || filesize($dump) < 1024) {
        $errors[] = 'mysqldump failed (exit '.$code.'): '.implode(' ', array_slice($out, -3));
    } else {
        @unlink($dest.'/mysqldump-error.txt');
        $rows = DB::selectOne('SELECT (SELECT COUNT(*) FROM students) AS students, (SELECT COUNT(*) FROM users) AS users');
        $meta = [
            'finished_at' => now()->toDateTimeString(),
            'database' => $config['database'],
            'dump_bytes' => filesize($dump),
            'students' => $rows->students ?? null,
            'users' => $rows->users ?? null,
        ];
        file_put_contents($dest.'/manifest.json', json_encode($meta, JSON_PRETTY_PRINT));
        echo 'database: '.number_format($meta['dump_bytes'])." bytes\n";
    }
}

// ---------------------------------------------------------------- uploads + env

$uploads = copyTree($root.'/storage/app/public', $dest.'/storage/app/public');
echo "uploads: $uploads files\n";
copy($root.'/.env', $dest.'/env.txt');

if ($uploads === 0) {
    $errors[] = 'no uploaded files copied (storage/app/public empty?)';
}

// ---------------------------------------------------------------- rotation

$cut = time() - ($keepDays * 86400);
$removed = 0;

foreach (glob($base.'/[0-9]*') as $old) {
    if (is_dir($old) && filemtime($old) < $cut) {
        removeTree($old);
        $removed++;
    }
}

echo "rotation: $removed backup(s) older than $keepDays days removed\n";

if ($errors) {
    file_put_contents($base.'/latest-error.txt', date('Y-m-d H:i:s')."\n".implode("\n", $errors)."\n", FILE_APPEND);
    fwrite(STDERR, implode("\n", $errors)."\n");
    exit(1);
}

@unlink($base.'/latest-error.txt');
echo "backup written to $dest\n";
