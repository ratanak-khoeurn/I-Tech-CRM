<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Intake;
use App\Models\Semester;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::query()->with(['course', 'campus', 'intake', 'semester'])
            ->withCount('studentCourses')->orderByDesc('start_date')->orderBy('name');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }
        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->input('campus_id'));
        }
        if ($request->filled('intake_id')) {
            $query->where('intake_id', $request->input('intake_id'));
        }
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->input('semester_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%");
            });
        }

        $batches = $query->paginate(15)->withQueryString();

        return view('academic.batches', [
            'batches' => $batches,
            'courses' => Course::orderBy('name')->get(['id', 'name']),
            'campuses' => Campus::orderBy('name')->get(['id', 'name']),
            'intakes' => Intake::orderByDesc('academic_year')->orderBy('name')->get(['id', 'name', 'code']),
            'semesters' => Semester::orderByDesc('academic_year_id')->orderBy('name')->get(['id', 'name', 'code']),
            'statuses' => Batch::STATUSES,
        ]);
    }

    public function create()
    {
        return view('academic.batch-form', $this->formData(new Batch()));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $batch = Batch::create($data);
        AuditLog::record('created', 'batch', $batch->id, "Created batch '{$batch->name}'");

        return redirect()->route('batches.index')->with('success', 'Batch created.');
    }

    public function show(Batch $batch)
    {
        $batch->load(['course', 'campus', 'intake', 'semester']);
        $enrollments = $batch->studentCourses()->with('student')->orderBy('id')->paginate(20);

        return view('academic.batch-show', compact('batch', 'enrollments'));
    }

    public function edit(Batch $batch)
    {
        return view('academic.batch-form', $this->formData($batch));
    }

    public function update(Request $request, Batch $batch)
    {
        $data = $request->validate($this->rules($batch));
        $batch->update($data);
        AuditLog::record('updated', 'batch', $batch->id, "Updated batch '{$batch->name}'");

        return redirect()->route('batches.index')->with('success', 'Batch updated.');
    }

    public function destroy(Batch $batch)
    {
        if ($batch->studentCourses()->exists()) {
            return back()->with('error', 'Cannot delete: students are enrolled in this batch. Move them to another batch first.');
        }
        $batch->delete();
        AuditLog::record('deleted', 'batch', $batch->id, "Deleted batch '{$batch->name}'");

        return back()->with('success', 'Batch deleted.');
    }

    private function formData(Batch $batch): array
    {
        return [
            'batch' => $batch,
            'courses' => Course::orderBy('name')->get(['id', 'name']),
            'campuses' => Campus::orderBy('name')->get(['id', 'name']),
            'intakes' => Intake::orderByDesc('academic_year')->orderBy('name')->get(),
            'semesters' => Semester::orderByDesc('academic_year_id')->orderBy('name')->get(),
            'statuses' => Batch::STATUSES,
        ];
    }

    private function rules(?Batch $batch = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:60', Rule::unique('batches', 'code')->ignore($batch?->id)],
            'course_id' => ['nullable', 'exists:courses,id'],
            'campus_id' => CampusContext::campusRules(false),
            'intake_id' => ['nullable', 'exists:intakes,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'study_time' => ['nullable', 'string', 'max:80'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(array_keys(Batch::STATUSES))],
            'description' => ['nullable', 'string'],
        ];
    }
}
