<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ticket Viewing Verification</title>
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
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#0f172a; margin:0; padding:24px;">
  <!-- Outer wrapper for gradient background -->
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
    <tr>
      <td style="padding:0;">
        <!-- Header with logo area -->
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius:16px 16px 0 0; padding:32px 24px;">
          <tr>
            <td style="text-align:center; padding:0;">
              <div style="display:inline-block; background:#ffffff; border-radius:50%; width:60px; height:60px; line-height:60px; text-align:center; margin-bottom:16px;">
                <span style="font-size:32px; font-weight:bold; color:#667eea;">🔐</span>
              </div>
              <h1 style="margin:0; font-size:28px; color:#ffffff; font-weight:700;">Verify Your Identity</h1>
              <p style="margin:8px 0 0 0; font-size:14px; color:#e0e7ff;">Secure access to your ticket history</p>
            </td>
          </tr>
        </table>

        <!-- Main content -->
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%; background:#ffffff; padding:32px 24px;">
          <tr>
            <td style="padding:0;">
              <h2 style="margin:0 0 12px 0; font-size:20px; color:#111827; font-weight:600;">Your Ticket Viewing OTP</h2>
              <p style="color:#6b7280; font-size:14px; margin:0 0 24px 0; line-height:1.6;">
                For your data privacy and security, we require verification before accessing your ticket history. Use the One-Time Password (OTP) below to verify your identity.
              </p>

              <!-- OTP Code Box -->
              <div style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border:2px solid #667eea; border-radius:12px; padding:24px; text-align:center; margin:24px 0;">
                <p style="margin:0 0 12px 0; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:1px; font-weight:600;">Your OTP Code</p>
                <span style="font-size:36px; letter-spacing:6px; font-weight:700; color:#667eea; font-family:'Courier New', monospace; display:block; word-break:break-all;">{{ $otpCode }}</span>
              </div>

              <!-- Info boxes -->
              <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%; margin:24px 0;">
                <tr>
                  <td style="padding:0;">
                    <!-- Expiry info -->
                    <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 16px; border-radius:4px; margin-bottom:12px;">
                      <p style="margin:0; font-size:13px; color:#92400e; line-height:1.5;">
                        <strong>⏱️ Expires in 15 minutes</strong><br>
                        This code will expire if not used within 15 minutes.
                      </p>
                    </div>

                    <!-- Security info -->
                    <div style="background:#dbeafe; border-left:4px solid #3b82f6; padding:12px 16px; border-radius:4px;">
                      <p style="margin:0; font-size:13px; color:#1e40af; line-height:1.5;">
                        <strong>🛡️ Never share this code</strong><br>
                        Sangkay staff will never ask for your OTP. Keep it confidential.
                      </p>
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Steps -->
              <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin:24px 0;">
                <p style="margin:0 0 12px 0; font-size:13px; color:#111827; font-weight:600;">How to proceed:</p>
                <ol style="margin:0; padding-left:20px; color:#6b7280; font-size:13px; line-height:1.8;">
                  <li>Go back to the ticket verification page</li>
                  <li>Enter the 6-digit code above</li>
                  <li>Click "Verify OTP"</li>
                  <li>Access your complete ticket history</li>
                </ol>
              </div>

              <!-- Divider -->
              <div style="height:1px; background:#e5e7eb; margin:24px 0;"></div>

              <!-- Footer message -->
              <p style="color:#6b7280; font-size:12px; margin:0; line-height:1.6;">
                If you did not request this verification code, please ignore this email. Your account remains secure.
              </p>
            </td>
          </tr>
        </table>

        <!-- Footer -->
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%; background:#1f2937; border-radius:0 0 16px 16px; padding:20px 24px;">
          <tr>
            <td style="text-align:center; padding:0;">
              <p style="margin:0; font-size:12px; color:#9ca3af; line-height:1.6;">
                <strong style="color:#f3f4f6;">Sangkay Integrated Ticketing System</strong><br>
                Secure • Private • Reliable
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>