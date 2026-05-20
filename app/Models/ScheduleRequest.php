<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleRequest extends Model
{
    use HasFactory;

    protected $table = 'shift_assignments';

    protected $fillable = [
        'employee_id',
        'work_date',
        'shift_id',
        'assignment_type',
        'notes',
        'assigned_by',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'notes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ✅ Quan hệ với Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * ✅ Quan hệ với CustomShift
     */
    public function shift()
    {
        return $this->belongsTo(CustomShift::class, 'shift_id');
    }

    /**
     * ✅ Quan hệ với User (người assign)
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * ✅ Scope: Lấy đơn pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * ✅ Scope: Lấy đơn approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * ✅ Scope: Lấy đơn rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * ✅ Scope: Lấy theo ngày
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('work_date', $date);
    }

    /**
     * ✅ Accessor: Format ngày làm
     */
    public function getFormattedDateAttribute()
    {
        return $this->work_date->format('d/m/Y');
    }

    /**
     * ✅ Accessor: Lấy thông tin ca (bao gồm custom hours nếu có)
     */
    public function getShiftDetailsAttribute()
    {
        if ($this->notes && is_array($this->notes)) {
            return [
                'shift_name' => $this->shift?->name ?? 'Ca không xác định',
                'start_time' => sprintf('%02d:%02d', $this->notes['start_hour'] ?? 0, $this->notes['start_minute'] ?? 0),
                'end_time' => sprintf('%02d:%02d', $this->notes['end_hour'] ?? 0, $this->notes['end_minute'] ?? 0),
                'is_custom' => true,
            ];
        }

        return [
            'shift_name' => $this->shift?->name ?? 'Ca không xác định',
            'start_time' => sprintf('%02d:%02d', $this->shift?->start_hour ?? 0, $this->shift?->start_minute ?? 0),
            'end_time' => sprintf('%02d:%02d', $this->shift?->end_hour ?? 0, $this->shift?->end_minute ?? 0),
            'is_custom' => false,
        ];
    }
}