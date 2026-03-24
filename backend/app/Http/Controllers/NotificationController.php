<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\Account;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getLoginLogs(Request $request)
    {
        $user = auth()->user();
        
        // Determine what logs to show based on role
        if (in_array($user->role, ['admin', 'hr', 'manager'])) {
            // Get recent login logs for all employees
            $logs = LoginLog::with(['account.employee'])
                ->latest()
                ->limit(20)
                ->get();
        } else {
            // Only HR and Admin can see login logs
            if (!in_array($user->role, ['admin', 'hr'])) {
                return response()->json([
                    'logs' => [],
                    'unread_count' => 0,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Get only the current user's login logs
            $logs = LoginLog::where('account_id', $user->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        return response()->json([
            'logs' => $logs->map(function($log) {
                $employeeName = $log->account && $log->account->employee 
                    ? $log->account->employee->first_name . ' ' . $log->account->employee->last_name
                    : ($log->account ? 'System Account' : 'Unknown Employee');
                
                return [
                    'id' => $log->id,
                    'employee_name' => $employeeName,
                    'employee_email' => $log->account->email ?? 'N/A',
                    'ip_address' => $log->ip_address,
                    'user_agent' => $this->parseUserAgent($log->user_agent),
                    'login_time' => $log->created_at->format('M d, Y g:i A'),
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $this->getUnreadCount($user),
            'user_role' => $user->role,
        ]);
    }

    private function parseUserAgent($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'Mozilla') !== false) return 'Mozilla';
        return 'Unknown Browser';
    }

    private function getUnreadCount($user)
    {
        // Get count of logs from the last 24 hours
        $recentLogsCount = LoginLog::where('created_at', '>=', now()->subDay())
            ->when(!in_array($user->role, ['admin', 'hr', 'manager']), function($query) use ($user) {
                return $query->where('account_id', $user->id);
            })
            ->count();

        return min($recentLogsCount, 99); // Cap at 99
    }

    public function index()
    {
        $user = auth()->user();
        
        // Only allow admin, HR, and manager roles
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            abort(403, 'Unauthorized access to notifications.');
        }
        
        // Get recent login alerts (last 7 days)
        $loginAlerts = LoginLog::with(['account.employee'])
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($log) {
                return [
                    'type' => 'login',
                    'icon' => 'fa-sign-in-alt',
                    'color' => 'blue',
                    'title' => 'User Login',
                    'description' => ($log->account && $log->account->employee 
                        ? $log->account->employee->first_name . ' ' . $log->account->employee->last_name
                        : 'System Account') . ' logged in from ' . $log->ip_address,
                    'time' => $log->created_at->diffForHumans(),
                    'timestamp' => $log->created_at,
                ];
            });
        
        // Get pending leave requests
        $leaveRequests = \App\Models\LeaveRequest::with(['employee'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($leave) {
                return [
                    'type' => 'leave',
                    'icon' => 'fa-calendar-times',
                    'color' => 'yellow',
                    'title' => 'Leave Request',
                    'description' => ($leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'Unknown') . ' requested ' . $leave->leave_type . ' leave',
                    'time' => $leave->created_at->diffForHumans(),
                    'timestamp' => $leave->created_at,
                ];
            });
        
        // Get pending overtime requests
        $overtimeRequests = \App\Models\OvertimeRequest::with(['employee'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($overtime) {
                return [
                    'type' => 'overtime',
                    'icon' => 'fa-clock',
                    'color' => 'purple',
                    'title' => 'Overtime Request',
                    'description' => ($overtime->employee ? $overtime->employee->first_name . ' ' . $overtime->employee->last_name : 'Unknown') . ' requested ' . $overtime->hours . ' hours overtime',
                    'time' => $overtime->created_at->diffForHumans(),
                    'timestamp' => $overtime->created_at,
                ];
            });
        
        // Get employee clock in/out activities (last 24 hours)
        $clockActivities = \App\Models\TimeEntry::with(['attendanceRecord.employee'])
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->limit(8)
            ->get()
            ->map(function($entry) {
                $employee = $entry->attendanceRecord?->employee;
                return [
                    'type' => 'clock',
                    'icon' => $entry->time_in && !$entry->time_out ? 'fa-sign-in-alt' : 'fa-sign-out-alt',
                    'color' => $entry->time_in && !$entry->time_out ? 'green' : 'red',
                    'title' => $entry->time_in && !$entry->time_out ? 'Clock In' : 'Clock Out',
                    'description' => ($employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown') . ' ' . ($entry->time_in && !$entry->time_out ? 'clocked in' : 'clocked out'),
                    'time' => $entry->created_at->diffForHumans(),
                    'timestamp' => $entry->created_at,
                ];
            });
        
        // Merge and sort all notifications by timestamp
        $notifications = collect()
            ->merge($loginAlerts)
            ->merge($leaveRequests)
            ->merge($overtimeRequests)
            ->merge($clockActivities)
            ->sortByDesc('timestamp')
            ->values();
        
        return view('notifications.index', compact('user', 'notifications'));
    }
}