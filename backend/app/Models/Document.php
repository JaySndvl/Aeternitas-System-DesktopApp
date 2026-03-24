<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'employee_id',
        'name',
        'type',
        'path',
        'description'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}