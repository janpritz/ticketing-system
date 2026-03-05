<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ticket Response</title>
    <style>
        @media only screen and (max-width: 600px) {
            .mobile-full-width {
                width: 100% !important;
            }

            .mobile-padding {
                padding: 16px !important;
            }

            .mobile-text-center {
                text-align: center !important;
            }

            .mobile-stack {
                display: block !important;
                width: 100% !important;
                margin-bottom: 16px !important;
            }

            .mobile-chat-container {
                padding: 0 8px !important;
            }

            .mobile-bubble {
                max-width: 85% !important;
                margin: 0 auto !important;
            }

            .mobile-header-stack {
                display: block !important;
            }

            .mobile-contact-button {
                display: block !important;
                width: 100% !important;
                margin: 12px 0 !important;
            }

            .mobile-contact-info {
                text-align: center !important;
            }
        }
    </style>
</head>

<body
    style="margin:0;padding:0;background-color:#f8fafc;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:16px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="mobile-full-width"
                    style="width:600px;max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 6px 18px rgba(15,23,42,0.06);border:1px solid #e5e7eb;">

                    <tr>
                        <td style="padding:20px 24px;background:linear-gradient(90deg,#d97706,#b45309);color:#fff">
                            <div style="display:flex;align-items:center;justify-content:space-between">
                                <div>
                                    <h1 style="margin:0;font-size:18px;font-weight:700;letter-spacing:-0.2px">Ticket
                                        Response</h1>
                                    <p style="margin:4px 0 0 0;font-size:13px;opacity:0.95">A staff member has replied
                                        to your ticket.</p>
                                </div>
                                <div style="font-size:12px;opacity:0.95">ID: <strong
                                        style="display:inline-block;margin-left:6px">{{ $ticketNo }}</strong></div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="mobile-padding" style="padding:24px;">
                            <div class="mobile-chat-container" style="max-width:100%;">

                                <div
                                    style="margin:0 0 8px 0;font-size:12px;color:#64748b;font-weight:600;text-align:left;">
                                    You</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="margin-bottom:24px;">
                                    <tr>
                                        <td align="left" class="mobile-stack">
                                            <div class="mobile-bubble"
                                                style="font-size:14px;color:#475569;background:#f1f5f9;padding:12px 16px;border-radius:18px 18px 18px 2px;display:inline-block;max-width:400px;word-break:break-word;">
                                                {{ $ticket->question ?? 'Question not available.' }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <div
                                    style="margin:0 0 8px 0;font-size:12px;color:#d97706;font-weight:600;text-align:right;">
                                    {{ $responderName ?? 'Support Team' }}
                                </div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="margin-bottom:24px;">
                                    <tr>
                                        <td align="right" class="mobile-stack">
                                            <div class="mobile-bubble"
                                                style="font-size:14px;color:#ffffff;background:#d97706;padding:12px 16px;border-radius:18px 18px 2px 18px;display:inline-block;max-width:400px;word-break:break-word;text-align:left;">
                                                {!! nl2br(e($messageBody ?? 'No response content available.')) !!}
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <div
                                    style="background:#fffbeb;border:1px solid #fde68a;padding:20px;border-radius:8px;margin:24px 0;">
                                    <p style="margin:0 0 12px 0;font-size:16px;color:#92400e;font-weight:600;">Need to
                                        follow up?</p>
                                    <p style="margin:0 0 16px 0;font-size:14px;color:#b45309;line-height:1.5;">
                                        If you need clarification, you can reach out to the staff member directly:
                                    </p>
                                    <div class="mobile-contact-info"
                                        style="background:#ffffff;border:1px solid #fde68a;border-radius:6px;padding:16px;">
                                        <div style="font-size:14px;color:#451a03;margin-bottom:4px;">
                                            <strong>{{ $staffName }}</strong></div>
                                        <div style="font-size:13px;color:#92400e;margin-bottom:12px;">
                                            {{ $staffEmail }}</div>

                                        <a href="mailto:{{ $staffEmail }}?subject=Re:%20{{ rawurlencode($ticketNo) }}"
                                            class="mobile-contact-button"
                                            style="display: block; background: #d97706; color: #ffffff; text-decoration: none; padding: 10px; border-radius: 6px; font-weight: 600; font-size: 14px; text-align: center;">
                                            Send Email Follow-up
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="padding:16px 24px;background:#f1f5f9;color:#64748b;font-size:12px;text-align:center;line-height:1.5;">
                            This is an automated message from Sangkay Ticketing System.<br>
                            Please do not reply directly to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="text-align:center;font-size:8px;color:#94a3b8;margin-bottom:24px;">
        Sangkay Integrated Ticketing System &copy; 2026
    </div>
</body>

</html>
