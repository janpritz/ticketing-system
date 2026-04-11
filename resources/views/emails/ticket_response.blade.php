<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Ticket Response</title>
    <style>
        /* Reset styles */
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }

        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                margin: 0 !important;
            }
            .mobile-padding {
                padding: 20px 15px !important;
            }
            .mobile-stack {
                display: block !important;
                width: 100% !important;
            }
            .mobile-bubble {
                max-width: 90% !important;
                width: auto !important;
            }
            .header-content {
                display: block !important;
                text-align: center !important;
            }
            .header-id {
                margin-top: 10px !important;
                display: block !important;
            }
            .mobile-contact-button {
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:#f8fafc;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;">
        <tr>
            <td align="center" style="padding: 16px 10px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="container" style="width:100%; max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(15,23,42,0.08); border:1px solid #e2e8f0;">
                    
                    <tr>
                        <td style="padding:24px; background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="header-content">
                                        <h1 style="margin:0; font-size:20px; font-weight:700; line-height:1.2;">Ticket Response</h1>
                                        <p style="margin:4px 0 0 0; font-size:14px; opacity:0.9;">A staff member has replied to your ticket.</p>
                                    </td>
                                    <td class="header-content header-id" align="right" style="vertical-align: middle;">
                                        <span style="font-size:12px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px;">
                                            ID: <strong>{{ $ticketNo }}</strong>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="mobile-padding" style="padding:32px 24px;">
                            
                            <div style="text-align: right; margin-bottom: 8px;">
                                <span style="font-size:12px; color:#64748b; font-weight:700; text-transform: uppercase; letter-spacing: 0.05em;">You</span>
                            </div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                                <tr>
                                    <td align="right">
                                        <div class="mobile-bubble" style="background-color:#f1f5f9; color:#334155; font-size:15px; line-height:1.5; padding:12px 18px; border-radius:18px 18px 4px 18px; display:inline-block; max-width:80%; text-align:left; word-break:break-word;">
                                            {{ $ticket->question ?? 'Question not available.' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align: left; margin-bottom: 8px;">
                                <span style="font-size:12px; color:#d97706; font-weight:700; text-transform: uppercase; letter-spacing: 0.05em;">{{ $responderName ?? 'Support Team' }}</span>
                            </div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                                <tr>
                                    <td align="left">
                                        <div class="mobile-bubble" style="background-color:#d97706; color:#ffffff; font-size:15px; line-height:1.5; padding:12px 18px; border-radius:18px 18px 18px 4px; display:inline-block; max-width:80%; text-align:left; word-break:break-word;">
                                            {!! nl2br(e($messageBody ?? 'No response content available.')) !!}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fffbeb; border:1px solid #fef3c7; border-radius:12px; margin-top:32px;">
                                <tr>
                                    <td style="padding:24px;">
                                        <h3 style="margin:0 0 8px 0; font-size:16px; color:#92400e;">Need clarification?</h3>
                                        <p style="margin:0 0 20px 0; font-size:14px; color:#b45309; line-height:1.5;">You can reach out to the assigned staff member directly for further assistance.</p>
                                        
                                        <div style="background-color:#ffffff; border:1px solid #fde68a; border-radius:8px; padding:16px; text-align:center;">
                                            <p style="margin:0; font-size:15px; color:#451a03; font-weight:700;">{{ $staffName }}</p>
                                            <p style="margin:4px 0 16px 0; font-size:13px; color:#92400e;">{{ $staffEmail }}</p>
                                            
                                            <a href="mailto:{{ $staffEmail }}?subject=Re:%20{{ rawurlencode($ticketNo) }}" class="mobile-contact-button" style="display:inline-block; background-color:#d97706; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:600; font-size:14px;">
                                                Send Email Follow-up
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px; background-color:#f1f5f9; color:#64748b; font-size:12px; text-align:center; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; line-height:1.6;">
                                This is an automated message from <strong>Sangkay Ticketing System</strong>.<br>
                                Please do not reply directly to this email.
                            </p>
                        </td>
                    </tr>
                </table>

                <div style="text-align:center; font-size:11px; color:#94a3b8; margin-top:20px;">
                    &copy; 2026 Abuyog Community College. All rights reserved.
                </div>
            </td>
        </tr>
    </table>
</body>

</html>