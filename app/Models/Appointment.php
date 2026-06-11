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
        'patient_profile_id',
        'doctor_id',
        'service_id',
        'room_id',
        'source',
        'patient_snapshot',
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
        'patient_id' => 'integer',
        'patient_profile_id' => 'integer',
        'doctor_id' => 'integer',
        'service_id' => 'integer',
        'room_id' => 'integer',
        'slots_used' => 'integer',
        'duration_minutes' => 'integer',
        'actual_used_minutes' => 'integer',
        'queue_number' => 'integer',

        'patient_snapshot' => 'array',

        'appointment_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'estimated_end_at' => 'datetime',
        'completed_at' => 'datetime',
        'delay_notified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Tài khoản bệnh nhân nếu lịch online có user đăng nhập.
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Hồ sơ bệnh nhân dùng chung cho online/offline.
     */
    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    /**
     * Bác sĩ phụ trách.
     */
    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    /**
     * Dịch vụ khám.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Phòng khám.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Hồ sơ bệnh án của lượt khám này.
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id');
    }

    // ==================== SCOPES ====================

    public function scopeForCurrentPatient($query)
    {
        return $query->where('patient_id', auth()->id());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('status', 'checked_in');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
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

    public function scopeReceptionFlow($query)
    {
        return $query->whereIn('status', [
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

    public function scopeByPatientProfile($query, $patientProfileId)
    {
        return $query->where('patient_profile_id', $patientProfileId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    // ==================== HELPERS ====================

    public function isPast()
    {
        return $this->appointment_date && $this->appointment_date->lt(now());
    }

    public function isUpcoming()
    {
        return $this->appointment_date
            && $this->appointment_date->gte(now())
            && !in_array($this->status, ['cancelled', 'missed'], true);
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

    public function isMissed()
    {
        return $this->status === 'missed';
    }

    public function isOnline()
    {
        return ($this->source ?? 'online') === 'online';
    }

    public function isOffline()
    {
        return ($this->source ?? 'online') === 'offline';
    }

    public function canBeCheckedIn()
    {
        return $this->status === 'confirmed'
            && $this->appointment_date
            && $this->appointment_date->isSameDay(now());
    }

    public function canStartExamination()
    {
        return in_array($this->status, ['checked_in', 'waiting'], true)
            && $this->checked_in_at !== null;
    }

    public function canCompleteExamination()
    {
        return $this->status === 'in_progress'
            && $this->started_at !== null;
    }

    public function getExpectedEndTimeAttribute()
    {
        if (!$this->appointment_date) {
            return null;
        }

        return $this->appointment_date->copy()->addMinutes((int) ($this->duration_minutes ?? 30));
    }

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

    public function getOvertimeMinutesAttribute()
    {
        if (!$this->isOvertime()) {
            return 0;
        }

        $estimatedEnd = $this->estimated_end_at ?? $this->expected_end_time;

        return max(0, $estimatedEnd->diffInMinutes(now()));
    }

    /**
     * Tên bệnh nhân ưu tiên lấy từ:
     * 1. patient_profiles
     * 2. patient_snapshot
     * 3. users
     * 4. fallback theo patient_id
     */
    public function getPatientDisplayNameAttribute()
    {
        return $this->patientProfile?->full_name
            ?? data_get($this->patient_snapshot, 'full_name')
            ?? $this->patient?->name
            ?? 'Bệnh nhân #' . ($this->patient_id ?? $this->id);
    }

    /**
     * SĐT bệnh nhân ưu tiên lấy từ:
     * 1. patient_profiles
     * 2. patient_snapshot
     * 3. users nếu có cột phone/phone_number/tel
     * 4. notes theo dạng "SĐT: ..."
     */
    public function getPatientDisplayPhoneAttribute()
    {
        $phone = $this->patientProfile?->phone
            ?? data_get($this->patient_snapshot, 'phone')
            ?? $this->patient?->phone
            ?? $this->patient?->phone_number
            ?? $this->patient?->tel
            ?? null;

        if (!$phone && $this->notes) {
            preg_match('/SĐT:\s*([0-9+\-\s]+)/u', $this->notes, $matches);
            $phone = isset($matches[1]) ? trim($matches[1]) : null;
        }

        return $phone ?: 'Chưa có SĐT';
    }

    public function getPatientDisplayEmailAttribute()
    {
        return $this->patientProfile?->email
            ?? data_get($this->patient_snapshot, 'email')
            ?? $this->patient?->email
            ?? null;
    }

    public function getPatientDisplayAddressAttribute()
    {
        return $this->patientProfile?->address
            ?? data_get($this->patient_snapshot, 'address')
            ?? 'Chưa cập nhật';
    }

    public function getPatientDisplayDobAttribute()
    {
        $dob = $this->patientProfile?->dob
            ?? data_get($this->patient_snapshot, 'dob');

        if (!$dob) {
            return null;
        }

        return is_string($dob) ? $dob : $dob->format('Y-m-d');
    }

    public function getPatientDisplayGenderAttribute()
    {
        return $this->patientProfile?->gender_label
            ?? data_get($this->patient_snapshot, 'gender_label')
            ?? data_get($this->patient_snapshot, 'gender')
            ?? 'Chưa cập nhật';
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã tiếp nhận',
            'waiting' => 'Đang chờ khám',
            'in_progress' => 'Đang khám',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            'missed' => 'Bỏ lỡ lịch khám',
        ];

        return $statuses[$this->status] ?? 'Không xác định';
    }

    public function getSourceLabelAttribute()
    {
        $sources = [
            'online' => 'Đặt online',
            'offline' => 'Tiếp nhận tại quầy',
        ];

        return $sources[$this->source ?? 'online'] ?? 'Không xác định';
    }

    public function getDisplayQueueNumberAttribute()
    {
        return $this->queue_number
            ? str_pad((string) $this->queue_number, 3, '0', STR_PAD_LEFT)
            : '-';
    }

    /**
     * Tạo snapshot từ hồ sơ bệnh nhân hiện tại.
     */
    public function syncPatientSnapshotFromProfile()
    {
        if (!$this->patientProfile) {
            return false;
        }

        $this->patient_snapshot = $this->patientProfile->toAppointmentSnapshot();

        return $this->save();
    }
}