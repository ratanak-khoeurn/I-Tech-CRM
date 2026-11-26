<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intakes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->unsignedSmallInteger('academic_year')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->unsignedSmallInteger('academic_year')->nullable();
            $table->unsignedTinyInteger('term')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 60)->unique();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('intake_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status', 30)->default('upcoming'); // upcoming, ongoing, completed, cancelled
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('student_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type', 40); // enrollment, transfer, batch_change, campus_change, deferral, withdrawal, reinstatement, status_change
            $table->foreignId('from_batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('to_batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('from_campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('to_campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->date('effective_date')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['student_id', 'movement_type']);
        });

        Schema::table('student_courses', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            $table->foreignId('intake_id')->nullable()->after('batch_id')->constrained()->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->after('intake_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['intake_id']);
            $table->dropForeign(['semester_id']);
            $table->dropColumn(['batch_id', 'intake_id', 'semester_id']);
        });
        Schema::dropIfExists('student_movements');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('intakes');
    }
};
