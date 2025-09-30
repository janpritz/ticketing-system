<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Ticket Response</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f7fb;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';">
                    <tr>
                        <td style="padding:18px 24px;background:#111827;color:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <img src="{{ asset('logo-white.png') }}" alt="Sangkay" width="28" height="28" style="display:block;border:0;outline:none;">
                                            <div style="font-size:16px;font-weight:700;line-height:1.4;">Sangkay Chatbot Integrated Ticketing System</div>
                                        </div>
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        <div style="font-size:12px;opacity:.85;">Response to your ticket</div>
                                        <div style="font-size:12px;font-weight:600;">{{ $ticketNo ?? '' }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 24px 6px 24px;">

                            <div style="margin:0 0 10px 0;font-size:12px;color:#6b7280;font-weight:700;letter-spacing:.2px;">Question</div>
                            <div style="margin:0 0 16px 0;font-size:14px;color:#111827;background:#f9fafb;border:1px solid #e5e7eb;padding:12px 14px;border-radius:8px;white-space:pre-wrap;">
                                {{ $ticket->question ?? '' }}
                            </div>

                            <div style="margin:12px 0 8px 0;font-size:12px;color:#6b7280;font-weight:700;letter-spacing:.2px;">Response</div>
                            <div style="margin:0 0 16px 0;font-size:14px;color:#111827;background:#eef2ff;border:1px solid #c7d2fe;border-left:4px solid #6366f1;padding:14px 16px;border-radius:8px;white-space:pre-wrap;">
                                {{ $messageBody ?? '' }}
                            </div>


                            <div style="margin:20px 0 0 0;padding:16px;border:1px solid #e5e7eb;background:#f9fafb;border-radius:10px;">
                                <div style="font-size:12px;color:#374151;margin:0 0 10px 0;font-weight:700;letter-spacing:.2px;">Ticket Details</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:12px;color:#4b5563;">
                                    <tr>
                                        <td style="padding:6px 0;width:140px;color:#6b7280;">Ticket</td>
                                        <td style="padding:6px 0;font-weight:600;color:#111827;">{{ $ticketNo ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:#6b7280;">Status</td>
                                        <td style="padding:6px 0;">{{ $ticket->status ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:#6b7280;">Category</td>
                                        <td style="padding:6px 0;">{{ $ticket->category ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;color:#6b7280;">Created</td>
                                        <td style="padding:6px 0;">{{ \Illuminate\Support\Carbon::parse($ticket->date_created ?? $ticket->created_at)->format('Y-m-d h:i a') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <p style="margin:20px 0 0 0;font-size:14px;color:#111827;">
                                Regards,<br>
                                {{ $responderName ?? 'Staff' }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;background:#f3f4f6;color:#6b7280;font-size:11px;line-height:1.5;">
                            This email was sent from an unmonitored mailbox. Please do not reply directly.<br>
                            For assistance, submit a ticket from the Sangkay Chatbot System.
                        </td>
                    </tr>
                </table>

                <div style="font-size:11px;color:#9ca3af;margin-top:10px;">
                    &copy; {{ date('Y') }} Sangkay Chatbot Integrated Ticketing System
                </div>
            </td>
        </tr>
    </table>
</body>
</html>