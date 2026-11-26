<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index()
    {
        return view('settings.courses', [
            'courses' => Course::with(['program', 'category'])->withCount('studentCourses')->orderBy('name')->paginate(15),
            'programs' => Program::where('is_active', true)->orderBy('name')->get(),
            'categories' => CourseCategory::orderBy('name_en')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', Rule::unique('courses', 'code')],
            'program_id' => ['nullable', 'exists:programs,id'],
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'course_category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'certificate_text' => ['nullable', 'string', 'max:2000'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Course::create($data);
        AuditLog::record('created', 'course', null, "Created course '{$data['name']}'");
        return back()->with('success', 'Course created.');
    }

    public function edit(Course $course)
    {
        $course->load(['program', 'category']);

        return view('settings.course-edit', [
            'course' => $course,
            'programs' => Program::where('is_active', true)->orderBy('name')->get(),
            'categories' => CourseCategory::orderBy('name_en')->get(),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', Rule::unique('courses', 'code')->ignore($course->id)],
            'program_id' => ['nullable', 'exists:programs,id'],
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'course_category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'certificate_text' => ['nullable', 'string', 'max:2000'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $course->update($data);
        AuditLog::record('updated', 'course', $course->id, "Updated course '{$course->name}'");
        return redirect()->route('courses.index')->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        if ($course->studentCourses()->exists()) {
            return back()->with('error', 'Cannot delete: this course has student records. Deactivate it instead.');
        }
        $course->delete();
        AuditLog::record('deleted', 'course', $course->id, "Deleted course '{$course->name}'");
        return back()->with('success', 'Course deleted.');
    }
}
