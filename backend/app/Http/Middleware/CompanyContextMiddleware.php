<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CompanyHelper;
use Symfony\Component\HttpFoundation\Response;

class CompanyContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users
        if (Auth::check()) {
            // If no company is selected, select the first active company
            if (!CompanyHelper::hasCompany()) {
                $firstCompany = \App\Models\Company::where('is_active', true)->first();
                
                if ($firstCompany) {
                    CompanyHelper::setCurrentCompany($firstCompany);
                }
            }
        }

        return $next($request);
    }
}
