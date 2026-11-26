<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('organization')->nullable();
            $table->string('opportunity_type', 50); // job, internship, scholarship, training, volunteer
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('contact_info')->nullable();
            $table->string('application_url')->nullable();
            $table->string('target_course')->nullable();
            $table->string('target_graduation_year')->nullable();
            $table->enum('publication_status', ['draft', 'published', 'closed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('opportunity_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('source', ['matched', 'referred', 'self_applied'])->default('referred');
            $table->enum('status', ['interested', 'applied', 'interviewing', 'accepted', 'rejected', 'withdrawn'])->default('interested');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['opportunity_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_applications');
        Schema::dropIfExists('opportunities');
    }
};
