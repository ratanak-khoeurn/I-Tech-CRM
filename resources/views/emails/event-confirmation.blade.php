<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f4f6fb; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(15,23,42,.08);">
                    <tr>
                        <td style="background:#2563eb; padding:24px 28px;">
                            <div style="color:#ffffff; font-size:20px; font-weight:bold;">{{ $centerName }}</div>
                            <div style="color:#dbeafe; font-size:13px; margin-top:4px;">Event Registration Confirmed</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px; font-size:15px;">Hello <strong>{{ $student->name_english ?: $student->student_code }}</strong>,</p>

                            <p style="margin:0 0 20px; font-size:15px; line-height:1.6;">
                                Your registration for the event below has been confirmed. We look forward to seeing you there.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 18px; border-bottom:1px solid #eef2f7;">
                                        <div style="font-size:17px; font-weight:bold; color:#0f172a;">{{ $event->title }}</div>
                                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#64748b; margin-top:2px;">
                                            {{ \App\Models\AlumniEvent::TYPES[$event->event_type] ?? ucfirst($event->event_type) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                            <tr>
                                                <td style="color:#64748b; padding:4px 0; width:42%;">Date &amp; Time</td>
                                                <td style="color:#0f172a; padding:4px 0;">{{ $event->start_datetime->format('l, F j, Y \a\t g:i A') }}</td>
                                            </tr>
                                            @if($event->end_datetime)
                                                <tr>
                                                    <td style="color:#64748b; padding:4px 0;">Ends</td>
                                                    <td style="color:#0f172a; padding:4px 0;">{{ $event->end_datetime->format('l, F j, Y g:i A') }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="color:#64748b; padding:4px 0;">Location</td>
                                                <td style="color:#0f172a; padding:4px 0;">{{ $event->location ?: 'To be announced' }}</td>
                                            </tr>
                                            @if($event->organizer)
                                                <tr>
                                                    <td style="color:#64748b; padding:4px 0;">Organizer</td>
                                                    <td style="color:#0f172a; padding:4px 0;">{{ $event->organizer }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if($event->description)
                                <p style="margin:20px 0 0; font-size:14px; line-height:1.6; color:#334155;">
                                    {{ strip_tags($event->description) }}
                                </p>
                            @endif

                            <p style="margin:24px 0 0; font-size:13px; color:#64748b; line-height:1.6;">
                                Please arrive a few minutes early. If you have any questions or need to cancel,
                                reply to this email and our team will be glad to help.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8fafc; padding:18px 28px; border-top:1px solid #eef2f7;">
                            <div style="font-size:12px; color:#94a3b8; line-height:1.6;">
                                This confirmation was sent on behalf of {{ $centerName }}.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
