<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class EmployeeBreak extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'employee_breaks';

    protected $fillable = [
        'attendance_record_id',
        'break_start',
        'break_end',
        'break_duration_minutes',
    ];

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
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
            // Calculate break duration when break_end is set
            if ($model->isDirty('break_end') && $model->break_end) {
                $model->break_duration_minutes = $model->break_start->diffInMinutes($model->break_end);
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

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Get break duration in hours
     */
    public function getDurationInHours(): float
    {
        if (!$this->break_end) {
            return 0;
        }
        return round($this->break_duration_minutes / 60, 2);
    }
}
