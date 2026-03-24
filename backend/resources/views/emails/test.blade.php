<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gmail SMTP Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #3b82f6;
            margin: 0;
        }
        .content {
            margin: 30px 0;
        }
        .success {
            background-color: #10b981;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .info {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Gmail SMTP Test Successful!</h1>
        </div>
        
        <div class="content">
            <div class="success">
                ✅ Your Gmail SMTP configuration is working perfectly!
            </div>
            
            <div class="info">
                <h3>📧 Email Configuration Details:</h3>
                <ul>
                    <li><strong>SMTP Host:</strong> smtp.gmail.com</li>
                    <li><strong>Port:</strong> 587 (TLS)</li>
                    <li><strong>Encryption:</strong> TLS</li>
                    <li><strong>Status:</strong> Connected successfully</li>
                </ul>
            </div>
            
            <div class="info">
                <h3>🚀 What This Means:</h3>
                <p>Your Aeternitas System can now send emails through Gmail SMTP. You can use this for:</p>
                <ul>
                    <li>Employee onboarding emails</li>
                    <li>Password reset notifications</li>
                    <li>Payroll notifications</li>
                    <li>Attendance reminders</li>
                    <li>System alerts and updates</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>📨 Aeternitas System V2</p>
            <p>Sent at {{ now()->format('F j, Y g:i A') }}</p>
        </div>
    </div>
</body>
</html>

