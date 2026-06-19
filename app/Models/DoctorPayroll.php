<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorPayroll extends Model
{
    use HasFactory;

    protected $table = 'doctor_payrolls';

    protected $fillable = [
        'payroll_code',
        'doctor_id',
        'salary_config_id',
        'created_by',
        'approved_by',
        'salary_month',
        'salary_year',
        'base_hourly_rate',
        'doctor_coefficient',
        'total_work_hours',
        'total_patient_complexity_coefficient',
        'total_converted_hours',
        'gross_amount',
        'bonus_amount',
        'deduction_amount',
        'net_amount',
        'items',
        'status',
        'generated_at',
        'approved_at',
        'paid_at',
        'doctor_confirmed_at',
        'notes',
    ];

    protected $casts = [
        'doctor_id' => 'integer',
        'salary_config_id' => 'integer',
        'created_by' => 'integer',
        'approved_by' => 'integer',
        'salary_month' => 'integer',
        'salary_year' => 'integer',
        'base_hourly_rate' => 'decimal:2',
        'doctor_coefficient' => 'decimal:2',
        'total_work_hours' => 'decimal:2',
        'total_patient_complexity_coefficient' => 'decimal:2',
        'total_converted_hours' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'items' => 'array',
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'doctor_confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function salaryConfig()
    {
        return $this->belongsTo(SalaryConfig::class, 'salary_config_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('salary_month', $month)
            ->where('salary_year', $year);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Nháp',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
            default => 'Không rõ',
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getFormattedGrossAmountAttribute(): string
    {
        return number_format((float) $this->gross_amount, 0, ',', '.') . ' đ';
    }

    public function getFormattedNetAmountAttribute(): string
    {
        return number_format((float) $this->net_amount, 0, ',', '.') . ' đ';
    }

    public static function generateCode(int $doctorId, int $month, int $year): string
    {
        return 'LG' . $year . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . 'BS' . str_pad((string) $doctorId, 4, '0', STR_PAD_LEFT);
    }

    public function isConfirmedByDoctor(): bool
    {
        return !is_null($this->doctor_confirmed_at);
    }
}