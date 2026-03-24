<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 48px 48px 32px;
            text-align: center;
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            background: rgba(255,255,255,0.2);
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
            color: white;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .header p {
            font-size: 15px;
            color: rgba(255,255,255,0.9);
            font-weight: 400;
        }
        .content {
            padding: 48px;
        }
        .greeting {
            font-size: 16px;
            color: #374151;
            margin-bottom: 32px;
            line-height: 1.8;
        }
        .greeting strong {
            color: #111827;
            font-size: 18px;
        }
        .credentials-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 32px;
            margin: 32px 0;
        }
        .credentials-title {
            font-size: 16px;
            font-weight: 600;
            color: #065f46;
            margin-bottom: 24px;
            text-align: center;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid rgba(16,185,129,0.2);
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .cred-label {
            color: #047857;
            font-size: 14px;
            font-weight: 500;
        }
        .cred-value {
            color: #065f46;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
        }
        .password-section {
            background: white;
            border: 2px solid #fbbf24;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
            text-align: center;
        }
        .password-label {
            font-size: 13px;
            color: #92400e;
            margin-bottom: 12px;
            font-weight: 500;
        }
        .password-display {
            font-size: 22px;
            font-weight: bold;
            color: #b45309;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            padding: 12px;
            background: #fef3c7;
            border-radius: 8px;
            margin: 8px 0;
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
        .login-button {
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
        .login-button:hover {
            transform: translateY(-2px);
        }
        .info-section {
            margin-top: 32px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }
        .info-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        .info-list {
            list-style: none;
            padding: 0;
        }
        .info-list li {
            font-size: 13px;
            color: #6b7280;
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        .info-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
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
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Welcome to {{ config('app.name') }}</h1>
            <p>Your account has been created</p>
        </div>

        <div class="content">
            <div class="greeting">
                Hello <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>,<br><br>
                We're excited to have you as part of our team!
            </div>

            <div class="credentials-box">
                <div class="credentials-title">Your Account Details</div>
                
                <div class="credential-item">
                    <span class="cred-label">Employee ID</span>
                    <span class="cred-value">{{ $employee->employee_id }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="cred-label">Position</span>
                    <span class="cred-value">{{ $employee->position }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="cred-label">Department</span>
                    <span class="cred-value">{{ $employee->department->name }}</span>
                </div>
                
                <div class="credential-item">
                    <span class="cred-label">Email</span>
                    <span class="cred-value">{{ $account->email }}</span>
                </div>
            </div>

            <div class="password-section">
                <div class="password-label">Your Temporary Password</div>
                <div class="password-display">{{ $password }}</div>
            </div>

            <div class="action-box">
                <p>Ready to get started?</p>
                <a href="{{ config('app.url') }}" class="login-button">Login to Your Account</a>
            </div>

            <div class="info-section">
                <div class="info-title">Important Information</div>
                <ul class="info-list">
                    <li>Change your password on first login</li>
                    <li>Keep your credentials secure and confidential</li>
                    <li>Contact HR if you need any assistance</li>
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


