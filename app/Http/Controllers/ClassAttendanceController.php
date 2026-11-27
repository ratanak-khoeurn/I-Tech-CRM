<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\ClassAttendance;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\StudyGroup;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * The class register: pick a class, tick each student present / absent / late / excused.
 *
 * This is separate from the biometric punch log in attendance_records — a student attends several
 * classes a day, so each class keeps its own mark for the day.
 */
class ClassAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->dateOf($request);
        $studyGroups = StudyGroup::orderBy('name')->get(['id', 'name', 'code', 'study_time', 'campus_id']);
        $studyGroup = $studyGroups->firstWhere('id', (int) $request->query('study_group_id')) ?? $studyGroups->first();

        $students = $studyGroup
            ? Student::whereIn('id', $this->rosterIds($studyGroup)->all() ?: [0])
                ->orderBy('name_english')
                ->get(['id', 'name_english', 'name_khmer', 'student_code'])
            : collect();

        $marks = $studyGroup
            ? ClassAttendance::where('study_group_id', $studyGroup->id)->whereDate('attendance_date', $date)->get()->keyBy('student_id')
            : collect();

        // The face/finger check-in of the same day is shown as a hint, never as a status.
        $scans = AttendanceRecord::whereIn('student_id', $students->pluck('id')->all() ?: [0])
            ->whereDate('attendance_date', $date)
            ->get(['student_id', 'check_in_at'])
            ->keyBy('student_id');

        $counts = collect(ClassAttendance::STATUSES)
            ->mapWithKeys(fn ($label, $status) => [$status => $marks->where('status', $status)->count()]);

        return view('attendance.class', [
            'studyGroups' => $studyGroups,
            'studyGroup' => $studyGroup,
            'students' => $students,
            'marks' => $marks,
            'scans' => $scans,
            'counts' => $counts,
            'date' => $date,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'study_group_id' => ['required', 'integer', Rule::exists('study_groups', 'id')],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.status' => ['required', Rule::in(array_keys(ClassAttendance::STATUSES))],
            'marks.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $studyGroup = StudyGroup::findOrFail($data['study_group_id']);
        CampusContext::authorize($studyGroup->campus_id);

        $date = Carbon::parse($data['attendance_date'])->toDateString();
        // Only students the register actually showed may be written: the roster, narrowed by the
        // account's campus boundary.
        $roster = $this->rosterIds($studyGroup);
        $memberIds = Student::whereIn('id', $roster->all() ?: [0])->pluck('id')->all();
        $marked = array_intersect_key($data['marks'], array_flip($memberIds));

        if (! $marked) {
            return back()->with('error', 'None of the saved rows belong to that class. Reload the register and try again.');
        }

        DB::transaction(function () use ($studyGroup, $date, $marked) {
            foreach ($marked as $studentId => $mark) {
                ClassAttendance::updateOrCreate(
                    [
                        'study_group_id' => $studyGroup->id,
                        'student_id' => $studentId,
                        'attendance_date' => $date,
                    ],
                    [
                        'campus_id' => $studyGroup->campus_id,
                        'status' => $mark['status'],
                        'note' => trim((string) ($mark['note'] ?? '')) ?: null,
                        'marked_by' => Auth::id(),
                    ],
                );
            }
        });

        $summary = collect(ClassAttendance::STATUSES)
            ->mapWithKeys(fn ($label, $status) => [
                $label => collect($marked)->where('status', $status)->count(),
            ])->filter()->keys()->implode(', ');

        AuditLog::record('updated', 'class_attendance', $studyGroup->id, "Marked attendance for {$studyGroup->name} on {$date} (" . count($marked) . " student(s): {$summary})");

        return redirect()
            ->route('attendance.class', ['study_group_id' => $studyGroup->id, 'date' => $date])
            ->with('success', 'Attendance saved for ' . count($marked) . ' student(s) of ' . $studyGroup->name . '.');
    }

    /**
     * Every student the class is made of.
     *
     * A class is populated two ways here — direct group membership and an enrollment that names
     * the group — and the register has to offer both, or a teacher sees an empty roster.
     *
     * @return Collection<int, int>
     */
    private function rosterIds(StudyGroup $studyGroup): Collection
    {
        return $studyGroup->students()->pluck('students.id')
            ->merge(StudentCourse::where('study_group_id', $studyGroup->id)->pluck('student_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function dateOf(Request $request): string
    {
        $date = (string) $request->input('date', now()->toDateString());

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }
}
