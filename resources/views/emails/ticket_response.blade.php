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

            .mobile-header-right {
                margin-top: 8px !important;
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

<body style="margin:0;padding:0;background-color:#ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;padding:16px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="mobile-full-width"
                    style="width:600px;max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';">

                    <!-- Header -->
                    <tr>
                        <td class="mobile-padding"
                            style="padding:20px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border-collapse:collapse;">
                                <tr>
                                    <td class="mobile-header-stack">
                                        <div style="font-size:16px;font-weight:700;color:#1f2937;">
                                            Ticket ID: {{ $ticketNo }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="mobile-padding" style="padding:24px;">
                            <div class="mobile-chat-container" style="max-width:100%;">

                                <!-- User's Question Bubble -->
                                <div
                                    style="margin:0 0 8px 0;font-size:12px;color:#4b5563;font-weight:600;text-align:left;">
                                    You</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="margin-bottom:24px;">
                                    <tr>
                                        <td align="left" class="mobile-stack">
                                            <div class="mobile-bubble"
                                                style="font-size:14px;color:#111827;background:#e5e7eb;padding:12px 16px;border-radius:18px 18px 18px 2px;display:inline-block;width:auto;max-width:400px;word-break:break-word;text-align:left;margin:0;text-indent:0;">
                                                {{ $ticket->question ?? 'Question not available.' }}</div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Staff Response Bubble -->
                                <div
                                    style="margin:0 0 8px 0;font-size:12px;color:#4b5563;font-weight:600;text-align:right;">
                                    {{ $responderName ?? 'Support Team' }}</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="margin-bottom:24px;">
                                    <tr>
                                        <td align="right" class="mobile-stack">
                                            <div class="mobile-bubble"
                                                style="font-size:14px;color:#111827;background:#dbeafe;padding:12px 16px;border-radius:18px 18px 2px 18px;display:inline-block;width:auto;max-width:400px;word-break:break-word;text-align:left;margin:0;text-indent:0;">
                                                {!! nl2br(e($messageBody ?? 'No response content available.')) !!}</div>
                                        </td>
                                    </tr>
                                </table>

                                <div style="clear:both;"></div>

                                <!-- Contact Information -->
                                <div
                                    style="background:#f0f9ff;border:1px solid #0ea5e9;padding:20px;border-radius:8px;margin:24px 0;">
                                    <p style="margin:0 0 12px 0;font-size:16px;color:#0c4a6e;font-weight:600;">Need to
                                        follow up?</p>
                                    <p style="margin:0 0 16px 0;font-size:14px;color:#0c4a6e;line-height:1.5;">
                                        If you have any additional questions or need clarification, feel free to contact
                                        the staff member who handled your ticket:
                                    </p>
                                    <div class="mobile-contact-info"
                                        style="background:#ffffff;border:1px solid #0ea5e9;border-radius:6px;padding:16px;margin:16px 0;">
                                        <div class="mobile-contact-info"
                                            style="font-size:14px;color:#0c4a6e;margin-bottom:8px;">
                                            <strong>Staff Member:</strong> {{ $staffName }}
                                        </div>
                                        <div class="mobile-contact-info"
                                            style="font-size:14px;color:#0c4a6e;margin-bottom:16px;">
                                            <strong>Email:</strong> {{ $staffEmail }}
                                        </div>
                                        <a href="mailto:{{ $staffEmail }}?subject=Re:%20{{ rawurlencode($ticketNo) }}&body={{ rawurlencode('Reference: ' . $ticketNo . "\n\n" . ($ticket->question ? 'Original Question: ' . $ticket->question . "\n\n" : '') . ($messageBody ? 'Response: ' . $messageBody . "\n\n" : '') . 'I would like to follow up on my ticket.\n\n') }}"
                                            class="mobile-contact-button"
                                            style="display: block; background: #0ea5e9; color: #ffffff; text-decoration: none; padding: 12px; border-radius: 6px; font-weight: 600; font-size: 14px; text-align: center; box-sizing: border-box;"
                                            target="_blank">
                                            Follow up
                                        </a>
                                    </div>
                                    <p style="margin:12px 0 0 0;font-size:12px;color:#0c4a6e;opacity:0.8;">
                                        Click the button above to open your email client with a pre-filled message.
                                    </p>
                                </div>


                                <!-- Footer -->
                    <tr>
                        <td class="mobile-padding"
                            style="padding:16px 24px;background:#ffffff;color:#9ca3af;font-size:11px;line-height:1.5;border-top:1px solid #e5e7eb;">
                            <div class="mobile-text-center">
                                This email was sent from the Sangkay Ticketing System.<br>
                                Please note that we don't monitor replies to this address. If you need more help, feel
                                free to reach out using the contact details listed above.
                                <div style="font-size:11px;color:#d1d5db;margin-top:10px;">
                                    &copy; {{ date('Y') }} Sangkay Chatbot Integrated Ticketing System
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
