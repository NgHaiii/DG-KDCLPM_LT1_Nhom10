<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'service_id',
        'appointment_date',
        'slots_used',
        'duration_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'slots_used' => 'integer',
        'duration_minutes' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================
    
    /**
     * Quan hệ: Bệnh nhân (users)
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Quan hệ: Bác sĩ (employees)
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    /**
     * Quan hệ: Dịch vụ
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Lấy lịch hẹn của bệnh nhân hiện tại
     */
    public function scopeForCurrentPatient($query)
    {
        return $query->where('patient_id', auth()->id());
    }

    /**
     * Scope: Lấy lịch hẹn đã xác nhận
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: Lấy lịch hẹn chưa xác nhận
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Lấy lịch hẹn đã hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Lấy lịch hẹn đã hủy
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: Lấy lịch hẹn sắp tới
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now())
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->orderBy('appointment_date', 'asc');
    }

    /**
     * Scope: Lấy lịch hẹn quá khứ
     */
    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now())
                    ->orderBy('appointment_date', 'desc');
    }

    /**
     * Scope: Lấy lịch hẹn trong khoảng thời gian
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Tìm lịch hẹn theo bác sĩ
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope: Tìm lịch hẹn theo dịch vụ
     */
    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    // ==================== HELPERS ====================

    /**
     * Kiểm tra xem lịch hẹn đã qua hay chưa
     */
    public function isPast()
    {
        return $this->appointment_date < now();
    }

    /**
     * Kiểm tra xem lịch hẹn còn hiệu lực không
     */
    public function isUpcoming()
    {
        return $this->appointment_date >= now() && $this->status !== 'cancelled';
    }

    /**
     * Kiểm tra xem lịch hẹn đã được xác nhận không
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Kiểm tra xem lịch hẹn đã bị hủy không
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Kiểm tra xem lịch hẹn đã hoàn thành không
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Lấy tên trạng thái hiển thị
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => '⏳ Chờ xác nhận',
            'confirmed' => '✅ Đã xác nhận',
            'completed' => '✔️ Đã hoàn thành',
            'cancelled' => '❌ Đã hủy',
        ];

        return $statuses[$this->status] ?? 'N/A';
    }
}