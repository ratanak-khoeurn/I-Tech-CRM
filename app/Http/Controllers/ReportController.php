<?php

namespace App\Http\Controllers;

use App\Models\AlumniEvent;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\CommunicationLog;
use App\Models\Course;
use App\Models\EventRegistration;
use App\Models\FollowUpTask;
use App\Models\OpportunityApplication;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Support\CampusContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /** Hard cap on the attendance detail report; a wider period should be split into sheets. */
    private const ATTENDANCE_MAX_ROWS = 5000;

    public const REPORTS = [
        'graduation_year' => 'Total Alumni by Graduation Year',
        'course_campus' => 'Alumni by Course & Campus',
        'employment_status' => 'Employment Status Distribution',
        'education_status' => 'Current Education Status',
        'industry' => 'Employment by Industry',
        'engagement' => 'Alumni Engagement Rate',
        'attendance' => 'Attendance Detail (per day)',
        'contacted_period' => 'Students Contacted in Period',
        'overdue' => 'Overdue Follow-ups',
        'event_participation' => 'Event Participation',
        'referrals' => 'Opportunity Referrals',
        'completion' => 'Profile Completion Rate',
    ];

    public function index(Request $request)
    {
        return view('reports.index', [
            'reports' => self::REPORTS,
            'courses' => Course::orderBy('name')->get(),
            'campuses' => Campus::orderBy('name')->get(),
            'gradYears' => StudentCourse::whereNotNull('graduation_year')->distinct()->orderByDesc('graduation_year')->pluck('graduation_year'),
        ]);
    }

    public function data(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, self::REPORTS), 404);
        $this->applyCampusDefault($request);
        $rows = $this->buildRows($type, $request);

        return response()->json([
            'title' => self::REPORTS[$type],
            'columns' => $rows['columns'],
            'rows' => $rows['rows'],
        ]);
    }

    public function export(Request $request, string $type, string $format)
    {
        abort_unless(array_key_exists($type, self::REPORTS), 404);
        abort_unless(in_array($format, ['csv', 'excel', 'pdf']), 404);

        $this->applyCampusDefault($request);
        $rows = $this->buildRows($type, $request);
        AuditLog::record('export', 'report', null, "Exported report '{$rows['title']}' as {$format}");

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', [
                'title' => $rows['title'],
                'columns' => $rows['columns'],
                'rows' => $rows['rows'],
                'filters' => $this->filterSummary($request),
            ]);

            // Wide reports (attendance detail) are unreadable in portrait.
            if (count($rows['columns']) > 7) {
                $pdf->setPaper('a4', 'landscape');
            }

            return $pdf->download('report-' . $type . '-' . now()->format('Y-m-d') . '.pdf');
        }

        $filename = 'report-' . $type . '-' . now()->format('Y-m-d-His');
        if ($format === 'excel') {
            // Spreadsheet-compatible HTML table (opens directly in Excel)
            $html = view('reports.excel', ['title' => $rows['title'], 'columns' => $rows['columns'], 'rows' => $rows['rows']])->render();
            return response($html, 200, ['Content-Type' => 'application/vnd.ms-excel'])
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"');
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $rows['columns']);
            foreach ($rows['rows'] as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, $filename . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function filterSummary(Request $request): string
    {
        $parts = [];
        foreach (['date_from' => 'From', 'date_to' => 'To', 'course_id' => 'Course', 'campus_id' => 'Campus', 'graduation_year' => 'Grad Year', 'person_type' => 'Who'] as $k => $lbl) {
            if ($request->filled($k)) {
                $val = $request->input($k);
                if ($k === 'course_id') {
                    $val = Course::find($val)?->name ?? $val;
                }
                if ($k === 'campus_id') {
                    $val = Campus::find($val)?->name ?? $val;
                }
                if ($k === 'person_type') {
                    $val = $val === 'student' ? 'Students' : ($val === 'employee' ? 'Staff' : ucfirst((string) $val));
                }
                $parts[] = "$lbl: $val";
            }
        }
        return $parts ? implode(' | ', $parts) : 'No filters';
    }

    /**
     * Staff see their signed-in campus first, the same way the list pages behave.
     * The Reports filter bar always offers "All", so this stays widen-able.
     */
    private function applyCampusDefault(Request $request): void
    {
        if (($campusId = CampusContext::filterFor($request)) !== null) {
            $request->merge(['campus_id' => $campusId]);
        }
    }

    /**
     * student_id => course name of that student's most recent enrollment.
     *
     * @return array<int, string|null>
     */
    private function latestCourseNames(array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        return StudentCourse::with('course:id,name')
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('enrollment_date')
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->mapWithKeys(fn ($enrollment) => [$enrollment->student_id => $enrollment->course?->name])
            ->all();
    }

    private function applyStudentFilters($query, Request $request): void
    {
        if ($request->filled('course_id') || $request->filled('campus_id') || $request->filled('graduation_year')) {
            $query->whereHas('studentCourses', function ($w) use ($request) {
                if ($request->filled('course_id')) {
                    $w->where('course_id', $request->input('course_id'));
                }
                if ($request->filled('campus_id')) {
                    $w->where('campus_id', $request->input('campus_id'));
                }
                if ($request->filled('graduation_year')) {
                    $w->where('graduation_year', $request->input('graduation_year'));
                }
            });
        }
    }

    private function buildRows(string $type, Request $request): array
    {
        $title = self::REPORTS[$type];
        $columns = [];
        $rows = [];

        switch ($type) {
            case 'graduation_year':
                $columns = ['Graduation Year', 'Alumni Count'];
                $data = StudentCourse::selectRaw('graduation_year, COUNT(DISTINCT student_id) as total')
                    ->whereNotNull('graduation_year')->groupBy('graduation_year')->orderBy('graduation_year')->get();
                foreach ($data as $d) {
                    $rows[] = [$d->graduation_year, $d->total];
                }
                break;

            case 'course_campus':
                $columns = ['Course', 'Campus', 'Alumni Count'];
                $data = StudentCourse::join('courses', 'courses.id', '=', 'student_courses.course_id')
                    ->leftJoin('campuses', 'campuses.id', '=', 'student_courses.campus_id')
                    ->selectRaw('courses.name as course, campuses.name as campus, COUNT(DISTINCT student_courses.student_id) as total')
                    ->groupBy('courses.name', 'campuses.name')->orderBy('courses.name')->get();
                foreach ($data as $d) {
                    $rows[] = [$d->course, $d->campus ?? '—', $d->total];
                }
                break;

            case 'employment_status':
                $columns = ['Employment Status', 'Count', '% of Total'];
                $total = Student::where('record_status', 'active')->count() ?: 1;
                $data = Student::where('record_status', 'active')->selectRaw('employment_status, COUNT(*) as total')
                    ->groupBy('employment_status')->get();
                foreach ($data as $d) {
                    $rows[] = [Student::EMPLOYMENT_STATUS_LABELS[$d->employment_status] ?? 'Unspecified', $d->total, round($d->total / $total * 100, 1) . '%'];
                }
                break;

            case 'education_status':
                $columns = ['Education Status', 'Count', '% of Total'];
                $total = Student::where('record_status', 'active')->count() ?: 1;
                $data = Student::where('record_status', 'active')->selectRaw('education_status, COUNT(*) as total')
                    ->groupBy('education_status')->get();
                foreach ($data as $d) {
                    $rows[] = [Student::EDUCATION_STATUS_LABELS[$d->education_status] ?? 'Unspecified', $d->total, round($d->total / $total * 100, 1) . '%'];
                }
                break;

            case 'industry':
                $columns = ['Industry', 'Alumni Count'];
                $data = \App\Models\EmploymentHistory::where('is_current', true)->whereNotNull('industry')
                    ->selectRaw('industry, COUNT(*) as total')->groupBy('industry')->orderByDesc('total')->get();
                foreach ($data as $d) {
                    $rows[] = [$d->industry, $d->total];
                }
                break;

            case 'engagement':
                $columns = ['Engagement Status', 'Count', '% of Total'];
                $total = Student::where('record_status', 'active')->count() ?: 1;
                $data = Student::where('record_status', 'active')->selectRaw('engagement_status, COUNT(*) as total')
                    ->groupBy('engagement_status')->get();
                foreach ($data as $d) {
                    $rows[] = [ucfirst(str_replace('_', ' ', (string) $d->engagement_status)) ?: 'Unspecified', $d->total, round($d->total / $total * 100, 1) . '%'];
                }
                break;

            case 'attendance':
                $columns = ['Date', 'Person', 'ID Code', 'Type', 'Course', 'Campus', 'Check-in', 'Check-out', 'Hours', 'Remark'];
                $from = $request->input('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : now()->subDays(30)->startOfDay();
                $to = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->startOfDay() : now()->startOfDay();

                $query = AttendanceRecord::query()
                    ->with([
                        'student:id,name_english,name_khmer,student_code',
                        'employee:id,first_name,last_name,employee_code',
                        'campus:id,name',
                    ])
                    ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()]);

                if ($request->filled('campus_id')) {
                    $query->where('campus_id', $request->input('campus_id'));
                }
                if ($request->input('person_type') === 'student') {
                    $query->whereNotNull('student_id');
                } elseif ($request->input('person_type') === 'employee') {
                    $query->whereNotNull('employee_id');
                }
                if ($request->filled('course_id')) {
                    $query->whereHas('student.studentCourses', function ($w) use ($request) {
                        $w->where('course_id', $request->input('course_id'));
                    });
                }

                $records = $query->orderBy('attendance_date')
                    ->orderBy('check_in_at')
                    ->orderBy('id')
                    ->limit(self::ATTENDANCE_MAX_ROWS + 1)
                    ->get();
                $truncated = $records->count() > self::ATTENDANCE_MAX_ROWS;
                $courses = $this->latestCourseNames($records->pluck('student_id')->filter()->unique()->all());

                foreach ($records->take(self::ATTENDANCE_MAX_ROWS) as $record) {
                    $hours = $record->check_in_at && $record->check_out_at
                        ? round(abs($record->check_out_at->diffInMinutes($record->check_in_at)) / 60, 1)
                        : null;

                    $rows[] = [
                        $record->attendance_date?->format('Y-m-d'),
                        $record->personName(),
                        $record->personCode(),
                        $record->personTypeLabel(),
                        $record->student_id ? ($courses[$record->student_id] ?? '—') : '—',
                        $record->campus?->name ?? '—',
                        $record->check_in_at?->format('H:i') ?? '—',
                        $record->check_out_at?->format('H:i') ?? '—',
                        $hours ?? '—',
                        $record->remark ?? '—',
                    ];
                }

                if ($truncated) {
                    $rows[] = ['Only the first ' . number_format(self::ATTENDANCE_MAX_ROWS) . ' rows are listed — narrow the date range or campus for the rest.'];
                }
                break;

            case 'contacted_period':
                $columns = ['Student', 'Student ID', 'Channel', 'Subject', 'Contact Date', 'Staff'];
                $from = $request->input('date_from') ? Carbon::parse($request->input('date_from')) : now()->subDays(30);
                $to = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : now();
                $data = CommunicationLog::with(['student:id,name_english,student_code', 'user:id,name'])
                    ->whereBetween('contact_datetime', [$from, $to])
                    ->latest('contact_datetime')->limit(500)->get();
                foreach ($data as $d) {
                    $rows[] = [$d->student?->name_english, $d->student?->student_code, CommunicationLog::CHANNELS[$d->channel] ?? $d->channel, $d->subject, $d->contact_datetime->format('Y-m-d H:i'), $d->user?->name];
                }
                break;

            case 'overdue':
                $columns = ['Task', 'Student', 'Due Date', 'Priority', 'Assigned To', 'Days Overdue'];
                $data = FollowUpTask::with(['student:id,name_english', 'assignee:id,name'])
                    ->whereIn('status', ['pending', 'in_progress'])->whereDate('due_date', '<', now())
                    ->orderBy('due_date')->get();
                foreach ($data as $d) {
                    $rows[] = [$d->title, $d->student?->name_english, $d->due_date->format('Y-m-d'), ucfirst($d->priority), $d->assignee?->name ?? '—', (int) $d->due_date->diffInDays(now())];
                }
                break;

            case 'event_participation':
                $columns = ['Event', 'Type', 'Date', 'Registered', 'Attended', 'No-show', 'Attendance Rate'];
                $data = AlumniEvent::withCount([
                    'registrations',
                    'registrations as attended_count' => fn ($q) => $q->where('status', 'attended'),
                    'registrations as noshow_count' => fn ($q) => $q->where('status', 'no_show'),
                ])->orderByDesc('start_datetime')->get();
                foreach ($data as $d) {
                    $rate = $d->registrations_count ? round($d->attended_count / $d->registrations_count * 100, 1) . '%' : '—';
                    $rows[] = [$d->title, AlumniEvent::TYPES[$d->event_type] ?? $d->event_type, $d->start_datetime->format('Y-m-d'), $d->registrations_count, $d->attended_count, $d->noshow_count, $d->status === 'completed' || $d->start_datetime->isPast() ? $rate : '—'];
                }
                break;

            case 'referrals':
                $columns = ['Opportunity', 'Type', 'Student', 'Source', 'Status', 'Date'];
                $data = OpportunityApplication::with(['opportunity:id,title,opportunity_type', 'student:id,name_english'])
                    ->latest()->limit(500)->get();
                foreach ($data as $d) {
                    $rows[] = [$d->opportunity?->title, ucfirst((string) $d->opportunity?->opportunity_type), $d->student?->name_english, ucfirst($d->source), ucfirst($d->status), $d->created_at->format('Y-m-d')];
                }
                break;

            case 'completion':
                $columns = ['Student', 'Student ID', 'Completion %', 'Missing Key Fields'];
                $students = Student::query()->where('record_status', 'active');
                $this->applyStudentFilters($students, $request);
                foreach ($students->orderBy('name_english')->limit(500)->get() as $s) {
                    $missing = [];
                    foreach (['name_khmer' => 'Khmer name', 'email' => 'Email', 'telegram' => 'Telegram', 'province' => 'Province',
                        'emergency_contact_name' => 'Emergency contact', 'preferred_language' => 'Language',
                        'education_status' => 'Education status', 'employment_status' => 'Employment status',
                        'alumni_membership_number' => 'Membership no.', 'engagement_status' => 'Engagement status'] as $f => $lbl) {
                        if (empty($s->{$f})) {
                            $missing[] = $lbl;
                        }
                    }
                    $rows[] = [$s->name_english, $s->student_code, $s->completionPercentage() . '%', implode(', ', array_slice($missing, 0, 5)) ?: '—'];
                }
                break;
        }

        return ['title' => $title, 'columns' => $columns, 'rows' => $rows];
    }
}
