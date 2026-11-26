<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 40)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('code')->constrained()->nullOnDelete();
            $table->dropIndex(['program', 'course_category']);
        });

        $legacyPrograms = DB::table('courses')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->distinct()
            ->pluck('program');

        foreach ($legacyPrograms as $name) {
            $programId = DB::table('programs')->insertGetId([
                'name' => $name,
                'code' => 'PRG-' . strtoupper(substr(sha1(mb_strtolower($name)), 0, 12)),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('courses')->where('program', $name)->update(['program_id' => $programId]);
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('program');
            $table->index(['program_id', 'course_category']);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['program_id', 'course_category']);
            $table->string('program', 120)->nullable()->after('code');
        });

        DB::table('courses')
            ->join('programs', 'programs.id', '=', 'courses.program_id')
            ->update(['courses.program' => DB::raw('programs.name')]);

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
            $table->index(['program', 'course_category']);
        });

        Schema::dropIfExists('programs');
    }
};
