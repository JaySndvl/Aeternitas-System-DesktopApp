<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreviousEmployment extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'sequence',
        'employment_name',
        'position',
        'start_month',
        'start_year',
        'end_month',
        'end_year',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'start_month' => 'integer',
        'start_year' => 'integer',
        'end_month' => 'integer',
        'end_year' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
