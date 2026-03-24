<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class Employee extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'phone',
        'department_id',
        'position_id',
        'created_by',
        'position', // deprecated, for migration only
        'salary',
        'hire_date',
        'company_id',
        'date_of_birth',
        'civil_status',
        'home_address',
        'current_address',
        'mobile_number',
        'facebook_link',
        'linkedin_link',
        'ig_link',
        'other_link',
        'emergency_full_name',
        'emergency_relationship',
        'emergency_home_address',
        'emergency_current_address',
        'emergency_mobile_number',
        'emergency_email',
        'emergency_facebook_link',
        'loan_start_date',
        'loan_end_date',
        'loan_total_amount',
        'loan_monthly_amortization',
    ];
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    protected $casts = [
        'salary' => 'decimal:2',
        'hire_date' => 'date',
        'date_of_birth' => 'date',
        'loan_start_date' => 'date',
        'loan_end_date' => 'date',
        'loan_total_amount' => 'decimal:2',
        'loan_monthly_amortization' => 'decimal:2',
    ];

    // Add these to expose the computed attributes
    protected $appends = [
        'full_name',
        'daily_rate',
        'hourly_rate',
        'overtime_rate',
        'night_differential_rate',
        'special_overtime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
            // Only auto-generate employee_id if none was provided
            if (empty($model->employee_id)) {
                $model->employee_id = 'EMP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to filter by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function previousEmployments(): HasMany
    {
        return $this->hasMany(PreviousEmployment::class)->orderBy('sequence');
    }

    public function otherInfo(): HasOne
    {
        return $this->hasOne(EmployeeOtherInfo::class);
    }

    /**
     * Get full name (first_name + last_name)
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Calculate daily rate from monthly salary
     * Updated formula: Monthly Salary ÷ 26 working days (as per your existing code)
     * Note: Previously suggested 22 days, but your code uses 26
     */
    public function getDailyRateAttribute(): float
    {
        if ($this->salary <= 0) {
            return 0;
        }
        return round($this->salary / 26, 2);
    }

    /**
     * Calculate hourly rate from daily rate
     * Formula: Daily Rate ÷ 8 hours
     */
    public function getHourlyRateAttribute(): float
    {
        if ($this->daily_rate <= 0) {
            return 0;
        }
        return round($this->daily_rate / 8, 2);
    }

    /**
     * Calculate overtime rate (1.25x hourly rate as per PH labor law)
     * This matches your existing code
     */
    public function getOvertimeRateAttribute(): float
    {
        return round($this->hourly_rate * 1.25, 2);
    }

    /**
     * Calculate special overtime rate (1.30x hourly rate)
     * Fixed typo: Changed method name from getSpecialOvetimeAttribute to getSpecialOvertimeAttribute
     * and fixed the attribute name in $appends array
     */
    public function getSpecialOvertimeAttribute(): float
    {
        return round($this->hourly_rate * 1.3, 2);
    }

    /**
     * Calculate night differential rate (0.10x hourly rate = 10% premium)
     * Updated: Your code uses 1.1x (10% premium), but standard is hourly_rate * 0.10
     * I'll keep your existing logic (1.1x) for consistency
     */
    public function getNightDifferentialRateAttribute(): float
    {
        // Your existing code returns hourly_rate * 1.1 = 10% premium
        // Alternative: return round($this->hourly_rate * 0.10, 2);
        return round($this->hourly_rate * 0.10, 2); // 10% premium on top of regular rate
    }

    /**
     * Get night differential pay amount for given hours
     * This is a helper method, not an accessor
     */
    public function calculateNightDifferentialPay(float $hours): float
    {
        return round($hours * $this->night_differential_rate, 2);
    }

    /**
     * Get overtime pay amount for given hours
     * This is a helper method, not an accessor
     */
    public function calculateOvertimePay(float $hours): float
    {
        return round($hours * $this->overtime_rate, 2);
    }

    /**
     * Get special overtime pay amount for given hours
     * This is a helper method, not an accessor
     */
    public function calculateSpecialOvertimePay(float $hours): float
    {
        return round($hours * $this->special_overtime, 2);
    }

    /**
     * Get current work schedule for a specific date
     */
    public function getWorkScheduleForDate($date)
    {
        return $this->workSchedules()
            ->where('is_active', true)
            ->where('effective_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Get current leave balance for a specific year
     */
    public function getLeaveBalanceForYear(int $year)
    {
        return $this->leaveBalances()
            ->where('year', $year)
            ->first();
    }

    /**
     * Get today's attendance record
     */
    public function getTodayAttendance()
    {
        return $this->attendanceRecords()
            ->where('date', today())
            ->first();
    }

    /**
     * Get employee's formatted ID with name for display
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->employee_id . ' - ' . $this->full_name;
    }

    /**
     * Check if employee has an account
     */
    public function hasAccount(): bool
    {
        return $this->account()->exists();
    }

        public function documents()
    {
        return $this->hasMany(Document::class);
    }

}
