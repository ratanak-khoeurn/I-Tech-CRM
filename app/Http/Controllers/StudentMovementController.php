<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\StudentMovement;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentMovement::query()
            ->with(['student', 'fromBatch', 'toBatch', 'fromCampus', 'toCampus', 'creator'])
            ->orderByDesc('effective_date')->orderByDesc('id');

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }
        if ($request->filled('from')) {
            $query->whereDate('effective_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('effective_date', '<=', $request->input('to'));
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->whereHas('student', function ($w) use ($q) {
                $w->where('name_english', 'like', "%$q%")
                    ->orWhere('name_khmer', 'like', "%$q%")
                    ->orWhere('student_code', 'like', "%$q%");
            });
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('academic.movements', [
            'movements' => $movements,
            'types' => StudentMovement::TYPES,
            'students' => Student::orderBy('name_english')->limit(500)->get(['id', 'name_english', 'student_code']),
        ]);
    }

    public function create(Request $request)
    {
        $student = $request->filled('student_id') ? Student::find($request->input('student_id')) : null;

        return view('academic.movement-form', [
            'student' => $student,
            'students' => Student::orderBy('name_english')->limit(500)->get(['id', 'name_english', 'student_code']),
            'batches' => Batch::orderBy('name')->get(['id', 'name', 'code', 'course_id', 'campus_id']),
            'campuses' => Campus::orderBy('name')->get(['id', 'name']),
            'types' => StudentMovement::TYPES,
            'statuses' => Student::STATUS_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'movement_type' => ['required', Rule::in(array_keys(StudentMovement::TYPES))],
            'effective_date' => ['nullable', 'date'],
            'to_batch_id' => ['nullable', 'exists:batches,id'],
            'to_campus_id' => CampusContext::campusRules(false),
            'to_status' => ['nullable', Rule::in(array_keys(Student::STATUS_LABELS))],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var Student $student */
        $student = Student::findOrFail($data['student_id']);
        $type = $data['movement_type'];
        $current = $student->latestStudentCourse();

        // Rule::exists reads the table directly, so the scoped Batch query has to confirm
        // the destination batch belongs to this account's campuses.
        if (!empty($data['to_batch_id']) && !Batch::whereKey($data['to_batch_id'])->exists()) {
            return back()->withInput()->withErrors([
                'to_batch_id' => 'That batch belongs to a campus outside your account.',
            ]);
        }

        $movement = new StudentMovement($data);
        $movement->effective_date = $data['effective_date'] ?? now()->toDateString();
        $movement->from_status = $student->status;
        $movement->from_batch_id = $current?->batch_id;
        $movement->from_campus_id = $current?->campus_id;
        $movement->created_by = Auth::id();

        // Batch/campus changes must target a destination.
        if (in_array($type, ['enrollment', 'batch_change', 'transfer'], true) && empty($data['to_batch_id'])) {
            return back()->withInput()->withErrors(['to_batch_id' => 'Select the destination batch for this movement.']);
        }
        if ($type === 'campus_change' && empty($data['to_campus_id'])) {
            return back()->withInput()->withErrors(['to_campus_id' => 'Select the destination campus for this movement.']);
        }
        if (in_array($type, ['withdrawal', 'deferral', 'reinstatement', 'status_change'], true) && empty($data['to_status'])) {
            $movement->to_status = match ($type) {
                'withdrawal', 'deferral' => 'inactive',
                'reinstatement' => 'active_alumni',
                default => $student->status,
            };
        }

        $movement->save();
        $this->apply($student, $movement, $current);

        AuditLog::record('created', 'student_movement', $movement->id,
            "Recorded '{$movement->typeLabel()}' movement for student #{$student->id}");

        return redirect()->route('student-movements.index')->with('success', 'Student movement recorded.');
    }

    public function destroy(StudentMovement $movement)
    {
        $movement->delete();
        AuditLog::record('deleted', 'student_movement', $movement->id, 'Deleted a student movement record');

        return back()->with('success', 'Movement record deleted.');
    }

    /** Apply the movement's effects to the student's enrollment and status. */
    private function apply(Student $student, StudentMovement $movement, ?StudentCourse $current): void
    {
        if ($movement->to_batch_id) {
            $batch = Batch::find($movement->to_batch_id);
            if ($batch) {
                if (!$current) {
                    $current = new StudentCourse([
                        'student_id' => $student->id,
                        'center_name' => \App\Models\Setting::get('center_name', 'Education Center'),
                        'enrollment_date' => $movement->effective_date,
                    ]);
                }
                $current->batch_id = $batch->id;
                $current->course_id = $batch->course_id ?? $current->course_id;
                $current->campus_id = $batch->campus_id ?? $movement->to_campus_id ?? $current->campus_id;
                $current->intake_id = $batch->intake_id ?? $current->intake_id;
                $current->semester_id = $batch->semester_id ?? $current->semester_id;
                $current->save();

                if ($current->campus_id && $student->campus_id === null) {
                    $student->forceFill(['campus_id' => $current->campus_id])->save();
                }
            }
        } elseif ($movement->to_campus_id && $current) {
            $current->campus_id = $movement->to_campus_id;
            $current->save();
        }

        // A campus move follows the student, otherwise they stay on the old campus list.
        if ($movement->to_campus_id && $student->campus_id !== (int) $movement->to_campus_id) {
            $student->forceFill(['campus_id' => (int) $movement->to_campus_id])->save();
        }

        if ($movement->to_status && $movement->to_status !== $student->status) {
            $student->status = $movement->to_status;
            if ($movement->movement_type === 'withdrawal') {
                $student->engagement_notes = trim($student->engagement_notes . "\n" .
                    '[' . $movement->effective_date->format('Y-m-d') . '] Withdrawal: ' . ($movement->reason ?: 'n/a'));
            }
            $student->save();
        }
    }
}
