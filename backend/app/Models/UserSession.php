<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class UserSession extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'location',
        'is_current',
        'last_activity',
        'login_at',
        'expires_at',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'login_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }

    public function newUniqueId()
    {
        return (string) Uuid::uuid4();
    }

    public function uniqueIds()
    {
        return ['id'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    /**
     * Get device icon based on device type
     */
    public function getDeviceIconAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'fas fa-mobile-alt',
            'tablet' => 'fas fa-tablet-alt',
            'desktop' => 'fas fa-desktop',
            default => 'fas fa-laptop'
        };
    }

    /**
     * Get browser icon
     */
    public function getBrowserIconAttribute(): string
    {
        return match(strtolower($this->browser)) {
            'chrome' => 'fab fa-chrome',
            'firefox' => 'fab fa-firefox-browser',
            'safari' => 'fab fa-safari',
            'edge' => 'fab fa-edge',
            'opera' => 'fab fa-opera',
            default => 'fas fa-globe'
        };
    }

    /**
     * Check if session is active (not expired)
     */
    public function isActive(): bool
    {
        return $this->expires_at > now();
    }

    /**
     * Get formatted last activity
     */
    public function getFormattedLastActivityAttribute(): string
    {
        return $this->last_activity->diffForHumans();
    }

    /**
     * Get session duration
     */
    public function getDurationAttribute(): string
    {
        return $this->login_at->diffForHumans($this->last_activity, true);
    }

    /**
     * Parse user agent to extract browser and OS info
     */
    public static function parseUserAgent(string $userAgent): array
    {
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $os = 'Unknown';

        // Detect device type
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Detect browser
        if (preg_match('/Chrome/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Opera/', $userAgent)) {
            $browser = 'Opera';
        }

        // Detect OS
        if (preg_match('/Windows/', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS/', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $os = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os
        ];
    }

    /**
     * Get location from IP (simplified - in production, use a proper geolocation service)
     */
    public static function getLocationFromIp(string $ip): string
    {
        // For demo purposes, return a placeholder
        // In production, integrate with services like MaxMind GeoIP2, IPStack, etc.
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local Development';
        }
        
        // You can integrate with external services here
        return 'Manila, Philippines'; // Placeholder
    }

public function loginLogs()
{
    return $this->hasMany(LoginLog::class);
}
}

