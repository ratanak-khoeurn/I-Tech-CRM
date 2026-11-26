<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_groups', function (Blueprint $table) {
            $table->string('study_time', 80)->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('study_groups', function (Blueprint $table) {
            $table->dropColumn('study_time');
        });
    }
};
