# 📧 Email Templates Guide

## ✅ Implemented Templates

### 1. **Employee Welcome Email** ✅
**When it's sent:** When a new employee is created  
**File:** `app/Mail/EmployeeWelcomeMail.php`  
**Template:** `resources/views/emails/welcome.blade.php`

**Includes:**
- Employee ID
- Full name
- Position and department
- Email address
- Hire date
- **Password** (plain text for initial login)
- Security warning
- Next steps

**How it works:**
```php
// Automatically sent in EmployeeController@store
Mail::to($account->email)->send(new EmployeeWelcomeMail($employee, $password, $account));
```

---

## 🚀 Template Ideas to Add

### 2. **Password Reset Email** 📝
**Purpose:** Send password reset link when user forgets password  
**Usage:**
```php
use App\Mail\PasswordResetMail;
Mail::to($user->email)->send(new PasswordResetMail($resetLink));
```

### 3. **Payroll Notification Email** 💰
**Purpose:** Send payroll details when payroll is processed  
**Usage:**
```php
use App\Mail\PayrollNotificationMail;
Mail::to($employee->email)->send(new PayrollNotificationMail($payroll, $employee));
```

### 4. **Attendance Reminder Email** ⏰
**Purpose:** Remind employees about attendance rules  
**Usage:**
```php
use App\Mail\AttendanceReminderMail;
Mail::to($employee->email)->send(new AttendanceReminderMail($employee));
```

### 5. **Leave Request Status Email** 🏖️
**Purpose:** Notify employee when leave request is approved/rejected  
**Usage:**
```php
use App\Mail\LeaveRequestStatusMail;
Mail::to($employee->email)->send(new LeaveRequestStatusMail($leaveRequest, 'approved'));
```

### 6. **Overtime Approval Email** ⏰
**Purpose:** Notify when overtime is approved/rejected  
**Usage:**
```php
use App\Mail\OvertimeApprovalMail;
Mail::to($employee->email)->send(new OvertimeApprovalMail($overtime, 'approved'));
```

---

## 🎨 Current Email Template Features

✅ **Responsive design** - Works on mobile, tablet, desktop  
✅ **Beautiful gradient header**  
✅ **Professional styling**  
✅ **Color-coded information boxes**  
✅ **Clear call-to-action buttons**  
✅ **Security warnings**  
✅ **Company branding**

---

## 🔧 How to Create New Email Templates

### Step 1: Create the Mailable
```bash
php artisan make:mail YourMailName
```

### Step 2: Update the Mailable Class
```php
public $data;

public function __construct($data)
{
    $this->data = $data;
}

public function build()
{
    return $this->subject('Your Subject')
                ->view('emails.your-template');
}
```

### Step 3: Create the Blade Template
Create `resources/views/emails/your-template.blade.php`

### Step 4: Send the Email
```php
use App\Mail\YourMailName;
use Illuminate\Support\Facades\Mail;

Mail::to($user->email)->send(new YourMailName($data));
```

---

## 📧 Gmail SMTP Configuration

**Status:** ✅ Configured and working

**Current Settings:**
- Host: `smtp.gmail.com`
- Port: `587`
- Encryption: `TLS`
- Username: `eternalbright094@gmail.com`
- Daily Limit: 500 emails

**Test Commands:**
```bash
# Test welcome email
php artisan tinker
use App\Mail\EmployeeWelcomeMail;
use App\Models\Employee;
use App\Models\Account;
$emp = Employee::first();
$acc = Account::where('employee_id', $emp->id)->first();
Mail::to('test@example.com')->send(new EmployeeWelcomeMail($emp, 'testpass123', $acc));
```

---

## 🎯 Next Steps

1. ✅ **Welcome Email** - DONE
2. 📝 Create Password Reset Email
3. 💰 Create Payroll Notification Email
4. ⏰ Create Attendance Reminder Email
5. 🏖️ Create Leave Request Status Email
6. ⏰ Create Overtime Approval Email
7. 🔔 Create System Alert Email

---

## 📊 Email Statistics

- **Templates Created:** 2 (Test + Welcome)
- **Templates Pending:** 5
- **Daily Limit:** 500 emails
- **Current Status:** ✅ Working

---

Happy emailing! 📧✨

