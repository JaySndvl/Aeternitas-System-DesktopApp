<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class TaxBracket extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'min_income',
        'max_income',
        'tax_rate',
        'base_tax',
        'excess_over',
        'sort_order',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'base_tax' => 'decimal:2',
        'excess_over' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
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
     * Scope to get active tax brackets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tax brackets for a specific date
     */
    public function scopeForDate($query, $date = null)
    {
        $date = $date ?: now();
        
        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>=', $date);
        });
    }

    /**
     * Scope to get tax brackets ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('min_income');
    }

    /**
     * Check if income falls within this bracket
     */
    public function isIncomeInBracket($income)
    {
        if ($income < $this->min_income) {
            return false;
        }
        
        if ($this->max_income !== null && $income > $this->max_income) {
            return false;
        }
        
        return true;
    }

    /**
     * Calculate tax for a given income
     */
    public function calculateTax($income)
    {
        if (!$this->isIncomeInBracket($income)) {
            return 0;
        }

        // For the first bracket (exempt), return 0
        if ($this->tax_rate == 0) {
            return 0;
        }

        // Calculate excess amount (income minus the threshold)
        $excess = $income - $this->excess_over;
        
        // Calculate tax on excess
        $taxOnExcess = $excess * ($this->tax_rate / 100);
        
        // Total tax = base tax + tax on excess
        return $this->base_tax + $taxOnExcess;
    }
}
