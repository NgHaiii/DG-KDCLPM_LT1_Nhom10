<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OffDay extends Model
{
    use HasFactory;

    protected $table = 'off_days';
    protected $fillable = [
        'employee_id',
        'date',
        'reason',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Quan hệ với Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
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

    // Scope: Ngày nghỉ đã được phê duyệt
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope: Ngày nghỉ chưa được xử lý
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}