<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HrContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpSupportController extends Controller
{
    /**
     * Show Help & Support page
     */
    public function index()
    {
        $user = Auth::user();

        // FAQ data
        $faqs = [
            [
                'category' => 'Attendance & Time Tracking',
                'items' => [
                    [
                        'question' => 'How do I clock in/out?',
                        'answer' => 'You can clock in and out from the Time In/Out section in the dashboard. Simply click the "Time In" button when you arrive and "Time Out" when you leave. The system will record your attendance automatically.'
                    ],
                    [
                        'question' => 'What if I forgot to clock out?',
                        'answer' => 'Contact HR immediately through the Contact HR feature in the sidebar. Provide details about when you left, and HR can manually update your attendance record.'
                    ],
                    [
                        'question' => 'Can I view my attendance history?',
                        'answer' => 'Yes! Go to Attendance > Daily or Timekeeping to view your complete attendance history with dates and times.'
                    ],
                    [
                        'question' => 'What about break times?',
                        'answer' => 'You can log break times from the Time In/Out page. Click "Break Start" when you take a break and "Break End" when you return.'
                    ]
                ]
            ],
            [
                'category' => 'Leave & Time Off',
                'items' => [
                    [
                        'question' => 'How do I request leave?',
                        'answer' => 'Go to Leave Management in the Attendance section. Click "Create Leave Request", select the type of leave, and provide the dates and reason.'
                    ],
                    [
                        'question' => 'How many days of leave do I have?',
                        'answer' => 'Your leave balance is shown in the Leave Management page. It displays your available leave by type (sick, vacation, etc.).'
                    ],
                    [
                        'question' => 'What if my leave is rejected?',
                        'answer' => 'Contact HR through the Contact HR feature to discuss why your leave request was rejected. They can provide guidance on rescheduling.'
                    ],
                    [
                        'question' => 'Can I cancel a leave request?',
                        'answer' => 'Yes, if your leave is still pending. Go to Leave Management and look for the cancel option on your pending requests. Approved leaves require HR approval to cancel.'
                    ]
                ]
            ],
            [
                'category' => 'Payroll & Compensation',
                'items' => [
                    [
                        'question' => 'When do I get paid?',
                        'answer' => 'Check your company\'s payroll schedule in the Payroll section. Your pay dates will be listed there.'
                    ],
                    [
                        'question' => 'How do I download my payslip?',
                        'answer' => 'Go to Payroll > View Payslips. Select the month and year, then click the download button to get your payslip in PDF format.'
                    ],
                    [
                        'question' => 'What deductions appear in my salary?',
                        'answer' => 'Common deductions include tax, SSS, health insurance, and other benefits. Check your payslip for a detailed breakdown. Contact HR if you have questions about specific deductions.'
                    ],
                    [
                        'question' => 'Can I request a salary advance?',
                        'answer' => 'Contact HR through the Contact HR feature with "Payroll" as the category to discuss salary advance options.'
                    ]
                ]
            ],
            [
                'category' => 'Schedules & Overtime',
                'items' => [
                    [
                        'question' => 'Where can I see my work schedule?',
                        'answer' => 'Go to the Schedules section to view your assigned work schedule. It shows your daily hours and any scheduled changes.'
                    ],
                    [
                        'question' => 'How do I request overtime?',
                        'answer' => 'Go to Overtime Requests in the dashboard. Submit your overtime request with the date and number of hours. HR will review and approve.'
                    ],
                    [
                        'question' => 'What if I need to change my schedule?',
                        'answer' => 'Contact HR through the Contact HR feature with "Schedule" as the category to request a schedule change.'
                    ],
                    [
                        'question' => 'How is overtime compensated?',
                        'answer' => 'Check your company policy for overtime rates. This information is usually provided during onboarding. Contact HR for clarification.'
                    ]
                ]
            ],
            [
                'category' => 'Account & Profile',
                'items' => [
                    [
                        'question' => 'How do I update my profile?',
                        'answer' => 'Click your profile icon in the top right, then select "Update Profile". You can update your personal information, contact details, and photo.'
                    ],
                    [
                        'question' => 'How do I change my password?',
                        'answer' => 'Go to Settings > Change Password. Enter your current password and the new password you want to set.'
                    ],
                    [
                        'question' => 'What if I forgot my password?',
                        'answer' => 'Click "Forgot Password" on the login page. Enter your email and follow the instructions to reset your password.'
                    ],
                    [
                        'question' => 'Can I switch between companies?',
                        'answer' => 'If you\'re assigned to multiple companies, click the company dropdown in the sidebar to switch between them.'
                    ]
                ]
            ],
            [
                'category' => 'Technical Issues',
                'items' => [
                    [
                        'question' => 'The page won\'t load. What should I do?',
                        'answer' => 'Try refreshing your browser (F5 or Ctrl+R). Clear your browser cache if the problem persists. Try accessing from a different browser if available.'
                    ],
                    [
                        'question' => 'I\'m getting an error message. What does it mean?',
                        'answer' => 'Take a screenshot of the error and contact HR through the Contact HR feature with "General" as the category. Include the error message and steps to reproduce it.'
                    ],
                    [
                        'question' => 'What browsers are supported?',
                        'answer' => 'The system works best with Chrome, Firefox, Safari, and Edge. Use the latest version for optimal performance.'
                    ],
                    [
                        'question' => 'Can I access this from my phone?',
                        'answer' => 'Yes! The system is mobile-responsive. Open it in your phone\'s browser for a mobile-optimized view.'
                    ]
                ]
            ]
        ];

        // Troubleshooting tips
        $troubleshooting = [
            [
                'title' => 'Clock In/Out Not Working',
                'steps' => [
                    'Make sure you\'re not already clocked in when trying to clock in again',
                    'Check your internet connection',
                    'Refresh the page and try again',
                    'Try from a different browser',
                    'If the problem persists, contact HR with a screenshot of the error'
                ]
            ],
            [
                'title' => 'Can\'t Download Payslip',
                'steps' => [
                    'Ensure your browser allows PDF downloads',
                    'Disable any browser ad-blockers that might interfere',
                    'Check that the payslip generation is complete (may take a moment)',
                    'Try using a different browser',
                    'Contact HR if the issue continues'
                ]
            ],
            [
                'title' => 'Leave Request Not Showing',
                'steps' => [
                    'Refresh the page',
                    'Check that you\'re viewing the correct date range',
                    'Clear your browser cache',
                    'Try again from a different browser',
                    'Contact HR with details about the leave request'
                ]
            ],
            [
                'title' => 'Can\'t Access My Department Schedule',
                'steps' => [
                    'Verify you have permission to view the schedule',
                    'Check if your schedule has been assigned yet',
                    'Refresh the Schedules page',
                    'Try logging out and logging back in',
                    'Contact HR to confirm your schedule assignment'
                ]
            ],
            [
                'title' => 'Salary Deduction Questions',
                'steps' => [
                    'Check your detailed payslip for a breakdown',
                    'Compare with previous months to see changes',
                    'Contact HR with your payslip and specific deduction questions',
                    'HR can provide an itemized explanation of all deductions',
                    'Request a meeting if you need further clarification'
                ]
            ]
        ];

        return view('help-support', compact('user', 'faqs', 'troubleshooting'));
    }

    /**
     * Store support ticket
     */
    public function storeTicket(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => 'required|in:attendance,leave,payroll,schedule,account,technical,other'
        ]);

        // Create as HrContact for tracking
        $validated['user_id'] = $user->id;
        if ($user->employee) {
            $validated['employee_id'] = $user->employee->id;
        }

        HrContact::create($validated);

        return redirect()->route('hr.help-support')
            ->with('success', 'Your support ticket has been created. HR will respond shortly.');
    }
}
