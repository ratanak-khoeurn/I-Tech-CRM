<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('graduation_status', 30)->default('not_graduated')->after('student_stage');
            $table->index(['student_stage', 'graduation_status', 'record_status'], 'students_lifecycle_graduation_index');
        });

        DB::table('students')
            ->where('student_stage', 'graduated')
            ->update([
                'student_stage' => 'alumni',
                'graduation_status' => 'graduated',
            ]);
    }

    public function down(): void
    {
        DB::table('students')
            ->where('student_stage', 'alumni')
            ->where('graduation_status', 'graduated')
            ->update(['student_stage' => 'graduated']);

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_lifecycle_graduation_index');
            $table->dropColumn('graduation_status');
        });
    }
};
