<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    public function index(Request $request)
    {
        $query = Semester::query()->with(['academicYear.campus', 'academicYear.courseCategory'])->withCount('batches')->orderByDesc('academic_year_id')->orderBy('term');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }
        $semesters = $query->paginate(15)->withQueryString();

        $academicYears = $this->academicYearOptions();

        return view('academic.semesters', compact('semesters', 'academicYears'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);
        $semester = Semester::create($data);
        AuditLog::record('created', 'semester', $semester->id, "Created semester '{$semester->name}'");

        return back()->with('success', 'Semester created.');
    }

    public function edit(Semester $semester)
    {
        $semester->load('academicYear.campus', 'academicYear.courseCategory');
        $academicYears = $this->academicYearOptions($semester->academic_year_id);

        return view('academic.semester-edit', compact('semester', 'academicYears'));
    }

    public function update(Request $request, Semester $semester)
    {
        $data = $request->validate($this->rules($semester));
        $data['is_active'] = $request->boolean('is_active', true);
        $semester->update($data);
        AuditLog::record('updated', 'semester', $semester->id, "Updated semester '{$semester->name}'");

        return redirect()->route('semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(Semester $semester)
    {
        if ($semester->batches()->exists()) {
            return back()->with('error', 'Cannot delete: batches are linked to this semester. Deactivate it instead.');
        }
        $semester->delete();
        AuditLog::record('deleted', 'semester', $semester->id, "Deleted semester '{$semester->name}'");

        return back()->with('success', 'Semester deleted.');
    }

    private function rules(?Semester $semester = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('semesters', 'code')->ignore($semester?->id)],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'term' => ['nullable', 'integer', 'min:1', 'max:12'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
        ];
    }

    private function academicYearOptions(?int $selectedYearId = null)
    {
        $query = AcademicYear::withTrashed()
            ->with(['campus:id,name', 'courseCategory:id,name_en'])
            ->where(fn ($years) => $years->whereNull('deleted_at')->when($selectedYearId, fn ($years) => $years->orWhere('id', $selectedYearId)))
            ->orderByDesc('from');

        return $query->get()->unique('id')->values();
    }
}
