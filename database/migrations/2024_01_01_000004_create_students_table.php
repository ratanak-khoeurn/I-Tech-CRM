<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            // A. Basic personal information
            $table->string('student_code', 30)->unique();
            $table->string('photo')->nullable();
            $table->string('name_khmer')->nullable();
            $table->string('name_english');
            $table->string('name_chinese')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('national_id')->nullable(); // restricted access
            $table->string('marital_status', 30)->nullable();
            $table->string('phone', 50);
            $table->string('telegram', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('address')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone', 50)->nullable();
            $table->string('preferred_channel', 50)->nullable();
            $table->string('preferred_language', 50)->nullable();
            $table->date('registered_date')->nullable();
            $table->enum('status', ['active_alumni', 'inactive', 'unreachable', 'do_not_contact'])->default('active_alumni');

            // Quick status flags used by dashboard/reports
            $table->string('education_status', 30)->nullable(); // studying, graduated, paused, not_studying
            $table->string('employment_status', 30)->nullable(); // employed, self_employed, unemployed, freelancing, internship, student
            $table->boolean('is_currently_studying')->default(false);
            $table->boolean('is_currently_employed')->default(false);
            $table->boolean('is_seeking_employment')->default(false);

            // D. Career snapshot (history in employment_histories)
            $table->text('professional_skills')->nullable();
            $table->text('technical_skills')->nullable();
            $table->text('certifications')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('cv_path')->nullable();
            $table->text('career_goals')->nullable();
            $table->boolean('interested_jobs')->default(false);
            $table->boolean('interested_internships')->default(false);
            $table->boolean('interested_mentoring')->default(false);
            $table->boolean('interested_trainer')->default(false);
            $table->boolean('interested_volunteering')->default(false);
            $table->text('career_notes')->nullable();

            // F. Alumni engagement
            $table->string('alumni_membership_number', 50)->nullable();
            $table->string('alumni_group')->nullable();
            $table->boolean('interested_activities')->default(false);
            $table->boolean('interested_newsletter')->default(false);
            $table->boolean('interested_job_announcements')->default(false);
            $table->boolean('interested_training')->default(false);
            $table->date('last_contacted_date')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->string('engagement_status', 50)->nullable(); // engaged, semi_engaged, not_engaged
            $table->text('referral_info')->nullable();
            $table->text('alumni_feedback')->nullable();
            $table->text('engagement_notes')->nullable();

            // G. System information
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('record_status', ['active', 'archived'])->default('active');
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'record_status']);
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
