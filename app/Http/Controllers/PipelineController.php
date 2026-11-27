<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PipelineController extends Controller
{
    private const COLUMN_LIMIT = 200;

    public function index(Request $request)
    {
        $query = Student::query()->with([
            'assignedStaff:id,name',
            'studentCourses.course:id,name',
            'studentCourses.campus:id,name',
        ])->where('record_status', 'active');

        $filters = $request->only(['course_id', 'graduation_year', 'pipeline_stage']);

        // null means "no campus restriction": an explicit All choice, or a user with no campus assignment.
        $filters['campus_id'] = CampusContext::filterFor($request);

        if (!empty($filters['course_id']) || !empty($filters['campus_id']) || !empty($filters['graduation_year'])) {
            $query->whereHas('studentCourses', function ($w) use ($filters) {
                if (!empty($filters['course_id'])) {
                    $w->where('course_id', $filters['course_id']);
                }
                if (!empty($filters['campus_id'])) {
                    $w->where('campus_id', $filters['campus_id']);
                }
                if (!empty($filters['graduation_year'])) {
                    $w->where('graduation_year', $filters['graduation_year']);
                }
            });
        }

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name_english', 'like', "%$q%")
                    ->orWhere('name_khmer', 'like', "%$q%")
                    ->orWhere('student_code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        $grouped = [];
        $counts = [];
        foreach (array_keys(Student::PIPELINE_LABELS) as $stage) {
            $stageQuery = (clone $query)->where('pipeline_stage', $stage)->orderBy('name_english');
            $counts[$stage] = (clone $stageQuery)->count();
            $grouped[$stage] = $stageQuery->limit(self::COLUMN_LIMIT)->get();
        }

        return view('students.pipeline', [
            'columns' => Student::PIPELINE_LABELS,
            'colors' => Student::PIPELINE_COLORS,
            'grouped' => $grouped,
            'counts' => $counts,
            'columnLimit' => self::COLUMN_LIMIT,
            'canManage' => Auth::user()->canManageStudents(),
            'totalStudents' => array_sum($counts),
            'courses' => Course::where('is_active', true)->orderBy('name')->get(),
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
            'gradYears' => StudentCourse::whereNotNull('graduation_year')->distinct()->orderByDesc('graduation_year')->pluck('graduation_year'),
            'filters' => $filters + ['q' => $request->input('q')],
        ]);
    }

    public function updateStage(Request $request, Student $student)
    {
        abort_unless($request->user()->canManageStudents(), 403);

        $data = $request->validate([
            'pipeline_stage' => ['required', Rule::in(array_keys(Student::PIPELINE_LABELS))],
        ]);

        $from = $student->pipeline_stage;
        if ($from === $data['pipeline_stage']) {
            return response()->json(['ok' => true, 'unchanged' => true]);
        }

        $student->update(['pipeline_stage' => $data['pipeline_stage']]);

        AuditLog::record(
            'update',
            'student',
            $student->id,
            sprintf('Moved %s (%s) on pipeline: %s -> %s', $student->name_english, $student->student_code,
                Student::PIPELINE_LABELS[$from] ?? $from, Student::PIPELINE_LABELS[$data['pipeline_stage']])
        );

        $totals = $this->stageTotals();
        $counts = [];
        foreach (array_keys(Student::PIPELINE_LABELS) as $stage) {
            $counts[$stage] = (int) ($totals[$stage] ?? 0);
        }

        return response()->json(['ok' => true, 'counts' => $counts]);
    }

    private function stageTotals(): array
    {
        return Student::query()
            ->where('record_status', 'active')
            ->selectRaw('pipeline_stage, COUNT(*) as total')
            ->groupBy('pipeline_stage')
            ->pluck('total', 'pipeline_stage')
            ->all();
    }
}
