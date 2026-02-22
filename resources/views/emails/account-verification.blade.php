<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-content {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4f46e5;
            margin: 0 0 16px 0;
            font-size: 24px;
        }
        p {
            margin: 0 0 16px 0;
            color: #6b7280;
            font-size: 16px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #4338ca;
        }
        .footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-content">
            <h1>Verify Your Account</h1>
            
            <p>Hello {{ $userName }},</p>
            
            <p>Your account has been created by the administrator. Please verify your account and set your password to get started.</p>
            
            <p>Click the button below to verify your account and set your password:</p>
            
            <a href="{{ route('staff.verify-account', ['token' => $verificationToken]) }}" class="button">
                Verify Account
            </a>
            
            {{-- <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
            
            <p style="word-break: break-all; font-size: 14px; color: #4f46e5;">
                {{ route('staff.verify-account', ['token' => $verificationToken]) }}
            </p> --}}
            
            <div class="footer">
                <p>If you did not request for create an account, please ignore this email.</p>
            </div>
        </div>
    </div>
</body>
</html>
