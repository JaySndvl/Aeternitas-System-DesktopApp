<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\LoginLog;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $account = Account::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$account || !Hash::check($request->password, $account->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($account);
        
        // Update last login time
        $account->update(['last_login_at' => now()]);

        $token = $account->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $account,
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

        protected function authenticated(Request $request, $user)
    {
        // Record login log
        LoginLog::create([
            'account_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update last login in accounts table
        $user->update([
            'last_login_at' => now()
        ]);

        return redirect()->intended($this->redirectPath());
    }
}
