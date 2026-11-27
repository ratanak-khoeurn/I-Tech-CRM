<?php

// Restores student_crm from a mysqldump taken by the CRM's Backup page.
//
//   php deploy/restore-from-backup.php backups/student_crm_2026-11-26.sql
//
// Destructive by design: it drops the current student_crm. It always writes a safety dump of the
// current state to D:/Company/I-TECH-MEDIA/CRM-BACKUPS/pre-restore-<timestamp>/ first and stops
// if that dump fails, so the run is reversible either way.

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

$dumpFile = $argv[1] ?? $root.'/backups/student_crm_2026-11-26.sql';
$dumpFile = str_replace('\\', '/', $dumpFile);
if (!str_starts_with($dumpFile, '/')) {
    $dumpFile = $root.'/'.$dumpFile;
}

if (!is_file($dumpFile) || filesize($dumpFile) < 10000) {
    fwrite(STDERR, "Refusing to run: $dumpFile is missing or smaller than 10 KB.\n");
    exit(1);
}

echo 'source dump: '.$dumpFile.' ('.number_format(filesize($dumpFile))." bytes)\n";

$c = config('database.connections.mysql');
$db = $c['database'];
$mysql = 'C:/xampp/mysql/bin/mysql.exe';
$mysqldump = 'C:/xampp/mysql/bin/mysqldump.exe';

$tmp = tempnam(sys_get_temp_dir(), 'mycnf');
file_put_contents($tmp, "[client]\nhost={$c['host']}\nport={$c['port']}\nuser={$c['username']}\npassword=\"{$c['password']}\"\n");

function run(string $cmd, string $label): bool
{
    exec($cmd.' 2>&1', $out, $code);
    echo $label.' exit='.$code.($code === 0 ? '' : ' -> '.implode(' ', array_slice($out, -4)))."\n";

    return $code === 0;
}

$tables = ['students', 'student_courses', 'employees', 'invoices', 'payments', 'users', 'campuses', 'attendance_records', 'study_groups', 'batches'];

function report(string $db, array $tables): string
{
    $counts = [];

    foreach ($tables as $t) {
        try {
            $counts[$t] = DB::selectOne("SELECT COUNT(*) AS n FROM `$db`.`$t`")->n;
        } catch (Throwable $e) {
            $counts[$t] = 'missing';
        }
    }

    return json_encode($counts);
}

echo "current  : ".report($db, $tables)."\n";

// ---------------------------------------------------------------- safety copy first

$safetyDir = rtrim(getenv('CRM_BACKUP_DIR') ?: 'D:/Company/I-TECH-MEDIA/CRM-BACKUPS', '/').'/pre-restore-'.date('Ymd-His');
mkdir($safetyDir, 0777, true);

if (!run(escapeshellarg($mysqldump).' --defaults-extra-file='.escapeshellarg($tmp).
    ' --single-transaction --routines --triggers --databases '.escapeshellarg($db).
    ' --result-file='.escapeshellarg($safetyDir.'/database.sql'), 'safety dump')) {
    fwrite(STDERR, "Stopped: the current database could not be dumped, so nothing was changed.\n");
    exit(1);
}

copy($root.'/.env', $safetyDir.'/env.txt');
echo 'safety copy: '.$safetyDir.' ('.number_format(filesize($safetyDir.'/database.sql'))." bytes)\n";

// ---------------------------------------------------------------- drop, create, import

$schema = DB::selectOne('SELECT DEFAULT_CHARACTER_SET_NAME AS cs, DEFAULT_COLLATION_NAME AS col FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$db])
    ?? (object) ['cs' => 'utf8mb4', 'col' => 'utf8mb4_unicode_ci'];

if (!run(escapeshellarg($mysql).' --defaults-extra-file='.escapeshellarg($tmp).' -e '.
    escapeshellarg("DROP DATABASE IF EXISTS `$db`; CREATE DATABASE `$db` CHARACTER SET {$schema->cs} COLLATE {$schema->col};"), 'recreate')) {
    fwrite(STDERR, "Stopped: $db could not be recreated. Recover the old state from $safetyDir/database.sql\n");
    exit(1);
}

if (!run(escapeshellarg($mysql).' --defaults-extra-file='.escapeshellarg($tmp).' '.escapeshellarg($db).
    ' < '.escapeshellarg($dumpFile), 'import')) {
    fwrite(STDERR, "Import reported an error. $safetyDir/database.sql still holds the pre-restore state.\n");
}

// ---------------------------------------------------------------- bring the schema up to date

if (in_array('migrations', array_map(fn ($r) => $r->TABLE_NAME, DB::select("SHOW TABLES FROM `$db`")), true)) {
    $batch = DB::table('migrations')->max('batch') ?? 0;
    echo "restored schema is at migration batch $batch (".DB::table('migrations')->count()." rows)\n";
    Artisan::call('migrate', ['--force' => true]);
    echo trim(Artisan::output())."\n";
} else {
    echo "No migrations table in the dump - run php artisan migrate --force yourself.\n";
}

// Sessions and queued jobs describing the replaced data must not survive.
foreach (['sessions', 'jobs', 'failed_jobs'] as $t) {
    try {
        DB::statement("TRUNCATE TABLE `$t`");
    } catch (Throwable $e) {
        // Table may not exist in older dumps.
    }
}

@unlink($tmp);

echo 'restored : '.report($db, $tables)."\n";
echo "campus work still needed: students.campus_id and student_courses.campus_id are empty in this\n";
echo "dump, so use Students -> All campuses -> tick -> Bulk Actions -> Set campus, and give users 4/5\n";
echo "their campus again under Settings -> Users. Then: php artisan config:clear && php artisan queue:restart\n";
