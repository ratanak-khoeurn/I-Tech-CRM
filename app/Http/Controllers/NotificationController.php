<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\Student;
use App\Models\StudentCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = \App\Models\Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Generate in-system notifications based on current data:
     * upcoming follow-ups (3 days), overdue tasks, upcoming events (7 days),
     * and new published opportunities (7 days).
     * NOTE: These are in-system only — no Telegram/Email/SMS/WhatsApp messages
     * are sent unless those services are configured and connected.
     */
    public function generate()
    {
        $user = Auth::user();
        $created = 0;

        $tasks = \App\Models\FollowUpTask::with('student:id,name_english')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function ($w) use ($user) {
                $w->where('assigned_to', $user->id);
                if ($user->isAdmin()) {
                    $w->orWhereNull('assigned_to');
                }
            })
            ->get();

        foreach ($tasks as $task) {
            if ($task->due_date->isPast()) {
                $created += $this->notifyOnce($user->id, 'overdue_task', $task->id,
                    'Overdue: ' . $task->title,
                    ($task->student?->name_english ?? 'Student') . ' — was due ' . $task->due_date->format('M d, Y'),
                    route('follow-ups.index', ['filter' => 'overdue']));
            } elseif ($task->due_date->diffInDays(now(), false) <= 3) {
                $created += $this->notifyOnce($user->id, 'followup_reminder', $task->id,
                    'Upcoming follow-up: ' . $task->title,
                    ($task->student?->name_english ?? 'Student') . ' — due ' . $task->due_date->format('M d, Y'),
                    route('students.show', [$task->student_id, 'tab' => 'tasks']));
            }
        }

        $events = \App\Models\AlumniEvent::where('status', 'published')
            ->whereBetween('start_datetime', [now(), now()->addDays(7)])->get();
        foreach ($events as $event) {
            $created += $this->notifyOnce($user->id, 'upcoming_event', $event->id,
                'Upcoming event: ' . $event->title,
                $event->start_datetime->format('M d, Y H:i') . ' — ' . ($event->location ?: 'TBA'),
                route('events.show', $event));
        }

        $opps = \App\Models\Opportunity::where('publication_status', 'published')
            ->where('created_at', '>=', now()->subDays(7))->get();
        foreach ($opps as $opp) {
            $created += $this->notifyOnce($user->id, 'new_opportunity', $opp->id,
                'New opportunity: ' . $opp->title,
                ($opp->organization ?: '') . ' — deadline ' . ($opp->application_deadline?->format('M d, Y') ?? 'N/A'),
                route('opportunities.show', $opp));
        }

        return back()->with('success', $created > 0
            ? "$created new notification(s) generated."
            : 'No new notifications to generate — you are up to date.');
    }

    private function notifyOnce(int $userId, string $type, int $entityId, string $title, string $message, string $link): int
    {
        $existing = \App\Models\Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('title', $title)
            ->where('created_at', '>=', now()->subDay())
            ->exists();
        if ($existing) {
            return 0;
        }
        \App\Models\Notification::create([
            'user_id' => $userId, 'type' => $type, 'title' => $title,
            'message' => $message, 'link' => $link,
        ]);
        return 1;
    }

    public function markRead(\App\Models\Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);
        $notification->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
