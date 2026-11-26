<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('program_id')
                ->constrained('course_categories')->nullOnDelete();
        });

        // Backfill: link a course to a category whose English name matches its legacy string.
        $categories = DB::table('course_categories')->get(['id', 'name_en'])
            ->keyBy(fn ($row) => mb_strtolower($row->name_en));
        DB::table('courses')->whereNotNull('course_category')->where('course_category', '!=', '')
            ->select('id', 'course_category')->chunkById(100, function ($courses) use ($categories) {
                foreach ($courses as $course) {
                    $categoryId = $categories->get(mb_strtolower($course->course_category))?->id;
                    if ($categoryId) {
                        DB::table('courses')->where('id', $course->id)->update(['category_id' => $categoryId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
