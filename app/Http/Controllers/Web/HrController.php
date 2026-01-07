<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Company;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrController extends Controller
{
    /**
     * Display HR Settings page
     */
    public function settings()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $departments = Department::all();
        $companies = Company::where('is_active', true)->get();
        
        return view('hr.settings', compact('user', 'employee', 'departments', 'companies'));
    }

    /**
     * Update HR Settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:accounts,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'timezone' => 'nullable|string',
            'date_format' => 'nullable|string',
            'dark_mode' => 'boolean',
            'email_notifications' => 'boolean',
            'auto_save' => 'boolean',
        ]);

        // Update account
        $user->update([
            'email' => $request->email,
        ]);

        // Update employee if exists
        if ($employee) {
            $employee->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'department_id' => $request->department_id,
            ]);
        }

        // Update user preferences (you might want to create a user_preferences table)
        // For now, we'll store in session
        session([
            'user_preferences' => [
                'timezone' => $request->timezone ?? 'Asia/Manila',
                'date_format' => $request->date_format ?? 'MM/DD/YYYY',
                'dark_mode' => $request->dark_mode ?? false,
                'email_notifications' => $request->email_notifications ?? true,
                'auto_save' => $request->auto_save ?? true,
            ]
        ]);

        return redirect()->route('hr.settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Check if new password is different from current password
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The new password must be different from the current password.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('hr.settings')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Display HR Profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $departments = Department::all();
        
        // Get recent activity
        $recentActivity = [];
        if ($employee) {
            $recentActivity = [
                'attendance_records' => $employee->attendanceRecords()->latest()->limit(5)->get(),
                'payrolls' => $employee->payrolls()->latest()->limit(5)->get(),
                'overtime_requests' => $employee->overtimeRequests()->latest()->limit(5)->get(),
                'leave_requests' => $employee->leaveRequests()->latest()->limit(5)->get(),
            ];
        }

        return view('hr.profile', compact('user', 'employee', 'departments', 'recentActivity'));
    }

    /**
     * Update HR Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:accounts,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'employment_type' => 'nullable|string|in:Full-time,Part-time,Contract',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'job_description' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_email' => 'nullable|email|max:255',
        ]);

        // Update account
        $user->update([
            'email' => $request->email,
        ]);

        // Update employee if exists
        if ($employee) {
            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ];
            
            // Only update optional fields if they're provided and not empty
            // This prevents null values when form fields are empty (since DB requires these fields)
            if ($request->filled('phone')) {
                $updateData['phone'] = $request->phone;
            }
            
            if ($request->filled('department_id')) {
                $updateData['department_id'] = $request->department_id;
            }
            
            if ($request->filled('position')) {
                $updateData['position'] = $request->position;
            }
            
            if ($request->filled('salary')) {
                $updateData['salary'] = $request->salary;
            }
            
            if ($request->filled('hire_date')) {
                $updateData['hire_date'] = $request->hire_date;
            }
            
            $employee->update($updateData);
        }

        return redirect()->route('hr.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Export employee data
     */
    public function exportData()
    {
        // This would implement data export functionality
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }

    /**
     * Backup system data
     */
    public function backupData()
    {
        // This would implement backup functionality
        return response()->json(['message' => 'Backup functionality not implemented yet']);
    }

    /**
     * Get user sessions for security tab
     */
    public function getUserSessions()
    {
        $user = Auth::user();
        
        // Get all active sessions for the user
        $sessions = UserSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->orderBy('last_activity', 'desc')
            ->get();

        return response()->json([
            'sessions' => $sessions,
            'current_session_id' => session()->getId()
        ]);
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(Request $request, $session)
    {
        $user = Auth::user();
        $sessionId = $session;

        // Don't allow terminating current session
        if ($sessionId === session()->getId()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot terminate current session'
            ], 400);
        }

        // Find and delete the session
        $session = UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if ($session) {
            $session->delete();
            
            // Also remove from Laravel's session storage if it exists
            DB::table('sessions')->where('id', $sessionId)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Session not found'
        ], 404);
    }

    /**
     * Terminate all other sessions (except current)
     */
    public function terminateAllOtherSessions()
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();

        // Get all other sessions
        $otherSessions = UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->get();

        $terminatedCount = 0;
        foreach ($otherSessions as $session) {
            $session->delete();
            DB::table('sessions')->where('id', $session->session_id)->delete();
            $terminatedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Terminated {$terminatedCount} sessions successfully"
        ]);
    }

    /**
     * Track user login session
     */
    public function trackLoginSession()
    {
        $user = Auth::user();
        $sessionId = session()->getId();
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();
        
        // Parse user agent
        $parsed = UserSession::parseUserAgent($userAgent);
        $location = UserSession::getLocationFromIp($ipAddress);
        
        // Mark all other sessions as not current
        UserSession::where('user_id', $user->id)->update(['is_current' => false]);
        
        // Create or update current session
        UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $parsed['device_type'],
                'browser' => $parsed['browser'],
                'os' => $parsed['os'],
                'location' => $location,
                'is_current' => true,
                'last_activity' => now(),
                'login_at' => now(),
                'expires_at' => now()->addMinutes(config('session.lifetime', 120))
            ]
        );

        return response()->json(['success' => true]);
    }
}
