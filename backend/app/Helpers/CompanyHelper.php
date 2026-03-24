<?php

namespace App\Helpers;

use App\Models\Company;

class CompanyHelper
{
    /**
     * Get the current company ID from session
     *
     * @return string|null
     */
    public static function getCurrentCompanyId()
    {
        return session('current_company_id');
    }

    /**
     * Get the current company model from session
     *
     * @return Company|null
     */
    public static function getCurrentCompany()
    {
        $companyId = self::getCurrentCompanyId();
        
        if (!$companyId) {
            return null;
        }
        
        return Company::find($companyId);
    }

    /**
     * Set the current company in session
     *
     * @param string|Company $company
     * @return void
     */
    public static function setCurrentCompany($company)
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        session(['current_company_id' => $companyId]);
    }

    /**
     * Clear the current company from session
     *
     * @return void
     */
    public static function clearCurrentCompany()
    {
        session()->forget('current_company_id');
    }

    /**
     * Check if a company is set in session
     *
     * @return bool
     */
    public static function hasCompany()
    {
        return !is_null(self::getCurrentCompanyId());
    }

    /**
     * Get all available companies for the authenticated user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAvailableCompanies()
    {
        return Company::where('is_active', true)->orderBy('name')->get();
    }
}

