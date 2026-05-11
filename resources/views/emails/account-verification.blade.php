<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
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

        body {
            background-color: #fffbeb;
            margin: 0;
            padding: 24px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .email-content {
            background-color: #ffffff;
            border-radius: 0 0 16px 16px;
            padding: 32px 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .button-wrapper {
            text-align: center;
            margin: 32px 0;
        }

        .button {
            display: inline-block;
            background-color: #ff9d00;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #e68a00;
        }

        .footer {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #fef3c7;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
            style="width:100%; background: linear-gradient(135deg, #ff9d00 0%, #e68a00 100%); border-radius:16px 16px 0 0; padding:32px 24px;">
            <tr>
                <td style="text-align:center; padding:0;">
                    <div
                        style="display:inline-block; background:#ffffff; border-radius:50%; width:70px; height:70px; line-height:70px; text-align:center; margin-bottom:16px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <img src="{{ asset('logo.png') }}" alt="Sangkay Logo"
                            style="width:40px; height:40px; vertical-align:middle;">
                    </div>
                    <h1 style="margin:0; font-size:24px; color:#ffffff; font-weight:700; letter-spacing: -0.5px;">
                        Sangkay Ticketing System</h1>
                    <p style="margin:4px 0 0 0; font-size:14px; color:#fef3c7; font-weight: 400;">Verify
                        Your Account</p>
                </td>
            </tr>
        </table>
        <div class="email-content">
            <p style="color:#78350f; font-size:16px; font-weight: 600; margin-bottom: 8px;">Hello {{ $userName }},
            </p>

            <p style="color:#92400e; font-size:15px; line-height:1.6;">
                Your account has been successfully created by the administrator. To ensure your security, please verify
                your account and set your permanent password to get started.
            </p>

            <div style="text-align:center; margin:32px 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                    <tr>
                        <td style="background-color:#ff9d00; border-radius:8px; text-align:center;">
                            <a href="{{ route('staff.verify-account', ['token' => $verificationToken]) }}"
                                target="_blank"
                                style="display:inline-block; background-color:#ff9d00; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:8px; font-weight:600; font-size:16px; font-family:'Poppins','Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
                                Verify Account
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                <p style="color:#b45309; font-size:13px; margin-bottom: 16px;">
                    If you did not request this account, please ignore this email or contact your administrator.
                </p>

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
            </div>
        </div>
    </div>
</body>

</html>
