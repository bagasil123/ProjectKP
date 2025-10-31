<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\CarbonPeriod;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'start_date', 'end_date', 'type', 'reason', 'attachment_path', 'status'
    ];

    // Relasi ke model Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_Auto');
    }
}