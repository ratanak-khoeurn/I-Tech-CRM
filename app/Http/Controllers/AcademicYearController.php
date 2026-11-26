<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseCategory;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years.
     */
    public function index(Request $request)
    {
        $query = AcademicYear::with(['campus', 'courseCategory']);

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('academic_year', 'like', "%$q%")
                    ->orWhereHas('campus', fn ($c) => $c->where('name', 'like', "%$q%"))
                    ->orWhereHas('courseCategory', fn ($c) => $c->where('name_en', 'like', "%$q%"));
            });
        }
        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->integer('campus_id'));
        }
        if ($request->filled('course_category_id')) {
            $query->where('course_category_id', $request->integer('course_category_id'));
        }
        if ($request->filled('current_year')) {
            $query->where('current_year', $request->boolean('current_year'));
        }

        $academicYears = $query->orderByDesc('from')->paginate(10)->withQueryString();

        // Get campuses
        $campuses = Campus::orderBy('name')->get();

        // Get course categories
        $categories = CourseCategory::orderBy('name_en')->get();

        return view('academic.academic_year', compact(
            'academicYears',
            'campuses',
            'categories'
        ));
    }

    /**
     * Store a new academic year.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'campus_id' => CampusContext::campusRules(),
            'course_category_id' => 'nullable|exists:course_categories,id',
            'academic_year' => 'required|string|max:50',
            'current_year' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated) {

            if (!empty($validated['current_year'])) {
                AcademicYear::where(
                    'campus_id',
                    $validated['campus_id']
                )->update([
                            'current_year' => false
                        ]);
            }

            AcademicYear::create([
                'from' => $validated['from'],
                'to' => $validated['to'],
                'campus_id' => $validated['campus_id'],
                'course_category_id' => $validated['course_category_id'] ?? null,
                'academic_year' => $validated['academic_year'],
                'current_year' => !empty($validated['current_year']),
            ]);
        });

        return back()->with(
            'success',
            'Academic year created successfully.'
        );
    }

    /**
     * Update an academic year.
     */
    public function update(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'campus_id' => CampusContext::campusRules(),
            'course_category_id' => 'nullable|exists:course_categories,id',
            'academic_year' => 'required|string|max:50',
            'current_year' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $academicYear) {

            if (!empty($validated['current_year'])) {
                AcademicYear::where(
                    'campus_id',
                    $validated['campus_id']
                )
                    ->where('id', '!=', $academicYear->id)
                    ->update([
                        'current_year' => false
                    ]);
            }

            $academicYear->update([
                'from' => $validated['from'],
                'to' => $validated['to'],
                'campus_id' => $validated['campus_id'],
                'course_category_id' => $validated['course_category_id'] ?? null,
                'academic_year' => $validated['academic_year'],
                'current_year' => !empty($validated['current_year']),
            ]);
        });

        return back()->with(
            'success',
            'Academic year updated successfully.'
        );
    }

    /**
     * Delete an academic year.
     */
    public function destroy($id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        $academicYear->delete();

        return back()->with(
            'success',
            'Academic year deleted successfully.'
        );
    }
}
