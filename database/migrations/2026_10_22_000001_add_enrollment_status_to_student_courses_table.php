<?php

use App\Models\StudentCourse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->string('enrollment_status', 30)->default('in_progress')->after('course_id');
            $table->index('enrollment_status');
        });

        StudentCourse::query()
            ->whereNotNull('course_completion_date')
            ->orWhereNotNull('graduation_date')
            ->update(['enrollment_status' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropIndex(['enrollment_status']);
            $table->dropColumn('enrollment_status');
        });
    }
};
