<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class TempTimekeeping extends Model
{
    use HasUuids;

    protected $table = 'temp_timekeeping';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'employee_name',
        'date',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'total_hours',
        'regular_hours',
        'overtime_hours',
        'status',
        'schedule_status',
        'notes',
        'validation_errors',
        'import_batch_id',
        'is_processed'
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
        'is_processed' => 'boolean',
    ];

    /**
     * Get the employee associated with this temp record
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Scope to get records by import batch
     */
    public function scopeByBatch($query, $batchId)
    {
        return $query->where('import_batch_id', $batchId);
    }

    /**
     * Scope to get unprocessed records
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    /**
     * Scope to get processed records
     */
    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    /**
     * Generate a unique batch ID for import
     */
    public static function generateBatchId()
    {
        return 'batch_' . time() . '_' . uniqid();
    }
}
