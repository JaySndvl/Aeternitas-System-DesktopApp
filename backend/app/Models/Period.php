<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class Period extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'department_id',
        'employee_ids',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'employee_ids' => 'array',
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

    /**
     * Get the department that owns the period.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employees for this period.
     */
    public function employees()
    {
        if (empty($this->employee_ids)) {
            return collect();
        }
        
        return Employee::whereIn('id', $this->employee_ids)->get();
    }

    /**
     * Get the duration of the period in days.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if the period is currently active.
     */
    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $this->start_date <= $today && $this->end_date >= $today;
    }

    /**
     * Check if the period is in the past.
     */
    public function isPast(): bool
    {
        return $this->end_date < now()->toDateString();
    }

    /**
     * Check if the period is in the future.
     */
    public function isFuture(): bool
    {
        return $this->start_date > now()->toDateString();
    }
}
