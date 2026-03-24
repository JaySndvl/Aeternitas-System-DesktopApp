<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class TimeEntry extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'attendance_record_id',
        'time_in',
        'time_out',
        'hours_worked',
        'entry_type',
        'notes',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'hours_worked' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });

        static::updating(function ($model) {
            // Auto-calculate hours worked when time_out is set
            if ($model->isDirty('time_out') && $model->time_out) {
                $model->hours_worked = $model->calculateHoursWorked();
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

    /**
     * Relationship to attendance record
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Calculate hours worked for this entry
     */
    public function calculateHoursWorked(): float
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        $timeIn = $this->time_in instanceof Carbon ? $this->time_in : Carbon::parse($this->time_in);
        $timeOut = $this->time_out instanceof Carbon ? $this->time_out : Carbon::parse($this->time_out);

        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        
        return round($totalMinutes / 60, 2);
    }

    /**
     * Check if this entry is currently active (clocked in but not out)
     */
    public function isActive(): bool
    {
        return $this->time_in && !$this->time_out;
    }

    /**
     * Get duration as human readable string
     */
    public function getDurationAttribute(): string
    {
        if (!$this->time_out) {
            return 'In progress';
        }

        $hours = floor($this->hours_worked);
        $minutes = round(($this->hours_worked - $hours) * 60);

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Scope for active entries (no time_out)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('time_out');
    }

    /**
     * Scope for completed entries (has time_out)
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('time_out');
    }
}
