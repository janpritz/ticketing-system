<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Response</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f7fb;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;padding:24px 0;">
        <tr>
            <td class="align:center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:20px 24px;background:#111827;color:#ffffff;">
                            <div style="font-size:16px;font-weight:600;line-height:1.4;">
                                Sangkay Chatbot Integrated Ticketing System
                            </div>
                            <div style="font-size:12px;opacity:.8;">
                                Response to your ticket {{ $ticketNo ?? '' }}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 24px;">
                            <p style="margin:0 0 12px 0;font-size:14px;color:#111827;">
                                Hello,
                            </p>

                            <p style="margin:0 0 16px 0;font-size:14px;color:#111827;white-space:pre-wrap;">
                                {{ $messageBody ?? '' }}
                            </p>

                            <div style="margin:20px 0 0 0;padding:12px 14px;border:1px solid #e5e7eb;background:#f9fafb;border-radius:6px;">
                                <div style="font-size:12px;color:#374151;margin:0 0 6px 0;font-weight:600;">Ticket Details</div>
                                <div style="font-size:12px;color:#4b5563;">
                                    <div><strong>Ticket:</strong> {{ $ticketNo ?? '' }}</div>
                                    <div><strong>Status:</strong> {{ $ticket->status ?? '' }}</div>
                                    <div><strong>Category:</strong> {{ $ticket->category ?? '' }}</div>
                                    <div><strong>Recipient ID:</strong> {{ $ticket->recepient_id ?? '' }}</div>
                                    <div><strong>Created:</strong> {{ \Illuminate\Support\Carbon::parse($ticket->date_created ?? $ticket->created_at)->format('M d, Y g:i A') }}</div>
                                </div>
                            </div>

                            <p style="margin:20px 0 0 0;font-size:14px;color:#111827;">
                                Regards,<br>
                                {{ $responderName ?? 'Staff' }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:14px 24px;background:#f3f4f6;color:#6b7280;font-size:11px;">
                            This email was sent from an unmonitored mailbox. Please do not reply directly.
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