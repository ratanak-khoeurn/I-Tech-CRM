<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationConfirmation;
use App\Models\AlumniEvent;
use App\Models\AuditLog;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventRegistrationController extends Controller
{
    public function store(Request $request, AlumniEvent $event)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        if ($event->registration_deadline && $event->registration_deadline->isPast() && $event->status !== 'completed') {
            return back()->with('error', 'Registration deadline for this event has passed.');
        }
        $seats = $event->seatsLeft();
        if ($seats !== null && $seats <= 0) {
            return back()->with('error', 'This event is full — no seats left.');
        }

        $existing = EventRegistration::where('alumni_event_id', $event->id)->where('student_id', $data['student_id'])->first();
        if ($existing) {
            return back()->with('error', 'This student is already registered for the event.');
        }

        $registration = EventRegistration::create([
            'alumni_event_id' => $event->id,
            'student_id' => $data['student_id'],
            'status' => 'registered',
            'registered_by' => Auth::id(),
        ]);
        AuditLog::record('registered', 'event_registration', $event->id, "Registered student #{$data['student_id']} for event '{$event->title}'");

        $result = $this->sendConfirmation($registration);

        return $this->flashForMailResult($result, 'Student registered', $registration);
    }

    /**
     * Manually (re)send a registration confirmation email.
     */
    public function emailConfirmation(EventRegistration $registration)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);

        $result = $this->sendConfirmation($registration);

        return $this->flashForMailResult($result, 'Confirmation email', $registration);
    }

    public function update(Request $request, EventRegistration $registration)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['registered', 'attended', 'no_show', 'cancelled'])],
            'feedback' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $status = $data['status'];
        if ($status === 'attended') {
            // Record the check-in moment once, and keep it if already present.
            $registration->attended_at ??= now();
        } else {
            $registration->attended_at = null;
        }
        $registration->status = $status;

        if (array_key_exists('feedback', $data)) {
            $registration->feedback = $data['feedback'];
        }

        $registration->save();

        AuditLog::record('updated', 'event_registration', $registration->id, "Registration status → {$status}");

        $message = match ($status) {
            'attended' => 'Attendance recorded.',
            'no_show' => 'Absence recorded.',
            default => 'Registration updated.',
        };

        return back()->with('success', $message);
    }

    public function destroy(EventRegistration $registration)
    {
        abort_unless(Auth::user()->canManageStudents(), 403);
        $registration->delete();
        return back()->with('success', 'Registration removed.');
    }

    /**
     * Hand the confirmation off to the queue so the request never waits on SMTP.
     *
     * @return string one of: queued | no_email | log_driver
     */
    private function sendConfirmation(EventRegistration $registration): string
    {
        $student = $registration->student;

        if (!$student?->email) {
            return 'no_email';
        }

        // Nothing is actually delivered on the log driver — warn instead of pretending.
        if (config('mail.default') === 'log') {
            return 'log_driver';
        }

        SendRegistrationConfirmation::dispatch($registration->id);

        return 'queued';
    }

    private function flashForMailResult(string $result, string $prefix, EventRegistration $registration): \Illuminate\Http\RedirectResponse
    {
        $recipient = $registration->student?->email;

        return match ($result) {
            'queued' => back()->with('success', "{$prefix} — the confirmation email to {$recipient} is being sent in the background."),
            'no_email' => back()->with('warning', "{$prefix}, but no email address is saved on the student profile."),
            'log_driver' => back()->with('warning', "{$prefix}. Email delivery is not enabled (MAIL_MAILER=log); configure SMTP to actually send it."),
            default => back()->with('warning', "{$prefix}."),
        };
    }
}
