<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The class register is deliberately its own table: attendance_records is the biometric
        // punch log (one check-in/out pair per student per day, no class link), while a student can
        // attend several classes on the same day and each one is marked present/absent/late/excused.
        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status', 20);
            $table->string('note', 255)->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['study_group_id', 'student_id', 'attendance_date'], 'class_attendances_unique');
            $table->index(['attendance_date', 'study_group_id'], 'class_attendances_date_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_attendances');
    }
};
