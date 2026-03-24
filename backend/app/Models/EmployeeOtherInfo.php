<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOtherInfo extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'address',
        'pov_address',
        'no_street',
        'barangay',
        'town_district',
        'city_province',
        'birthplace',
        'religion',
        'blood_type',
        'citizenship',
        'height',
        'weight',
        'phone',
        'mobile',
        'drivers_license',
        'prc_no',
        'father',
        'mother',
        'spouse',
        'spouse_employed',
        'photo_path',
    ];

    protected $casts = [
        'spouse_employed' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
