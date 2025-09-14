<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Reset OTP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f6f7f9; color:#0f172a; margin:0; padding:24px; }
    .card { max-width:560px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; }
    .brand { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
    .brand img { width:28px; height:28px; }
    .otp { font-size:28px; letter-spacing:4px; font-weight:700; color:#111827; background:#f3f4f6; border:1px dashed #d1d5db; padding:12px 16px; border-radius:10px; display:inline-block; }
    .muted { color:#6b7280; font-size:14px; }
    .divider { height:1px; background:#e5e7eb; margin:20px 0; }
    a.button { display:inline-block; background:#2563eb; color:#fff !important; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:600; }
  </style>
</head>
<body>
  <div class="card">
    <div class="brand">
      <img src="{{ asset('logo.png') }}" alt="Logo">
      <strong>Sangkay Ticketing System</strong>
    </div>

    <h2 style="margin:0 0 8px 0;">Password reset verification</h2>
    <p class="muted" style="margin:0 0 16px 0;">Use the One-Time Password (OTP) below to verify your request and set a new password.</p>

    <div style="margin:20px 0;">
      <span class="otp">{{ $otp }}</span>
    </div>

    <p class="muted" style="margin:12px 0 0 0;">This OTP expires in 10 minutes. If you did not request a password reset, you can safely ignore this email.</p>

    <div class="divider"></div>

    <p class="muted" style="margin:0;">— Sangkay Ticketing System</p>
  </div>
</body>
</html>