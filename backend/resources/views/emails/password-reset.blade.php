<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f9fafb;
            padding: 40px 20px;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .header {
            background: #ffffff;
            padding: 48px 48px 32px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-circle i {
            font-size: 32px;
            color: white;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .header p {
            font-size: 15px;
            color: #6b7280;
            font-weight: 400;
        }
        .content {
            padding: 48px;
        }
        .greeting {
            font-size: 16px;
            color: #374151;
            margin-bottom: 24px;
            line-height: 1.8;
        }
        .action-box {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 32px;
            border-radius: 12px;
            text-align: center;
            margin: 32px 0;
        }
        .action-box p {
            color: white;
            font-size: 15px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .reset-button {
            display: inline-block;
            background: white;
            color: #3b82f6;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: transform 0.2s;
        }
        .reset-button:hover {
            transform: translateY(-2px);
        }
        .expiry {
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            margin: 24px 0;
        }
        .security {
            margin-top: 32px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }
        .security-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        .security ul {
            list-style: none;
            padding: 0;
        }
        .security li {
            font-size: 13px;
            color: #6b7280;
            margin: 6px 0;
            padding-left: 20px;
            position: relative;
        }
        .security li:before {
            content: '•';
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 32px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 32px 48px;
            text-align: center;
        }
        .footer p {
            font-size: 13px; 
            color: #9ca3af;
            margin: 4px 0;
        }
        .company-name {
            font-weight: 600;
            color: #374151;
        }
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .header, .content, .footer { padding: 32px 24px !important; }
            .header h1 { font-size: 22px !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon-circle">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Reset Your Password</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <div class="content">
            <div class="greeting">
                Hello {{ $userName }},<br><br>
                We received a request to reset your password for your {{ config('app.name') }} account.
            </div>

            <div class="action-box">
                <p>Click the button below to create a new password</p>
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>

            <div class="expiry">
                ⏰ This link expires in 60 minutes
            </div>

            <div class="security">
                <div class="security-title">Security Notice</div>
                <ul>
                    <li>If you didn't request this, please ignore this email</li>
                    <li>Your current password remains unchanged until you complete the reset</li>
                    <li>Never share your reset link with anyone</li>
                    <li>Contact support if you need assistance</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p class="company-name">{{ config('app.name') }}</p>
            <p>This is an automated message — please do not reply</p>
            <p style="margin-top: 16px; color: #d1d5db;">{{ now()->format('F j, Y') }}</p>
        </div>
    </div>
</body>
</html>

