<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'schedule_name',
        'monday_start',
        'monday_end',
        'tuesday_start',
        'tuesday_end',
        'wednesday_start',
        'wednesday_end',
        'thursday_start',
        'thursday_end',
        'friday_start',
        'friday_end',
        'saturday_start',
        'saturday_end',
        'sunday_start',
        'sunday_end',
        'is_active',
        'effective_date',
        'end_date',
    ];

    protected $casts = [
        'monday_start' => 'datetime:H:i',
        'monday_end' => 'datetime:H:i',
        'tuesday_start' => 'datetime:H:i',
        'tuesday_end' => 'datetime:H:i',
        'wednesday_start' => 'datetime:H:i',
        'wednesday_end' => 'datetime:H:i',
        'thursday_start' => 'datetime:H:i',
        'thursday_end' => 'datetime:H:i',
        'friday_start' => 'datetime:H:i',
        'friday_end' => 'datetime:H:i',
        'saturday_start' => 'datetime:H:i',
        'saturday_end' => 'datetime:H:i',
        'sunday_start' => 'datetime:H:i',
        'sunday_end' => 'datetime:H:i',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
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

    /**
     * Get work hours for a specific day
     */
    public function getWorkHoursForDay(string $dayOfWeek): ?array
    {
        $dayOfWeek = strtolower($dayOfWeek);
        $startTime = $this->{$dayOfWeek . '_start'};
        $endTime = $this->{$dayOfWeek . '_end'};

        if (!$startTime || !$endTime) {
            return null;
        }

        return [
            'start' => $startTime,
            'end' => $endTime,
        ];
    }

    /**
     * Check if a specific day is a working day
     */
    public function isWorkingDay(string $dayOfWeek): bool
    {
        $dayOfWeek = strtolower($dayOfWeek);
        return !is_null($this->{$dayOfWeek . '_start'}) && !is_null($this->{$dayOfWeek . '_end'});
    }

    /**
     * Get total working hours per week
     */
    public function getTotalWeeklyHours(): float
    {
        $totalHours = 0;
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            if ($this->isWorkingDay($day)) {
                $startTime = $this->{$day . '_start'};
                $endTime = $this->{$day . '_end'};
                
                if ($startTime && $endTime) {
                    $start = Carbon::parse($startTime);
                    $end = Carbon::parse($endTime);
                    $totalHours += $end->diffInHours($start);
                }
            }
        }

        return $totalHours;
    }

    /**
     * Check if schedule is effective for a given date
     */
    public function isEffectiveForDate(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($date->lt($this->effective_date)) {
            return false;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        return true;
    }
}
