<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneHelper
{
    /**
     * Get the application timezone
     */
    public static function getAppTimezone(): string
    {
        return config('app.timezone', 'Asia/Manila');
    }

    /**
     * Get current time in application timezone
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::getAppTimezone());
    }

    /**
     * Convert a date to application timezone
     */
    public static function toAppTimezone($date, $fromTimezone = null): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->setTimezone(self::getAppTimezone());
        }

        $carbon = Carbon::parse($date);
        
        if ($fromTimezone) {
            $carbon = $carbon->setTimezone($fromTimezone);
        }
        
        return $carbon->setTimezone(self::getAppTimezone());
    }

    /**
     * Format date for display in Philippine timezone
     */
    public static function formatForDisplay($date, $format = 'Y-m-d H:i:s'): string
    {
        return self::toAppTimezone($date)->format($format);
    }

    /**
     * Get Philippine timezone offset
     */
    public static function getOffset(): string
    {
        return '+08:00';
    }

    /**
     * Check if current time is within business hours (8 AM - 5 PM Philippine Time)
     */
    public static function isBusinessHours(): bool
    {
        $now = self::now();
        $hour = $now->hour;
        
        return $hour >= 8 && $hour < 17;
    }

    /**
     * Get business hours start and end
     */
    public static function getBusinessHours(): array
    {
        return [
            'start' => '08:00',
            'end' => '17:00',
            'timezone' => self::getAppTimezone()
        ];
    }

    /**
     * Get current date in Philippine timezone
     */
    public static function today(): Carbon
    {
        return self::now()->startOfDay();
    }

    /**
     * Get start of week (Monday) in Philippine timezone
     */
    public static function startOfWeek(): Carbon
    {
        return self::now()->startOfWeek();
    }

    /**
     * Get end of week (Sunday) in Philippine timezone
     */
    public static function endOfWeek(): Carbon
    {
        return self::now()->endOfWeek();
    }

    /**
     * Get start of month in Philippine timezone
     */
    public static function startOfMonth(): Carbon
    {
        return self::now()->startOfMonth();
    }

    /**
     * Get end of month in Philippine timezone
     */
    public static function endOfMonth(): Carbon
    {
        return self::now()->endOfMonth();
    }

    /**
     * Format decimal hours to human readable format
     * 
     * @param float $decimalHours
     * @return string
     */
    public static function formatHours(float $decimalHours): string
    {
        if ($decimalHours <= 0) {
            return '0h';
        }

        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        
        // Handle edge case where minutes round to 60
        if ($minutes == 60) {
            $hours++;
            $minutes = 0;
        }

        if ($minutes > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$hours}h";
    }
}
