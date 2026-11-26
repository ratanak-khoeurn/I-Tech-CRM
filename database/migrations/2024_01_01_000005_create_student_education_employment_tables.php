<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // B. Education center information
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('center_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('major')->nullable();
            $table->string('class_group')->nullable();
            $table->string('instructor_name')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->date('course_start_date')->nullable();
            $table->date('course_completion_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->decimal('attendance_percentage', 5, 2)->nullable();
            $table->string('grade', 20)->nullable();
            $table->boolean('certificate_received')->default(false);
            $table->string('certificate_number')->nullable();
            $table->string('scholarship_status', 100)->nullable();
            $table->boolean('internship_participation')->default(false);
            $table->string('internship_company')->nullable();
            $table->string('graduation_project')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->string('alumni_membership_status', 50)->nullable();
            $table->date('alumni_membership_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('graduation_year');
        });

        // C. Current / further education
        Schema::create('education_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_current')->default(false);
            $table->string('institution')->nullable();
            $table->string('degree_program')->nullable();
            $table->string('major')->nullable();
            $table->string('year_level', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_graduation_date')->nullable();
            $table->string('scholarship_info')->nullable();
            $table->text('further_education_plans')->nullable();
            $table->string('status', 30)->nullable(); // studying, graduated, paused, not_studying
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // D. Employment history (current + previous)
        Schema::create('employment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_current')->default(false);
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('department')->nullable();
            $table->string('company_address')->nullable();
            $table->string('work_phone', 50)->nullable();
            $table->string('work_email')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('salary_range')->nullable(); // confidential
            $table->string('employment_type', 30)->nullable(); // full_time, part_time, contract, freelance
            $table->string('employment_status', 30)->nullable(); // employed, self_employed, unemployed, freelancing, internship, student
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_histories');
        Schema::dropIfExists('education_histories');
        Schema::dropIfExists('student_courses');
    }
};
