<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Helpers\TimezoneHelper;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $account = Account::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$account || !Hash::check($request->password, $account->password)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($account, $request->boolean('remember'));
        
        // Update last login time with proper timezone handling
        $account->updateLastLogin();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:accounts,email',
        ]);

        $account = Account::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return back()->withErrors(['email' => 'Email not found or account is inactive.']);
        }

        // Generate reset token (valid for 60 minutes)
        $resetToken = Str::random(60);
        
        $account->update([
            'password_reset_token' => $resetToken,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        // Send reset email
        try {
            $employeeName = $account->employee ? $account->employee->first_name . ' ' . $account->employee->last_name : 'User';
            
            Mail::to($account->email)->send(new PasswordResetMail($resetToken, $employeeName));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send reset email. Please try again later.');
        }

        return back()->with('success', 'Password reset link has been sent to your email.');
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return redirect()->route('login')->with('error', 'Invalid or missing reset token.');
        }

        $account = Account::where('password_reset_token', $token)
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$account) {
            return redirect()->route('login')->with('error', 'Invalid or expired reset token. Please request a new one.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $account->email,
            'userName' => $account->employee ? $account->employee->first_name . ' ' . $account->employee->last_name : 'User'
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        // Find account by token only (email is already in the token)
        $account = Account::where('password_reset_token', $request->token)
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$account) {
            return back()->with('error', 'Invalid or expired reset token.');
        }

        // Update password and clear reset token
        $account->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        return redirect()->route('login')->with('success', 'Password has been reset successfully. Please login with your new password.');
    }
}
