<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f6f7f9; color:#0f172a; margin:0; padding:24px;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; width:100%;">
    <tr>
      <td style="padding:0;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
          <strong style="font-size:16px; color:#111827;">Sangkay Ticketing System</strong>
        </div>

        <h2 style="margin:0 0 8px 0; font-size:18px; color:#111827; font-weight:600;">Email Verification Code</h2>
        <p style="color:#6b7280; font-size:14px; margin:0 0 16px 0; line-height:1.5;">Use the One-Time Password (OTP) below to verify your email for ticket creation.</p>

        <div style="margin:20px 0;">
          <span style="font-size:28px; letter-spacing:4px; font-weight:700; color:#111827; background:#f3f4f6; border:1px dashed #d1d5db; padding:12px 16px; border-radius:10px; display:inline-block; font-family:monospace;">{{ $otpCode }}</span>
        </div>

        <p style="color:#6b7280; font-size:14px; margin:12px 0 0 0; line-height:1.5;">This OTP expires in 15 minutes. If you did not request this verification, you can safely ignore this email.</p>

        <div style="height:1px; background:#e5e7eb; margin:20px 0;"></div>

        <p style="color:#6b7280; font-size:14px; margin:0; line-height:1.5;">— Sangkay Ticketing System</p>
      </td>
    </tr>
  </table>
</body>
</html>