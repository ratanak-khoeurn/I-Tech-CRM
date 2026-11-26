<?php

namespace App\Http\Controllers;

use App\Models\AlumniEvent;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AlumniEventController extends Controller
{
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_type' => ['required', Rule::in(array_keys(AlumniEvent::TYPES))],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['nullable', 'date', 'after_or_equal:start_datetime'],
            'location' => ['nullable', 'string', 'max:255'],
            'registration_deadline' => ['nullable', 'date'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'target_group' => ['nullable', 'string', 'max:255'],
            'feedback' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published', 'ongoing', 'completed', 'cancelled'])],
        ];
    }

    public function index(Request $request)
    {
        $query = AlumniEvent::query()->withCount('registrations');
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('title', 'like', "%$q%")->orWhere('location', 'like', "%$q%"));
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $events = $query->orderByDesc('start_datetime')->paginate(10)->withQueryString();

        return view('events.index', compact('events'));
    }

    public function create()
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        return view('events.form', ['event' => new AlumniEvent(['status' => 'draft'])]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());
        $data['created_by'] = Auth::id();
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('events', 'public');
        }
        $event = AlumniEvent::create($data);
        AuditLog::record('created', 'alumni_event', $event->id, "Created event '{$event->title}'");

        $this->notifyAboutEvent($event);

        return redirect()->route('events.show', $event)->with('success', 'Event created successfully.');
    }

    public function show(AlumniEvent $event)
    {
        $event->load(['registrations.student:id,name_english,student_code,photo', 'creator:id,name']);
        $registeredIds = $event->registrations->pluck('student_id')->all();
        $availableStudents = Student::where('record_status', 'active')
            ->whereNotIn('id', $registeredIds)
            ->orderBy('name_english')
            ->get(['id', 'name_english', 'student_code']);

        return view('events.show', compact('event', 'availableStudents'));
    }

    public function edit(AlumniEvent $event)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        return view('events.form', compact('event'));
    }

    public function update(Request $request, AlumniEvent $event)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate($this->rules());
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('events', 'public');
        } else {
            unset($data['photo']);
        }
        $event->update($data);
        AuditLog::record('updated', 'alumni_event', $event->id, "Updated event '{$event->title}'");

        return redirect()->route('events.show', $event)->with('success', 'Event updated.');
    }

    public function destroy(AlumniEvent $event)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $event->delete();
        AuditLog::record('deleted', 'alumni_event', $event->id, "Deleted event '{$event->title}'");
        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    private function notifyAboutEvent(AlumniEvent $event): void
    {
        if ($event->status !== 'published') {
            return;
        }
        foreach (User::where('is_active', true)->get() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'upcoming_event',
                'title' => 'New alumni event published',
                'message' => $event->title . ' — ' . $event->start_datetime->format('M d, Y H:i'),
                'link' => route('events.show', $event),
            ]);
        }
    }
}
