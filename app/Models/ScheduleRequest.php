<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleRequest extends Model
{
    use HasFactory;

    protected $table = 'schedule_requests';
    protected $fillable = [
        'employee_id',
        'work_date',
        'shift_id',
        'duty_type',
        'status',
        'reason',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Quan hệ với Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Quan hệ với Shift
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // Quan hệ với User (admin xác nhận)
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Kiểm tra trạng thái
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Scope: Chỉ lấy request pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope: Chỉ lấy request approved
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope: Chỉ lấy request của một nhân viên
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Scope: Lấy các request trong khoảng ngày
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }
}