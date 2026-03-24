<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'amount',
        'status',
        'transaction_id',
        'paid_at',
        'meta',
        'payment_method',      // Add this
        'payment_reference',   // Add this
        'notes',               // Add this
        'processed_by',        // Add this
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Payment belongs to a Payroll
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Payroll::class);
    }

    /**
     * Payment belongs to an Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }

    /**
     * Payment processed by an Account (user)
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class, 'processed_by');
    }
}
