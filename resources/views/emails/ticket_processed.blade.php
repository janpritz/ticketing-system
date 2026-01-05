<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ticket update</title>
    <style>
        @media only screen and (max-width: 600px) {
            .mobile-full-width { width: 100% !important; }
            .mobile-padding { padding: 16px !important; }
            .mobile-text-center { text-align: center !important; }
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
                        <td class="mobile-padding" style="padding:20px 24px;background:#ffffff;border-bottom:1px solid #e5e7eb;">
                            <div style="font-size:16px;font-weight:700;color:#1f2937;">Ticket Update: {{ $ticketNo }}</div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="mobile-padding" style="padding:24px;">
                            <p style="font-size:14px;color:#374151;margin:0 0 12px 0;">Hello,</p>
                            <p style="font-size:14px;color:#374151;margin:0 0 12px 0;">This is an automated notification to let you know that staff have seen or processed your ticket. Here are the details:</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                                <tr>
                                    <td style="font-size:14px;color:#111827;padding:8px 0;"><strong>Ticket Number:</strong></td>
                                    <td style="font-size:14px;color:#111827;padding:8px 0;">{{ $ticketNo }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#111827;padding:8px 0;"><strong>Category:</strong></td>
                                    <td style="font-size:14px;color:#111827;padding:8px 0;">{{ optional($ticket->category)->name ?? ($ticket->getAttribute('category') ?? 'Uncategorized') }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#111827;padding:8px 0;"><strong>Question / Concern:</strong></td>
                                    <td style="font-size:14px;color:#111827;padding:8px 0;">{{ $ticket->question }}</td>
                                </tr>
                            </table>

                            <?php if (!empty($forwardHistory)): ?>
                                <p style="font-size:14px;color:#374151;margin:0 0 12px 0;"><strong>Status:</strong> <?php echo htmlspecialchars($forwardHistory, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php else: ?>
                                <p style="font-size:14px;color:#374151;margin:0 0 12px 0;"><strong>Status:</strong> Seen by - <?php echo !empty($currentAssignee) ? '<strong>' . htmlspecialchars($currentAssignee->name ?? '', ENT_QUOTES, 'UTF-8') . '</strong>' : 'Support Team'; ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="mobile-padding" style="padding:16px 24px;background:#ffffff;color:#9ca3af;font-size:11px;line-height:1.5;border-top:1px solid #e5e7eb;">
                            <div class="mobile-text-center">This email was sent from the Sangkay Ticketing System.<br>
                                Please note that we don't monitor replies to this address. If you need more help, contact the support team.
                                <div style="font-size:11px;color:#d1d5db;margin-top:10px;">&copy; {{ date('Y') }} Sangkay Chatbot Integrated Ticketing System</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

