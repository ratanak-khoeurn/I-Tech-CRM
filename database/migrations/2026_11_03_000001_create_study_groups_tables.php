<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 60)->nullable()->unique();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('study_group_student', function (Blueprint $table) {
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['study_group_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_group_student');
        Schema::dropIfExists('study_groups');
    }
};
