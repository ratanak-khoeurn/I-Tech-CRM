<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_kh')->nullable();

            $table->foreignId('program_id')
                ->nullable()
                ->constrained('programs')
                ->nullOnDelete();

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->nullOnDelete();
            $table->unsignedBigInteger('academic_year_id')->nullable();

            $table->integer('sort_order')->default(0);

            $table->string('name_import')->nullable();

            $table->boolean('extra_course')->default(false);
            $table->boolean('penalty')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
