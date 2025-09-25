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

    /**
     * Calculate total hours worked
     */
    public function calculateTotalHours(): float
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $totalMinutes = $this->time_out->diffInMinutes($this->time_in);
        
        // Subtract break time if exists
        if ($this->break_start && $this->break_end) {
            $breakMinutes = $this->break_end->diffInMinutes($this->break_start);
            $totalMinutes -= $breakMinutes;
        }

        return round($totalMinutes / 60, 2);
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
}
