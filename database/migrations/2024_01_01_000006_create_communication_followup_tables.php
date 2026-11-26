<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('contact_datetime');
            $table->string('channel', 50); // phone_call, telegram, email, sms, facebook, in_person, alumni_event, other
            $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
            $table->string('subject');
            $table->text('message_summary')->nullable();
            $table->string('outcome')->nullable();
            $table->boolean('followup_required')->default(false);
            $table->date('next_followup_date')->nullable();
            $table->string('attachment')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index('contact_datetime');
        });

        Schema::create('follow_up_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('communication_log_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->date('due_date');
            $table->boolean('reminder_enabled')->default(false);
            $table->dateTime('reminder_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('result')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up_tasks');
        Schema::dropIfExists('communication_logs');
    }
};
