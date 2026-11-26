<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('pipeline_stage', 30)->default('not_contacted')->after('engagement_status');
        });

        // Backfill only from evidence we already have; staff confirms 'enrolled' by dragging.
        DB::statement("UPDATE students SET pipeline_stage = 'contacted'
            WHERE deleted_at IS NULL AND EXISTS (SELECT 1 FROM communication_logs cl WHERE cl.student_id = students.id)");

        DB::statement("UPDATE students SET pipeline_stage = 'in_progress'
            WHERE deleted_at IS NULL AND EXISTS (SELECT 1 FROM follow_up_tasks t
                WHERE t.student_id = students.id AND t.status IN ('pending','in_progress'))");

        DB::statement("UPDATE students SET pipeline_stage = 'unreachable'
            WHERE deleted_at IS NULL AND status IN ('unreachable','do_not_contact')");

        Schema::table('students', function (Blueprint $table) {
            $table->index('pipeline_stage');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['pipeline_stage']);
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('pipeline_stage');
        });
    }
};
