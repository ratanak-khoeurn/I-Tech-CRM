<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Student;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $routeName = optional($request->route())->getName();
        if ($routeName === 'attendance.index') {
            return redirect()->route('attendance.face', $request->query());
        }
        if ($routeName === 'attendance.finger') {
            $mode = 'finger';
        } elseif ($routeName === 'attendance.face') {
            $mode = 'face';
        } else {
            $mode = in_array($request->input('mode'), ['face', 'finger'], true) ? $request->input('mode') : 'face';
        }
        $query = AttendanceRecord::with(['student', 'employee', 'campus', 'checkedInBy', 'checkedOutBy'])
            ->whereDate('attendance_date', $date)
            ->latest('check_in_at');

        $this->applyCommonFilters($query, $request);

        $records = $query->paginate(30)->withQueryString();
        $campuses = Campus::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $dayRecords = AttendanceRecord::whereDate('attendance_date', $date);

        return view('attendance.index', [
            'records' => $records,
            'campuses' => $campuses,
            'date' => $date,
            'mode' => $mode,
            'checkInsCount' => (clone $dayRecords)->count(),
            'checkedOutCount' => (clone $dayRecords)->whereNotNull('check_out_at')->count(),
        ]);
    }

    public function scan(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'person_type' => ['required', Rule::in(['auto', 'student', 'employee'])],
            'campus_id' => CampusContext::campusRules(false),
        ]);

        $code = trim($data['code']);
        $student = null;
        $employee = null;

        if (in_array($data['person_type'], ['auto', 'student'], true)) {
            $student = Student::query()
                ->where('student_code', $code)
                ->where('record_status', 'active')
                ->first();
        }

        if (in_array($data['person_type'], ['auto', 'employee'], true)) {
            $employee = Employee::query()
                ->where('employee_code', $code)
                ->where('status', 'active')
                ->first();
        }

        if ($data['person_type'] === 'auto' && $student && $employee) {
            return back()->withInput()->withErrors([
                'code' => 'This ID matches both a student and an employee. Select the person type and scan again.',
            ]);
        }

        if (!$student && !$employee) {
            return back()->withInput()->withErrors([
                'code' => 'No active student or employee was found for this ID.',
            ]);
        }

        $now = now();
        $person = $student ?? $employee;

        $result = app(\App\Services\AttendanceRecorder::class)
            ->record($person, $now, Auth::id(), $data['campus_id'] ?? null);

        return redirect()
            ->route('attendance.face', ['date' => $now->toDateString()])
            ->with('attendance_scan', $result);
    }

    public function export(Request $request)
    {
        $query = AttendanceRecord::with(['student', 'employee', 'campus', 'checkedInBy', 'checkedOutBy']);

        if ($request->filled('from') && $request->filled('to')) {
            $from = $request->input('from');
            $to = $request->input('to');
            $query->whereBetween('attendance_date', [$from, $to])
                ->orderBy('attendance_date')
                ->orderBy('check_in_at');
            $suffix = $from . '_to_' . $to;
        } else {
            $date = $request->input('date', now()->toDateString());
            $query->whereDate('attendance_date', $date)->orderBy('check_in_at');
            $suffix = $date;
        }

        $this->applyCommonFilters($query, $request);

        if ((clone $query)->count() === 0) {
            return redirect()
                ->route('attendance.face', $request->only(['date', 'from', 'to', 'person_type', 'campus_id', 'q']))
                ->with('export_empty', 'No attendance records were found for that period and filter. Try a wider date range or clear the person/campus filters.');
        }

        AuditLog::record('export', 'attendance', null, 'Exported attendance to CSV (' . $suffix . ')');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Person', 'ID Code', 'Type', 'Campus', 'Check-in', 'Check-out', 'Checked in by', 'Checked out by', 'Remark']);

            $query->chunk(500, function ($records) use ($out) {
                foreach ($records as $record) {
                    fputcsv($out, [
                        $record->attendance_date?->toDateString(),
                        $record->personName(),
                        $record->personCode(),
                        $record->personTypeLabel(),
                        $record->campus?->name,
                        $record->check_in_at?->format('Y-m-d H:i:s'),
                        $record->check_out_at?->format('Y-m-d H:i:s'),
                        $record->checkedInBy?->name,
                        $record->checkedOutBy?->name,
                        $record->remark,
                    ]);
                }
            });

            fclose($out);
        }, 'attendance-' . $suffix . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function applyCommonFilters($query, Request $request): void
    {
        if ($request->filled('person_type')) {
            $query->whereNotNull($request->input('person_type') === 'student' ? 'student_id' : 'employee_id');
        }

        $campusId = CampusContext::filterFor($request);

        if ($campusId !== null) {
            $query->where('campus_id', $campusId);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($personQuery) use ($search) {
                $personQuery->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('name_english', 'like', "%{$search}%")
                        ->orWhere('name_khmer', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            });
        }
    }
}
