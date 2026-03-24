<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class AttendanceException extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'date',
        'type',
        'name',
        'description',
        'is_paid',
        'is_working_day',
    ];

    protected $casts = [
        'date' => 'date',
        'is_paid' => 'boolean',
        'is_working_day' => 'boolean',
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
     * Check if a date is a holiday
     */
    public static function isHoliday($date)
    {
        return static::where('date', $date)
            ->where('type', 'holiday')
            ->exists();
    }

    /**
     * Check if a date is a working day despite being weekend
     */
    public static function isSpecialWorkingDay($date)
    {
        return static::where('date', $date)
            ->where('is_working_day', true)
            ->exists();
    }
}
