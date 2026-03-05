<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        body,
        table,
        td,
        h1,
        h2,
        p,
        span,
        div {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
    </style>
</head>

<body style="background-color: #fffbeb; color:#451a03; margin:0; padding:24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0"
        style="width:100%; max-width:600px; margin:0 auto;">
        <tr>
            <td style="padding:0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; background: linear-gradient(135deg, #ff9d00 0%, #e68a00 100%); border-radius:16px 16px 0 0; padding:32px 24px;">
                    <tr>
                        <td style="text-align:center; padding:0;">
                            <div
                                style="display:inline-block; background:#ffffff; border-radius:50%; width:70px; height:70px; line-height:70px; text-align:center; margin-bottom:16px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <img src="{{ asset('logo.png') }}" alt="Sangkay Logo"
                                    style="width:40px; height:40px; vertical-align:middle;">
                            </div>
                            <h1
                                style="margin:0; font-size:24px; color:#ffffff; font-weight:700; letter-spacing: -0.5px;">
                                Sangkay Ticketing System</h1>
                            <p style="margin:4px 0 0 0; font-size:14px; color:#fef3c7; font-weight: 400;">Password Reset
                                Verification</p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; background:#ffffff; padding:32px 24px; border-radius: 0 0 16px 16px;">
                    <tr>
                        <td style="padding:0;">
                            <h2 style="margin:0 0 12px 0; font-size:18px; color:#78350f; font-weight:600;">Reset Your
                                Password</h2>
                            <p style="color:#92400e; font-size:14px; margin:0 0 24px 0; line-height:1.6;">
                                We received a request to reset your password. Use the One-Time Password (OTP) below to
                                verify your identity and complete the process.
                            </p>

                            <div
                                style="background: #fffcf0; border:2px dashed #ff9d00; border-radius:12px; padding:24px; text-align:center; margin:24px 0;">
                                <p
                                    style="margin:0 0 12px 0; font-size:11px; color:#ff9d00; text-transform:uppercase; letter-spacing:2px; font-weight:700;">
                                    Verification Code</p>
                                <span
                                    style="font-size:38px; letter-spacing:8px; font-weight:700; color:#451a03; display:block; word-break:break-all;">{{ $otp }}</span>
                            </div>

                            <div
                                style="background:#fffbeb; border-left:4px solid #fbbf24; padding:12px 16px; border-radius:4px; margin-bottom:24px;">
                                <p style="margin:0; font-size:13px; color:#92400e; line-height:1.5;">
                                    <strong>⏱️ Expires in 10 minutes</strong><br>
                                    If you didn't request this, you can safely ignore this email. Your password won't
                                    change until you use this code.
                                </p>
                            </div>

                            <div style="height:1px; background:#fef3c7; margin:24px 0;"></div>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                style="width:100%; background:#451a03; border-radius:12px; padding:20px;">
                                <tr>
                                    <td style="text-align:center;">
                                        <p style="margin:0; font-size:12px; color:#fde68a; line-height:1.6;">
                                            Sangkay Integrated Ticketing System &copy; 2026<br>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
