<?php

namespace App\Http\Controllers;

use App\Models\AlumniEvent;
use App\Models\Campus;
use App\Models\CommunicationLog;
use App\Models\Course;
use App\Models\EmploymentHistory;
use App\Models\FollowUpTask;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $base = fn () => Student::query()->where('record_status', 'active');

        $stats = [
            'total' => $base()->count(),
            'current_students' => $base()->where('student_stage', 'current_student')->count(),
            'alumni' => $base()->where('student_stage', 'alumni')->count(),
            'graduates' => $base()->where('graduation_status', 'graduated')->count(),
            'dropouts' => $base()->where('student_stage', 'dropped_out')->count(),
            'campuses' => Campus::where('is_active', true)->count(),
            'courses' => Course::where('is_active', true)->count(),
            'income' => (float) Payment::sum('amount'),
            'active' => $base()->where('status', 'active_alumni')->count(),
            'inactive' => $base()->where('status', 'inactive')->count(),
            'unreachable' => $base()->where('status', 'unreachable')->count(),
            'do_not_contact' => $base()->where('status', 'do_not_contact')->count(),
            'studying' => $base()->where('is_currently_studying', true)->count(),
            'employed' => $base()->where('is_currently_employed', true)->count(),
            'seeking' => $base()->where('is_seeking_employment', true)->count(),
        ];

        // Graduation years distribution
        $byGradYear = StudentCourse::query()
            ->selectRaw('graduation_year, COUNT(DISTINCT student_id) as total')
            ->whereNotNull('graduation_year')
            ->groupBy('graduation_year')
            ->orderBy('graduation_year')
            ->pluck('total', 'graduation_year');

        // Course distribution
        $byCourse = StudentCourse::query()
            ->join('courses', 'courses.id', '=', 'student_courses.course_id')
            ->selectRaw('courses.name, COUNT(DISTINCT student_courses.student_id) as total')
            ->groupBy('courses.name')
            ->orderByDesc('total')
            ->pluck('total', 'name');

        // Employment status distribution
        $byEmployment = $base()
            ->selectRaw('employment_status, COUNT(*) as total')
            ->whereNotNull('employment_status')
            ->groupBy('employment_status')
            ->pluck('total', 'employment_status');

        // Engagement distribution
        $byEngagement = $base()
            ->selectRaw('engagement_status, COUNT(*) as total')
            ->whereNotNull('engagement_status')
            ->groupBy('engagement_status')
            ->pluck('total', 'engagement_status');

        $taskQuery = FollowUpTask::query()->with('student:id,name_english,student_code,photo')
            ->whereIn('status', ['pending', 'in_progress']);
        if (!$user->isAdmin()) {
            $taskQuery->where('assigned_to', $user->id);
        }
        $upcomingTasks = $taskQuery->orderBy('due_date')->limit(8)->get();
        $overdueCount = (clone $taskQuery)->whereDate('due_date', '<', now())->count();

        $recentLogs = CommunicationLog::query()
            ->with(['student:id,name_english,student_code,photo', 'user:id,name'])
            ->latest('contact_datetime')
            ->limit(8)
            ->get();

        $upcomingEvents = AlumniEvent::query()
            ->whereIn('status', ['published', 'ongoing'])
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit(5)
            ->get();

        $recentEnrollments = collect();
        if ($user->canManageStudents()) {
            $recentEnrollments = StudentCourse::query()
                ->with(['student:id,name_english,name_khmer,student_code,photo', 'course:id,name'])
                ->whereNotNull('enrollment_date')
                ->orderByDesc('enrollment_date')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'stats', 'byGradYear', 'byCourse', 'byEmployment', 'byEngagement',
            'upcomingTasks', 'overdueCount', 'recentLogs', 'upcomingEvents', 'recentEnrollments'
        ));
    }
}
