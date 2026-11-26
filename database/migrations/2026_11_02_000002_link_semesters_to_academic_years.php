<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('code')
                ->constrained('academic_years')
                ->nullOnDelete();
        });

        $academicYears = DB::table('academic_years')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'academic_year', 'from']);

        foreach (DB::table('semesters')->whereNotNull('academic_year')->get(['id', 'academic_year']) as $semester) {
            $legacyYear = (string) $semester->academic_year;
            $academicYear = $academicYears->first(function ($candidate) use ($legacyYear) {
                return preg_match('/\\b' . preg_quote($legacyYear, '/') . '\\b/', (string) $candidate->academic_year)
                    || substr((string) $candidate->from, 0, 4) === $legacyYear;
            });

            if ($academicYear) {
                DB::table('semesters')->where('id', $semester->id)->update([
                    'academic_year_id' => $academicYear->id,
                ]);
            }
        }

        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->unsignedSmallInteger('academic_year')->nullable();
        });

        foreach (DB::table('semesters')
            ->join('academic_years', 'semesters.academic_year_id', '=', 'academic_years.id')
            ->get(['semesters.id', 'academic_years.academic_year', 'academic_years.from']) as $semester) {
            preg_match('/\\b(19|20|21|22|23|24|25|26|27|28|29)\\d{2}\\b/', (string) $semester->academic_year, $matches);
            $year = $matches[0] ?? substr((string) $semester->from, 0, 4);
            DB::table('semesters')->where('id', $semester->id)->update([
                'academic_year' => (int) $year,
            ]);
        }

        Schema::table('semesters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};
