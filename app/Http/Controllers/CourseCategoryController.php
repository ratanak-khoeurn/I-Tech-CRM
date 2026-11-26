<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseCategory;
use App\Models\Program;
use App\Models\Semester;
use Illuminate\Http\Request;

class CourseCategoryController extends Controller
{
    /**
     * Display course categories.
     */
    public function index()
    {
        // Course categories
        $categories = CourseCategory::with([
            'program',
            'semester',
            'academicYear',
            'academicYears'
        ])
            ->latest()
            ->paginate(10);

        // Programs for dropdown
        $programs = Program::orderBy('name')->get();

        // Semesters for dropdown
        $semesters = Semester::orderBy('name')->get();

        // Academic years for dropdown
        $academicYears = AcademicYear::orderByDesc('from')->get();

        return view('academic.course_category', compact(
            'categories',
            'programs',
            'semesters',
            'academicYears'
        ));
    }

    /**
     * Store a course category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:course_categories,code',
            'name_en' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'program_id' => 'nullable|exists:programs,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'sort_order' => 'nullable|integer|min:0',
            'name_import' => 'nullable|string|max:255',
            'extra_course' => 'nullable|boolean',
            'penalty' => 'nullable|boolean',
        ]);

        CourseCategory::create([
            'code' => $validated['code'],
            'name_en' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'program_id' => $validated['program_id'] ?? null,
            'semester_id' => $validated['semester_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'name_import' => $validated['name_import'] ?? null,
            'extra_course' => $request->boolean('extra_course'),
            'penalty' => $request->boolean('penalty'),
        ]);

        return back()->with(
            'success',
            'Course category created successfully.'
        );
    }

    /**
     * Update a course category.
     */
    public function update(Request $request, $id)
    {
        $category = CourseCategory::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:course_categories,code,' . $category->id,
            'name_en' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'program_id' => 'nullable|exists:programs,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'sort_order' => 'nullable|integer|min:0',
            'name_import' => 'nullable|string|max:255',
            'extra_course' => 'nullable|boolean',
            'penalty' => 'nullable|boolean',
        ]);

        $category->update([
            'code' => $validated['code'],
            'name_en' => $validated['name_en'],
            'name_kh' => $validated['name_kh'] ?? null,
            'program_id' => $validated['program_id'] ?? null,
            'semester_id' => $validated['semester_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'name_import' => $validated['name_import'] ?? null,
            'extra_course' => $request->boolean('extra_course'),
            'penalty' => $request->boolean('penalty'),
        ]);

        return back()->with(
            'success',
            'Course category updated successfully.'
        );
    }

    /**
     * Delete a course category.
     */
    public function destroy($id)
    {
        $category = CourseCategory::findOrFail($id);

        $category->delete();

        return back()->with(
            'success',
            'Course category deleted successfully.'
        );
    }
}
