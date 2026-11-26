<?php

namespace App\Mail;

use App\Models\AlumniEvent;
use App\Models\EventRegistration;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AlumniEvent $event,
        public EventRegistration $registration,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're registered: " . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-confirmation',
            with: [
                'event' => $this->event,
                'registration' => $this->registration,
                'student' => $this->registration->student,
                'centerName' => Setting::get('center_name', 'Education Center'),
            ],
        );
    }
}
