<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'campus_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('assigned_staff_id')
                    ->constrained('campuses')->nullOnDelete();
            });
        }

        // Home campus = campus of the student's newest enrollment that carries one,
        // either directly or through its batch.
        $rows = DB::table('student_courses')
            ->leftJoin('batches', 'batches.id', '=', 'student_courses.batch_id')
            ->select(['student_courses.student_id', 'student_courses.campus_id', 'batches.campus_id as batch_campus_id'])
            ->orderByDesc('student_courses.enrollment_date')
            ->orderByDesc('student_courses.id')
            ->get();

        foreach ($rows as $row) {
            $campusId = $row->campus_id ?? $row->batch_campus_id;

            if ($campusId !== null) {
                DB::table('students')->where('id', $row->student_id)->whereNull('campus_id')
                    ->update(['campus_id' => $campusId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'campus_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            });
        }
    }
};
