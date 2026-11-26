<?php

namespace App\Jobs;

use App\Mail\EventRegistrationConfirmation;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers the event confirmation email off the request path.
 *
 * The SMTP conversation (TLS handshake, auth, DATA) used to run inside the
 * Register/Resend request, so a slow or rejecting mail server froze the page.
 * The worker also stops after two attempts instead of re-trying bad credentials.
 */
class SendRegistrationConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 2;

    public function __construct(public int $registrationId)
    {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $registration = EventRegistration::with('student', 'event')->find($this->registrationId);
        $student = $registration?->student;

        if (!$registration || !$student?->email) {
            return;
        }

        Mail::to($student->email, $student->name_english)
            ->send(new EventRegistrationConfirmation($registration->event, $registration));

        $registration->forceFill(['confirmation_sent_at' => now()])->save();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Event registration email failed.', [
            'registration_id' => $this->registrationId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
