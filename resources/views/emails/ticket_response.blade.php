<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Ticket Response</title>
    <style>
        @media only screen and (max-width: 600px) {
            .mobile-full-width { width: 100% !important; }
            .mobile-padding { padding: 16px !important; }
            .mobile-text-center { text-align: center !important; }
            .mobile-stack { display: block !important; width: 100% !important; margin-bottom: 16px !important; }
            .mobile-chat-container { padding: 0 8px !important; }
            .mobile-bubble { max-width: 85% !important; margin: 0 auto !important; }
            .mobile-header-stack { display: block !important; text-align: center !important; }
            .mobile-header-right { margin-top: 8px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;padding:16px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="mobile-full-width" style="width:600px;max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';">

                    <!-- Header -->
                    <tr>
                        <td class="mobile-padding" style="padding:20px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td class="mobile-header-stack" style="vertical-align:middle;">
                                        <div style="font-size:16px;font-weight:700;line-height:1.4;color:#1f2937;">
                                            Sangkay Ticketing System
                                        </div>
                                    </td>
                                    <td align="right" class="mobile-header-right mobile-text-center" style="vertical-align:middle;">
                                        <div style="font-size:12px;color:#6b7280;">Ticket ID:</div>
                                        <div style="font-size:13px;font-weight:600;color:#111827;">{{ $ticket->id ?? 'N/A' }}</div>
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
                                <div style="margin:0 0 8px 0;font-size:12px;color:#4b5563;font-weight:600;text-align:left;">You</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                    <tr>
                                        <td align="left" class="mobile-stack">
                                            <div class="mobile-bubble" style="font-size:14px;color:#111827;background:#e5e7eb;padding:12px 16px;border-radius:18px 18px 18px 2px;display:inline-block;width:auto;max-width:400px;word-break:break-word;text-align:left;margin:0;text-indent:0;">
{{ $ticket->question ?? 'Question not available.' }}</div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Staff Response Bubble -->
                                <div style="margin:0 0 8px 0;font-size:12px;color:#4b5563;font-weight:600;text-align:right;">{{ $responderName ?? 'Support Team' }}</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                    <tr>
                                        <td align="right" class="mobile-stack">
                                            <div class="mobile-bubble" style="font-size:14px;color:#111827;background:#dbeafe;padding:12px 16px;border-radius:18px 18px 2px 18px;display:inline-block;width:auto;max-width:400px;word-break:break-word;text-align:left;margin:0;text-indent:0;">
{{ $messageBody ?? 'No response content available.' }}</div>
                                        </td>
                                    </tr>
                                </table>

                                <div style="clear:both;"></div>

                                <!-- Closing Message -->
                                <p style="margin:30px 0 0 0;font-size:14px;color:#1f2937;line-height:1.6;">
                                    Thank you for your patience.<br>
                                    Regards,<br>
                                    <strong style="color:#111827;">{{ $responderName ?? 'The Support Team' }}</strong>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="mobile-padding" style="padding:16px 24px;background:#ffffff;color:#9ca3af;font-size:11px;line-height:1.5;border-top:1px solid #e5e7eb;">
                            <div class="mobile-text-center">
                                This email was sent from an unmonitored mailbox. Please do not reply directly.<br>
                                For further assistance, please contact us through the Sangkay Chatbot System.
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