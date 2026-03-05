<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ticket Notification</title>
  </head>
  <body style="margin:0;background:#f8fafc;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:24px auto;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 6px 18px rgba(15,23,42,0.06)">
      <tr>
        <td style="padding:20px 24px;background:linear-gradient(90deg,#d97706,#b45309);color:#fff">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <h1 style="margin:0;font-size:18px;font-weight:700;letter-spacing:-0.2px">@if($action === 'forwarded') Ticket Forwarded @else New Ticket Assigned @endif</h1>
              <p style="margin:4px 0 0 0;font-size:13px;opacity:0.95">A ticket has been {{ $action === 'forwarded' ? 'forwarded to you' : 'assigned to you' }}.</p>
            </div>
            <div style="font-size:12px;opacity:0.95">Ticket: <strong style="display:inline-block;margin-left:6px">{{ $ticketNo }}</strong></div>
          </div>
        </td>
      </tr>

      <tr>
        <td style="padding:20px 24px">
          <p style="margin:0 0 12px 0;color:#475569">Hello {{ optional(auth()->user())->name ?? 'Team Member' }},</p>

          <p style="margin:0 0 18px 0;color:#334155">You have a new ticket to review. Below are the details to help you take action quickly.</p>

          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border-spacing:0 8px;margin-bottom:18px">
            <tr>
              <td style="width:75px;color:#64748b;font-size:13px;vertical-align:middle">Subject</td>
              <td style="background:#f8fafc;padding:10px 12px;border-radius:6px;color:#0f172a;border-left:3px solid #d97706">{{ $ticket->question ?? 'No subject' }}</td>
            </tr>
            <tr>
              <td style="width:75px;color:#64748b;font-size:13px;vertical-align:middle">Category</td>
              <td style="background:#f8fafc;padding:10px 12px;border-radius:6px;color:#0f172a;border-left:3px solid #d97706">{{ is_object($ticket->category) ? ($ticket->category->name ?? ($ticket->getAttribute('category') ?? 'Uncategorized')) : ($ticket->getAttribute('category') ?? 'Uncategorized') }}</td>
            </tr>
          </table>

          <div style="text-align:center;margin-top:12px">
            <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:12px 24px;background:#d97706;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Open in Dashboard</a>
          </div>

          <p style="margin-top:18px;color:#64748b;font-size:13px">Clicking the button will open your staff dashboard and automatically open the ticket details for quick review and response.</p>
        </td>
      </tr>

      <tr>
        <td style="padding:16px 24px;background:#f1f5f9;color:#64748b;font-size:12px">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>This is an automated message — please do not reply to this email.</div>
            <div style="opacity:0.9">Need help? Contact your administrator</div>
          </div>
        </td>
      </tr>
    </table>

    <div style="text-align:center;font-size:8px;color:#94a3b8;margin-bottom:24px;">
        Sangkay Integrated Ticketing System &copy; 2026
    </div>

  </body>
</html>