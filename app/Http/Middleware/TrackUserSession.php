<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserSession;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track sessions for authenticated users
        if (Auth::check()) {
            $this->trackSession($request);
        }

        return $response;
    }

    /**
     * Track user session
     */
    private function trackSession(Request $request)
    {
        $user = Auth::user();
        $sessionId = session()->getId();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        
        // Parse user agent
        $parsed = UserSession::parseUserAgent($userAgent);
        $location = UserSession::getLocationFromIp($ipAddress);
        
        // Update or create session record
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
                'expires_at' => now()->addMinutes(config('session.lifetime', 120))
            ]
        );
    }
}