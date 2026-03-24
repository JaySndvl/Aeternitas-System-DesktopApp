<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HrContact extends Model
{
    use HasUuids;

    protected $table = 'hr_contacts';

    protected $fillable = [
        'employee_id',
        'user_id',
        'subject',
        'message',
        'category',
        'status',
        'response',
        'responded_by',
        'responded_at'
    ];

    protected $casts = [
        'responded_at' => 'datetime'
    ];

    /**
     * Get the employee who sent the contact
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who sent the contact
     */
    public function user()
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    /**
     * Get the HR person who responded
     */
    public function responder()
    {
        return $this->belongsTo(Account::class, 'responded_by');
    }

    /**
     * Scope for pending contacts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for resolved contacts
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
