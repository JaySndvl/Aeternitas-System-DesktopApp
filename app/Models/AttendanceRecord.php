<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class AttendanceRecord extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'total_hours',
        'regular_hours',
        'overtime_hours',
        'night_shift',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'total_hours' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'night_shift' => 'boolean',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(EmployeeBreak::class);
    }

    /**
     * Calculate total hours worked
     */
    public function calculateTotalHours(): float
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        // Ensure both times are Carbon instances
        $timeIn = $this->time_in instanceof \Carbon\Carbon ? $this->time_in : \Carbon\Carbon::parse($this->time_in);
        $timeOut = $this->time_out instanceof \Carbon\Carbon ? $this->time_out : \Carbon\Carbon::parse($this->time_out);
        
        // Ensure both times are in the same timezone
        $timeIn->setTimezone(config('app.timezone', 'Asia/Manila'));
        $timeOut->setTimezone(config('app.timezone', 'Asia/Manila'));
        
        // Calculate total minutes
        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        
        // If diffInMinutes returns 0 or negative, try alternative calculation
        if ($totalMinutes <= 0) {
            // Use timestamp difference as fallback
            $totalSeconds = $timeOut->timestamp - $timeIn->timestamp;
            $totalMinutes = max(0, round($totalSeconds / 60));
        }
        
        // Subtract total break time from all breaks
        $totalBreakMinutes = $this->getTotalBreakMinutes();
        $totalMinutes = max(0, $totalMinutes - $totalBreakMinutes);

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get total break minutes from all breaks
     */
    public function getTotalBreakMinutes(): int
    {
        // Use breaks relationship if available
        if ($this->relationLoaded('breaks')) {
            return $this->breaks->sum(function ($break) {
                if ($break->break_end) {
                    return $break->break_duration_minutes ?? $break->break_start->diffInMinutes($break->break_end);
                }
                // If break is still active, calculate up to now
                return $break->break_start->diffInMinutes(now());
            });
        }

        // Fallback to old break_start/break_end fields for backward compatibility
        if ($this->break_start && $this->break_end) {
            return $this->break_end->diffInMinutes($this->break_start);
        }

        // Check if there's an active break
        if ($this->break_start && !$this->break_end) {
            return $this->break_start->diffInMinutes(now());
        }

        return 0;
    }

    /**
     * Get total break hours
     */
    public function getTotalBreakHours(): float
    {
        return round($this->getTotalBreakMinutes() / 60, 2);
    }

    /**
     * Check if total break exceeds 1.5 hours (90 minutes)
     */
    public function isOverBreak(): bool
    {
        return $this->getTotalBreakMinutes() > 90; // 1.5 hours = 90 minutes
    }

    /**
     * Get over break minutes (how many minutes over 1.5 hours)
     */
    public function getOverBreakMinutes(): int
    {
        $totalMinutes = $this->getTotalBreakMinutes();
        return max(0, $totalMinutes - 90);
    }

    /**
     * Calculate regular and overtime hours
     */
    public function calculateRegularAndOvertimeHours(): array
    {
        $totalHours = $this->calculateTotalHours();
        $regularHours = min($totalHours, 8); // 8 hours regular
        $overtimeHours = max(0, $totalHours - 8);

        return [
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
        ];
    }

    /**
     * Check if employee is late
     */
    public function isLate(): bool
    {
        if (!$this->time_in) {
            return false;
        }

        // Get employee's work schedule for this date
        $schedule = $this->employee->getWorkScheduleForDate($this->date);
        if (!$schedule) {
            return false;
        }

        $dayOfWeek = strtolower($this->date->format('l'));
        $expectedStartTime = $schedule->{$dayOfWeek . '_start'};
        
        if (!$expectedStartTime) {
            return false;
        }

        $gracePeriod = 15; // 15 minutes grace period
        $expectedTime = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $expectedStartTime);
        $actualTime = $this->time_in;

        return $actualTime->gt($expectedTime->addMinutes($gracePeriod));
    }

    /**
     * Get status based on attendance data
     */
    public function getCalculatedStatus(): string
    {
        if (!$this->time_in && !$this->time_out) {
            return 'absent';
        }

        if ($this->time_in && !$this->time_out) {
            return 'present'; // Currently working
        }

        if ($this->time_in && $this->time_out) {
            $totalHours = $this->calculateTotalHours();
            if ($totalHours < 4) {
                return 'half_day';
            }
            return $this->isLate() ? 'late' : 'present';
        }

        return 'absent';
    }

    /**
     * Check if this attendance record is a night shift (10pm-6am)
     */
    public function isNightShift(): bool
    {
        if (!$this->time_in || !$this->time_out) {
            return false;
        }

        $timeIn = \Carbon\Carbon::parse($this->time_in);
        $timeOut = \Carbon\Carbon::parse($this->time_out);
        
        // Night shift period: 10:00 PM (22:00) to 6:00 AM (06:00)
        $nightStart = 22; // 10 PM
        $nightEnd = 6;    // 6 AM
        
        // Convert times to minutes for easier calculation
        $timeInMinutes = $timeIn->hour * 60 + $timeIn->minute;
        $timeOutMinutes = $timeOut->hour * 60 + $timeOut->minute;
        
        // Determine if work spans across midnight
        $spansMidnight = $timeOutMinutes < $timeInMinutes;
        
        if ($spansMidnight) {
            // Work spans across midnight (e.g., 10 PM to 2 AM)
            $midnightMinutes = 24 * 60; // 1440 minutes
            
            // Check if time_in is in night period (10 PM to midnight)
            if ($timeInMinutes >= $nightStart * 60) {
                return true;
            }
            
            // Check if time_out is in night period (midnight to 6 AM)
            if ($timeOutMinutes <= $nightEnd * 60) {
                return true;
            }
        } else {
            // Work within the same day
            $nightStartMinutes = $nightStart * 60; // 10 PM = 1320 minutes
            $nightEndMinutes = $nightEnd * 60;     // 6 AM = 360 minutes
            $midnightMinutes = 24 * 60;            // 1440 minutes
            
            // Check if work overlaps with evening night period (10 PM to midnight)
            if ($timeInMinutes >= $nightStartMinutes && $timeInMinutes < $midnightMinutes) {
                return true;
            }
            
            // Check if work overlaps with early morning night period (midnight to 6 AM)
            if ($timeInMinutes < $nightEndMinutes && $timeOutMinutes > $timeInMinutes) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Calculate night shift hours (10pm-6am)
     */
    public function calculateNightShiftHours(): float
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $timeIn = \Carbon\Carbon::parse($this->time_in);
        $timeOut = \Carbon\Carbon::parse($this->time_out);
        
        // Night shift period: 10:00 PM (22:00) to 6:00 AM (06:00)
        $nightStart = 22; // 10 PM
        $nightEnd = 6;    // 6 AM
        
        $nightShiftHours = 0;
        
        // Convert times to minutes for easier calculation
        $timeInMinutes = $timeIn->hour * 60 + $timeIn->minute;
        $timeOutMinutes = $timeOut->hour * 60 + $timeOut->minute;
        
        // Determine if work spans across midnight
        $spansMidnight = $timeOutMinutes < $timeInMinutes;
        
        if ($spansMidnight) {
            // Work spans across midnight (e.g., 10 PM to 2 AM)
            $midnightMinutes = 24 * 60; // 1440 minutes
            
            // Check if time_in is in night period (10 PM to midnight)
            if ($timeInMinutes >= $nightStart * 60) {
                $nightShiftHours += ($midnightMinutes - $timeInMinutes) / 60;
            }
            
            // Check if time_out is in night period (midnight to 6 AM)
            if ($timeOutMinutes <= $nightEnd * 60) {
                $nightShiftHours += $timeOutMinutes / 60;
            }
        } else {
            // Work within the same day
            $nightStartMinutes = $nightStart * 60; // 10 PM = 1320 minutes
            $nightEndMinutes = $nightEnd * 60;     // 6 AM = 360 minutes
            $midnightMinutes = 24 * 60;            // 1440 minutes
            
            // Check if work overlaps with evening night period (10 PM to midnight)
            if ($timeInMinutes >= $nightStartMinutes && $timeInMinutes < $midnightMinutes) {
                $eveningEnd = min($timeOutMinutes, $midnightMinutes);
                $nightShiftHours += ($eveningEnd - $timeInMinutes) / 60;
            }
            
            // Check if work overlaps with early morning night period (midnight to 6 AM)
            if ($timeInMinutes <= $nightEndMinutes && $timeOutMinutes > 0) {
                $morningStart = max($timeInMinutes, 0);
                $morningEnd = min($timeOutMinutes, $nightEndMinutes);
                $nightShiftHours += ($morningEnd - $morningStart) / 60;
            }
        }
        
        return round($nightShiftHours, 2);
    }
}
