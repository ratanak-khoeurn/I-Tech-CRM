<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_groups', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->after('code')->constrained('campuses')->nullOnDelete();
            $table->foreignId('course_category_id')->nullable()->after('campus_id')->constrained('course_categories')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->after('course_category_id')->constrained('academic_years')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->after('academic_year_id')->constrained('programs')->nullOnDelete();
            $table->foreignId('intake_id')->nullable()->after('program_id')->constrained('intakes')->nullOnDelete();
            $table->boolean('all_courses')->default(true)->after('intake_id');
        });

        Schema::create('course_study_group', function (Blueprint $table) {
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['study_group_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_study_group');

        Schema::table('study_groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('intake_id');
            $table->dropConstrainedForeignId('program_id');
            $table->dropConstrainedForeignId('academic_year_id');
            $table->dropConstrainedForeignId('course_category_id');
            $table->dropConstrainedForeignId('campus_id');
            $table->dropColumn('all_courses');
        });
    }
};
