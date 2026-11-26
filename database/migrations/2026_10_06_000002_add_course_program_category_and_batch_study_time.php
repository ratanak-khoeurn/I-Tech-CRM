<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('program', 120)->nullable()->after('code');
            $table->string('course_category', 120)->nullable()->after('program');
            $table->index(['program', 'course_category']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->string('study_time', 80)->nullable()->after('semester_id');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('study_time');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['program', 'course_category']);
            $table->dropColumn(['program', 'course_category']);
        });
    }
};
