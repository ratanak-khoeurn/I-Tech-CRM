<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add student stage
        |--------------------------------------------------------------------------
        */

        Schema::table('students', function (Blueprint $table) {
            $table->string('student_stage', 30)
                ->default('current_student')
                ->after('status');
        });

        // Set graduated students.
        DB::table('students')
            ->where('education_status', 'graduated')
            ->update(['student_stage' => 'graduated']);

        // Set alumni who are no longer studying.
        DB::table('students')
            ->where('status', 'active_alumni')
            ->where(function ($query) {
                $query->whereNull('education_status')
                    ->orWhere('education_status', '!=', 'graduated');
            })
            ->where('is_currently_studying', false)
            ->update(['student_stage' => 'alumni']);

        // Add index after the column exists and data is updated.
        Schema::table('students', function (Blueprint $table) {
            $table->index(
                ['student_stage', 'record_status'],
                'students_stage_record_status_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Create school clubs
        |--------------------------------------------------------------------------
        */

        Schema::create('school_clubs', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('code', 50)->nullable()->unique();

            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('billing_period', 50)->nullable();

            $table->unsignedInteger('capacity')->nullable();

            $table->string('location')->nullable();
            $table->text('schedule')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();

            $table->string('coach_name')->nullable();
            $table->string('coach_email')->nullable();
            $table->string('coach_phone', 30)->nullable();

            $table->string('image')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Create school club student pivot table
        |--------------------------------------------------------------------------
        */

        Schema::create('school_club_student', function (Blueprint $table) {
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('school_club_id')
                ->constrained('school_clubs')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['student_id', 'school_club_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_club_student');
        Schema::dropIfExists('school_clubs');

        if (Schema::hasColumn('students', 'student_stage')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex('students_stage_record_status_index');
                $table->dropColumn('student_stage');
            });
        }
    }
};
