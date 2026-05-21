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
        'start_hour',
        'start_minute',
        'end_hour',
        'end_minute',
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
     * ✅ Accessor: Lấy time_range từ các cột start_hour, start_minute, end_hour, end_minute
     */
    public function getTimeRangeAttribute()
    {
        // Nếu có custom hours (cột không NULL)
        if (!is_null($this->start_hour) && !is_null($this->end_hour)) {
            $start_h = str_pad($this->start_hour, 2, '0', STR_PAD_LEFT);
            $start_m = str_pad($this->start_minute ?? 0, 2, '0', STR_PAD_LEFT);
            $end_h = str_pad($this->end_hour, 2, '0', STR_PAD_LEFT);
            $end_m = str_pad($this->end_minute ?? 0, 2, '0', STR_PAD_LEFT);
            return "{$start_h}:{$start_m} - {$end_h}:{$end_m}";
        }
        
        // Nếu không có, lấy từ shift
        return $this->shift?->time_range ?? 'Tùy chỉnh';
    }

    /**
     * ✅ Accessor: Lấy thông tin ca
     */
    public function getShiftDetailsAttribute()
    {
        if (!is_null($this->start_hour) && !is_null($this->end_hour)) {
            return [
                'shift_name' => $this->shift?->name ?? 'Ca không xác định',
                'start_time' => sprintf('%02d:%02d', $this->start_hour, $this->start_minute ?? 0),
                'end_time' => sprintf('%02d:%02d', $this->end_hour, $this->end_minute ?? 0),
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