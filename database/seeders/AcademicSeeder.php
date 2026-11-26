<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Intake;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\StudentMovement;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        if (Intake::count() > 0 || Semester::count() > 0 || Batch::count() > 0) {
            return;
        }
        $courses = Course::orderBy('id')->get();
        $campuses = Campus::orderBy('id')->get();
        if ($courses->isEmpty()) {
            return;
        }
        $year = (int) now()->format('Y');

        // Intakes
        $intakes = [];
        foreach ([['Spring Intake', 'SPR'], ['Summer Intake', 'SUM'], ['Fall Intake', 'FALL']] as $i => [$name, $pfx]) {
            $intakes[] = Intake::create([
                'name' => "$name $year",
                'code' => "$pfx-$year",
                'academic_year' => $year,
                'start_date' => now()->copy()->startOfYear()->addMonths($i * 4),
                'end_date' => now()->copy()->startOfYear()->addMonths($i * 4 + 3),
                'capacity' => 60 + $i * 20,
                'is_active' => $i >= 1,
            ]);
        }

        // Semesters — linked through academic_year_id since 2026_11_02_000002 dropped
        // the old academic_year column.
        $academicYearId = AcademicYear::where(function ($q) use ($year) {
            $q->where('academic_year', 'like', "%$year%")
                ->orWhereBetween('from', ["$year-01-01", "$year-12-31"]);
        })->orderBy('id')->value('id');

        if (!$academicYearId && $campuses->isNotEmpty()) {
            $academicYearId = AcademicYear::create([
                'academic_year' => (string) $year,
                'from' => "$year-01-01",
                'to' => "$year-12-31",
                'campus_id' => $campuses[0]->id,
                'current_year' => true,
            ])->id;
        }

        $semesters = [];
        foreach ([['Semester 1', 1], ['Semester 2', 2]] as $i => [$name, $term]) {
            $semesters[] = Semester::create([
                'name' => "$name $year",
                'code' => "$year-S$term",
                'academic_year_id' => $academicYearId,
                'term' => $term,
                'start_date' => now()->copy()->startOfYear()->addMonths($i * 6),
                'end_date' => now()->copy()->startOfYear()->addMonths($i * 6 + 5),
                'is_active' => $i === 0,
            ]);
        }

        // Batches — one per course, spread across intakes/semesters
        $batches = [];
        foreach ($courses as $i => $course) {
            $slug = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $course->code ?: $course->name), 0, 3));
            $batch = Batch::create([
                'name' => $course->name . ' — Batch ' . $year . '-' . chr(65 + ($i % 26)),
                'code' => "$slug-$year-" . chr(65 + ($i % 26)),
                'course_id' => $course->id,
                'campus_id' => $campuses->isNotEmpty() ? $campuses[$i % $campuses->count()]->id : null,
                'intake_id' => $intakes[$i % count($intakes)]->id,
                'semester_id' => $semesters[$i % count($semesters)]->id,
                'start_date' => now()->copy()->subMonths($i % 6),
                'end_date' => now()->copy()->addMonths(3 + ($i % 4)),
                'capacity' => 25 + ($i % 3) * 5,
                'status' => ['ongoing', 'upcoming', 'completed'][$i % 3],
            ]);
            $batches[] = $batch;
        }

        // Enroll existing student_courses into batches (round-robin)
        $scs = StudentCourse::orderBy('id')->get();
        foreach ($scs as $i => $sc) {
            $batch = $batches[$i % count($batches)];
            $sc->batch_id = $batch->id;
            $sc->intake_id = $sc->intake_id ?? $batch->intake_id;
            $sc->semester_id = $sc->semester_id ?? $batch->semester_id;
            $sc->save();
        }

        // A few enrollment movement logs for students with a batch
        $enrolled = StudentCourse::whereNotNull('batch_id')->with('student')->limit(5)->get();
        foreach ($enrolled as $i => $sc) {
            StudentMovement::create([
                'student_id' => $sc->student_id,
                'movement_type' => 'enrollment',
                'to_batch_id' => $sc->batch_id,
                'to_campus_id' => $sc->campus_id,
                'effective_date' => $sc->enrollment_date ?? now()->subDays(30 - $i),
                'reason' => 'Initial enrollment',
                'created_by' => 1,
            ]);
        }
    }
}
