<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class Payroll extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'employee_id',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'monthly_rate',
        'semi_monthly_rate',
        'daily_rate',
        'hourly_rate',
        'holiday_basic_pay',
        'holiday_premium',
        'special_holiday_premium',
        'regular_holiday_days',
        'special_holiday_days',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'scheduled_hours',
        'night_differential_hours',
        'night_differential_rate',
        'night_differential_pay',
        'rest_day_premium_pay',
        'allowances',
        'bonuses',
        'deductions',
        'sss', // MISSING FROM YOUR TABLE - ADD THIS
        'phic', // MISSING FROM YOUR TABLE - ADD THIS
        'hdmf', // MISSING FROM YOUR TABLE - ADD THIS
        'tax_amount',
        'gross_pay',
        'net_pay',
        'status',
        'processed_at',
        'approved_by',
        'approved_at',
        'paid_at',
        'paid_by',
        'notes',
        'payslip_file',
        'payment_reference',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'semi_monthly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'holiday_basic_pay' => 'decimal:2',
        'holiday_premium' => 'decimal:2',
        'special_holiday_premium' => 'decimal:2',
        'regular_holiday_days' => 'integer',
        'special_holiday_days' => 'integer',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'scheduled_hours' => 'decimal:2',
        'night_differential_hours' => 'decimal:2',
        'night_differential_rate' => 'decimal:2',
        'night_differential_pay' => 'decimal:2',
        'rest_day_premium_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'sss' => 'decimal:2', // ADD THIS
        'phic' => 'decimal:2', // ADD THIS
        'hdmf' => 'decimal:2', // ADD THIS
        'tax_amount' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'notes' => 'string',
        'payslip_file' => 'string',
        'payment_reference' => 'string',
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

    // Add this scope to your Payroll model
    public function scopeLatestPerEmployee($query)
    {
        return $query->whereIn('id', function($subquery) {
            $subquery->select('id')
                ->from('payrolls as p2')
                ->whereRaw('p2.employee_id = payrolls.employee_id')
                ->whereRaw('p2.pay_period_start = payrolls.pay_period_start')
                ->whereRaw('p2.pay_period_end = payrolls.pay_period_end')
                ->orderBy('p2.created_at', 'desc')
                ->limit(1);
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class, 'approved_by');
    }

    /**
     * Payments relationship (one payroll may have many payments)
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Latest/primary payment (one-to-one)
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get formatted status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'approved' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get overtime pay amount (calculated if not set)
     */
    public function getOvertimePayAttribute(): float
    {
        if ($this->attributes['overtime_pay'] !== null) {
            return (float) $this->attributes['overtime_pay'];
        }
        
        return round(($this->overtime_hours ?? 0) * ($this->overtime_rate ?? 0), 2);
    }

    /**
     * Get night differential pay (calculated if not set)
     */
    public function getNightDifferentialPayAttribute(): float
    {
        if ($this->attributes['night_differential_pay'] !== null) {
            return (float) $this->attributes['night_differential_pay'];
        }
        
        return round(($this->night_differential_hours ?? 0) * ($this->night_differential_rate ?? 0), 2);
    }

    /**
     * Get total statutory deductions - BUT CHECK IF COLUMNS EXIST FIRST
     */
    public function getTotalStatutoryDeductionsAttribute(): float
    {
        $total = 0;
        
        // Check if columns exist before accessing
        if (isset($this->attributes['sss'])) {
            $total += (float) $this->attributes['sss'];
        }
        if (isset($this->attributes['phic'])) {
            $total += (float) $this->attributes['phic'];
        }
        if (isset($this->attributes['hdmf'])) {
            $total += (float) $this->attributes['hdmf'];
        }
        
        return $total;
    }

    /**
     * Get total deductions (statutory + other)
     */
    public function getTotalDeductionsAttribute(): float
    {
        $deductions = (float) ($this->deductions ?? 0);
        $tax = (float) ($this->tax_amount ?? 0);
        $statutory = $this->total_statutory_deductions;
        
        return $deductions + $tax + $statutory;
    }
}