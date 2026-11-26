<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_face_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->unique()
                ->constrained('students')
                ->cascadeOnDelete();

            // Laravel encrypts this value through the model cast.
            $table->longText('embedding');

            $table->string('model_version', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('consent_obtained_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_face_profiles');
    }
};
