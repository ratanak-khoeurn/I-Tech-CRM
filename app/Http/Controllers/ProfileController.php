<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\FollowUpTask;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('role');

        $openTasks = FollowUpTask::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress']);

        $stats = [
            'assigned_students' => Student::where('assigned_staff_id', $user->id)
                ->where('record_status', 'active')->count(),
            'open_tasks' => (clone $openTasks)->count(),
            'overdue_tasks' => (clone $openTasks)->whereDate('due_date', '<', now())->count(),
            'communications' => CommunicationLog::where('user_id', $user->id)->count(),
            'documents' => StudentDocument::where('uploaded_by', $user->id)->count(),
            'invoices' => Invoice::where('created_by', $user->id)->count(),
            'payments' => Payment::where('received_by', $user->id)->count(),
            'unread_notifications' => $user->notifications()->where('is_read', false)->count(),
        ];

        return view('profile.show', compact('user', 'stats'));
    }
}
