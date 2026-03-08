<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ticket Update - Sangkay</title>
    <style>
        @media only screen and (max-width: 600px) {
            .mobile-padding { padding: 16px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:24px auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 6px 18px rgba(15,23,42,0.06)">
        
        <tr>
            <td style="padding:20px 24px;background:linear-gradient(90deg,#d97706,#b45309);color:#fff">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <h1 style="margin:0;font-size:18px;font-weight:700;letter-spacing:-0.2px">Ticket Under Review</h1>
                        <p style="margin:4px 0 0 0;font-size:13px;opacity:0.95">A staff member has started viewing your request.</p>
                    </div>
                    <div style="font-size:12px;opacity:0.95">ID: <strong style="display:inline-block;margin-left:6px">#{{ $ticketNo }}</strong></div>
                </div>
            </td>
        </tr>

        <tr>
            <td class="mobile-padding" style="padding:24px;">
                <p style="margin:0 0 12px 0;color:#475569">Hello,</p>
                <p style="margin:0 0 20px 0;color:#334155; line-height:1.5;">Your ticket is currently being handled by <strong>{{ $seenByStaff->name ?? 'our technical team' }}</strong>. We will notify you as soon as there is a resolution or further update.</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border-spacing:0 8px;margin-bottom:18px">
                    <tr>
                        <td style="width:110px;color:#64748b;font-size:13px;vertical-align:middle">Category</td>
                        <td style="background:#f8fafc;padding:10px 12px;border-radius:6px;color:#0f172a;border-left:3px solid #d97706">
                            {{ $categoryName ?? 'General Inquiry' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="width:110px;color:#64748b;font-size:13px;vertical-align:middle">Submitted On</td>
                        <td style="background:#f8fafc;padding:10px 12px;border-radius:6px;color:#0f172a;border-left:3px solid #d97706">
                            {{ \Carbon\Carbon::parse($createdAt)->format('M d, Y - h:i A') }}
                        </td>
                    </tr>
                </table>

                <p style="margin-top:24px;color:#64748b;font-size:13px;line-height:1.5; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                    Automated notification. Please do not reply to this email. Thank you for your patience.
                </p>
            </td>
        </tr>
    </table>

    <div style="text-align:center;font-size:10px;color:#94a3b8;margin-bottom:24px;">
        Sangkay Integrated Ticketing System &copy; 2026
    </div>
</body>
</html>