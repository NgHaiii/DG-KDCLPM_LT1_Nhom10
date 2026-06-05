<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $table = 'shift_assignments';

    protected $fillable = [
        'employee_id',
        'work_date',
        'shift_id',
        'start_hour',
        'start_minute',
        'end_hour',
        'end_minute',
        'assignment_type',
        'status',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_hour' => 'integer',
        'start_minute' => 'integer',
        'end_hour' => 'integer',
        'end_minute' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONS =====
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CustomShift::class, 'shift_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ===== AUTHORIZATION SCOPES (SECURITY) =====
    
    /**
     * ✅ Scope: Chỉ lấy dữ liệu của bác sĩ hiện tại
     * Tránh: Bác sĩ A xem dữ liệu của bác sĩ B
     * 
     * Sử dụng: ShiftAssignment::forCurrentDoctor()->get()
     */
    public function scopeForCurrentDoctor($query)
    {
        $user = Auth::user();
        if (!$user) return $query;

        $employee = $user->employee;
        if (!$employee) return $query;

        return $query->where('employee_id', $employee->id);
    }

    /**
     * ✅ Scope: Chỉ lấy dữ liệu của bác sĩ cụ thể
     * Sử dụng: ShiftAssignment::forDoctor($employeeId)->get()
     */
    public function scopeForDoctor($query, $employeeId)
    {
        $user = Auth::user();
        $currentEmployee = $user?->employee;

        // ✅ Admin có thể xem tất cả, Doctor chỉ xem của mình
        if ($currentEmployee && $currentEmployee->id !== $employeeId && $user->role !== 'admin') {
            return $query->whereNull('id'); // Trả về rỗng
        }

        return $query->where('employee_id', $employeeId);
    }

    // ===== FILTER SCOPES =====
    
    /**
     * ✅ Scope: Lấy ca trực được giao
     */
    public function scopeDuties($query)
    {
        return $query->where('assignment_type', 'duty');
    }

    /**
     * ✅ Scope: Lấy lịch làm việc thường xuyên
     */
    public function scopeWork($query)
    {
        return $query->where('assignment_type', 'work');
    }

    /**
     * ✅ Scope: Lọc theo ngày cụ thể
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('work_date', $date);
    }

    /**
     * ✅ Scope: Lọc theo trạng thái (approved, pending, rejected)
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * ✅ Scope: Lọc theo chuyên khoa
     */
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->whereHas('employee', function($q) use ($specialty) {
            $q->where('specialization', $specialty);
        });
    }

    /**
     * ✅ Scope: Lọc theo chuyên khoa + khoảng ngày
     */
    public function scopeBySpecialtyInDateRange($query, $specialty, $startDate, $endDate)
    {
        return $query
            ->whereHas('employee', function($q) use ($specialty) {
                $q->where('specialization', $specialty);
            })
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->orderBy('employee_id');
    }

    /**
     * ✅ Scope: Lấy ca trong khoảng ngày
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    /**
     * ✅ Scope: Lấy ca từ ngày này trở đi (tương lai)
     */
    public function scopeFuture($query)
    {
        return $query->whereDate('work_date', '>=', now()->toDateString());
    }

    /**
     * ✅ Scope: Lấy ca đã qua (quá khứ)
     */
    public function scopePast($query)
    {
        return $query->whereDate('work_date', '<', now()->toDateString());
    }

    /**
     * ✅ Scope: Lấy ca hôm nay
     */
    public function scopeToday($query)
    {
        return $query->whereDate('work_date', now()->toDateString());
    }

    /**
     * ✅ Scope: Lấy ca của bác sĩ hiện tại - Chỉ ca trực
     */
    public function scopeCurrentDoctorDuties($query)
    {
        return $query->forCurrentDoctor()->duties();
    }

    /**
     * ✅ Scope: Lấy ca của bác sĩ hiện tại - Chỉ lịch làm
     */
    public function scopeCurrentDoctorWork($query)
    {
        return $query->forCurrentDoctor()->work();
    }

    // ===== ACCESSORS =====
    
    public function getTimeRangeAttribute()
    {
        $start = str_pad($this->start_hour ?? 0, 2, '0', STR_PAD_LEFT) . ':' . 
                 str_pad($this->start_minute ?? 0, 2, '0', STR_PAD_LEFT);
        $end = str_pad($this->end_hour ?? 0, 2, '0', STR_PAD_LEFT) . ':' . 
               str_pad($this->end_minute ?? 0, 2, '0', STR_PAD_LEFT);
        return "{$start} - {$end}";
    }

    public function getFormattedDateAttribute()
    {
        return $this->work_date->format('d/m/Y');
    }

    public function getShiftNameAttribute()
    {
        return $this->shift?->name ?? 'Ca tùy chỉnh';
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'approved' => 'Đã duyệt',
            'pending' => 'Chờ duyệt',
            'rejected' => 'Bị từ chối',
            default => $this->status,
        };
    }

    // ===== METHODS =====

    /**
     * ✅ Kiểm tra bác sĩ hiện tại có quyền xem record này không
     */
    public function canViewByCurrentDoctor(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $employee = $user->employee;
        if (!$employee) return false;

        // Admin hoặc chính bác sĩ đó
        return $user->role === 'admin' || $employee->id === $this->employee_id;
    }

    /**
     * ✅ Kiểm tra bác sĩ hiện tại có quyền sửa record này không
     */
    public function canEditByCurrentDoctor(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Chỉ admin mới có quyền sửa
        return $user->role === 'admin';
    }

    /**
     * ✅ Kiểm tra bác sĩ hiện tại có quyền xóa record này không
     */
    public function canDeleteByCurrentDoctor(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Chỉ admin mới có quyền xóa
        return $user->role === 'admin';
    }
}