<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->foreignId('study_group_id')
                ->nullable()
                ->after('course_id')
                ->constrained('study_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('study_group_id');
        });
    }
};
