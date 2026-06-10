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
        'room_id',
        'source',
        'appointment_date',
        'slots_used',
        'duration_minutes',
        'status',
        'confirmed_at',
        'checked_in_at',
        'started_at',
        'estimated_end_at',
        'completed_at',
        'actual_used_minutes',
        'queue_number',
        'delay_notified_at',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'estimated_end_at' => 'datetime',
        'completed_at' => 'datetime',
        'delay_notified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'service_id' => 'integer',
        'room_id' => 'integer',
        'slots_used' => 'integer',
        'duration_minutes' => 'integer',
        'actual_used_minutes' => 'integer',
        'queue_number' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Bệnh nhân đặt lịch.
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Bác sĩ phụ trách lịch hẹn.
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    /**
     * Dịch vụ khám/điều trị.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Phòng khám thực tế được xếp cho lịch hẹn.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // ==================== SCOPES ====================

    public function scopeForCurrentPatient($query)
    {
        return $query->where('patient_id', auth()->id());
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOnline($query)
    {
        return $query->where('source', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('source', 'offline');
    }

    public function scopeActiveFlow($query)
    {
        return $query->whereIn('status', [
            'pending',
            'confirmed',
            'checked_in',
            'waiting',
            'in_progress',
        ]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'waiting', 'in_progress'])
            ->orderBy('appointment_date', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now())
            ->orderBy('appointment_date', 'desc');
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    // ==================== HELPERS ====================

    public function isPast()
    {
        return $this->appointment_date < now();
    }

    public function isUpcoming()
    {
        return $this->appointment_date >= now() && $this->status !== 'cancelled';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isCheckedIn()
    {
        return $this->status === 'checked_in';
    }

    public function isWaiting()
    {
        return $this->status === 'waiting';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isOnline()
    {
        return $this->source === 'online';
    }

    public function isOffline()
    {
        return $this->source === 'offline';
    }

    /**
     * Thời điểm kết thúc dự kiến theo appointment_date + duration_minutes.
     */
    public function getExpectedEndTimeAttribute()
    {
        if (!$this->appointment_date) {
            return null;
        }

        return $this->appointment_date->copy()->addMinutes((int) ($this->duration_minutes ?? 30));
    }

    /**
     * Kiểm tra ca khám có đang quá thời lượng dự kiến không.
     */
    public function isOvertime()
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        $estimatedEnd = $this->estimated_end_at ?? $this->expected_end_time;

        if (!$estimatedEnd) {
            return false;
        }

        return now()->greaterThan($estimatedEnd);
    }

    /**
     * Số phút quá giờ, dùng cho cảnh báo lễ tân/bệnh nhân.
     */
    public function getOvertimeMinutesAttribute()
    {
        if (!$this->isOvertime()) {
            return 0;
        }

        $estimatedEnd = $this->estimated_end_at ?? $this->expected_end_time;

        return max(0, $estimatedEnd->diffInMinutes(now()));
    }

    /**
     * Lấy tên trạng thái hiển thị.
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã check-in',
            'waiting' => 'Đang chờ khám',
            'in_progress' => 'Đang khám',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return $statuses[$this->status] ?? 'Không xác định';
    }

    /**
     * Lấy tên nguồn lịch.
     */
    public function getSourceLabelAttribute()
    {
        $sources = [
            'online' => 'Đặt online',
            'offline' => 'Tiếp nhận tại quầy',
        ];

        return $sources[$this->source] ?? 'Không xác định';
    }
}