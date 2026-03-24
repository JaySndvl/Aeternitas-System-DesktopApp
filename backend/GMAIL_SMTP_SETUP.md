# Gmail SMTP Setup Guide

## 📧 Quick Setup for Gmail SMTP

### Step 1: Enable 2-Factor Authentication on Your Gmail Account

1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security**
3. Under "How you sign in to Google", click on **2-Step Verification**
4. Follow the prompts to enable 2-Step Verification

### Step 2: Generate an App Password

1. Go back to **Security** in your Google Account
2. Click on **App Passwords** (you'll need to search for it)
3. Select app: **Mail** and device: **Other (Custom name)**
4. Enter a name like "Laravel Aeternitas"
5. Click **Generate**
6. **Copy the 16-character password** (you'll need this for .env)

### Step 3: Update Your .env File

Add these lines to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Test the Configuration

Run this command to test:

```bash
php artisan tinker
```

Then in tinker:

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email from Laravel!', function ($message) {
    $message->to('jersondev03@gmail.com')
            ->subject('Test Email');
});
```

## 🔒 Gmail SMTP Settings

**For Gmail:**
- **SMTP Host:** `smtp.gmail.com`
- **SMTP Port:** `587` (TLS) or `465` (SSL)
- **Encryption:** `tls` (recommended) or `ssl`
- **Authentication:** Required (use App Password, not your regular password)

## 📊 Limits

- **Daily limit:** 500 emails/day per Gmail account
- **Rate limit:** ~100 emails per minute
- **Attachment limit:** 25MB per email

## 🎯 Configuration Example

Complete `.env` configuration:

```env
# Environment
APP_NAME="Aeternitas System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourcompany@gmail.com
MAIL_PASSWORD=xkcd xkcd xkcd xkcd
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourcompany@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## ⚠️ Important Notes

1. **App Password**: Use the 16-character App Password, NOT your regular Gmail password
2. **2FA Required**: You must have 2-Factor Authentication enabled to use App Passwords
3. **Less Secure Apps**: Don't enable "Less secure app access" - use App Passwords instead
4. **Testing**: Start with the `log` driver for testing before switching to `smtp`

## 🔧 Troubleshooting

### Error: "Authentication failed"
- Make sure you're using an App Password, not your regular password
- Verify 2FA is enabled on your Google account
- Regenerate the App Password

### Error: "Could not connect"
- Check if port 587 is not blocked by your firewall
- Try using port 465 with SSL instead
- Verify your internet connection

### Emails going to spam
- Set up SPF records for your domain
- Use a custom domain email instead of Gmail (optional)
- Authenticate your sending domain

## 🚀 Next Steps

After setup, you can use email in your Laravel app:

```php
// In your controller
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

Mail::to($user->email)->send(new WelcomeMail($user));
```

Happy emailing! 📧

