<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    use HasFactory;

    protected $table = 'patient_profiles';

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'dob',
        'gender',
        'blood_type',
        'address',
        'occupation',
        'identity_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'medical_history',
        'current_medications',
        'dental_history',
        'source',
        'is_temporary',
        'last_visit_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'dob' => 'date',
        'is_temporary' => 'boolean',
        'last_visit_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_profile_id');
    }

    public function latestAppointment()
    {
        return $this->hasOne(Appointment::class, 'patient_profile_id')
            ->latestOfMany('appointment_date');
    }

    public function medicalRecords()
    {
        return $this->hasManyThrough(
            MedicalRecord::class,
            Appointment::class,
            'patient_profile_id',
            'appointment_id',
            'id',
            'id'
        );
    }

    public function clinicalImages()
    {
        return $this->hasMany(ClinicalImage::class, 'patient_profile_id')
            ->latest('taken_date')
            ->latest('created_at');
    }

    public function latestClinicalImage()
    {
        return $this->hasOne(ClinicalImage::class, 'patient_profile_id')
            ->latestOfMany('taken_date');
    }

    // ==================== SCOPES ====================

    public function scopeSearch($query, $keyword)
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('full_name', 'like', "%{$keyword}%")
                ->orWhere('phone', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhere('identity_number', 'like', "%{$keyword}%")
                ->orWhere('blood_type', 'like', "%{$keyword}%")
                ->orWhere('occupation', 'like', "%{$keyword}%");
        });
    }

    public function scopeOnline($query)
    {
        return $query->where('source', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('source', 'offline');
    }

    public function scopeTemporary($query)
    {
        return $query->where('is_temporary', true);
    }

    // ==================== ACCESSORS ====================

    public function getDisplayNameAttribute()
    {
        return $this->full_name ?: 'Bệnh nhân #' . $this->id;
    }

    public function getDisplayPhoneAttribute()
    {
        return $this->phone ?: 'Chưa có SĐT';
    }

    public function getGenderLabelAttribute()
    {
        return match ($this->gender) {
            'male', 'nam', 'Nam' => 'Nam',
            'female', 'nu', 'nữ', 'Nữ' => 'Nữ',
            'other', 'khac', 'khác', 'Khác' => 'Khác',
            default => 'Chưa cập nhật',
        };
    }

    public function getSourceLabelAttribute()
    {
        return match ($this->source) {
            'online' => 'Online',
            'offline' => 'Trực tiếp',
            'imported' => 'Nhập dữ liệu',
            default => 'Không rõ',
        };
    }

    public function getProfileTypeLabelAttribute()
    {
        return $this->is_temporary ? 'Hồ sơ tạm' : 'Hồ sơ chính thức';
    }

    public function getDisplayBloodTypeAttribute()
    {
        return $this->blood_type ?: 'Chưa cập nhật';
    }

    public function getDisplayOccupationAttribute()
    {
        return $this->occupation ?: 'Chưa cập nhật';
    }

    public function getDisplayAllergiesAttribute()
    {
        return $this->allergies ?: 'Chưa ghi nhận dị ứng.';
    }

    public function getDisplayMedicalHistoryAttribute()
    {
        return $this->medical_history ?: 'Chưa cập nhật.';
    }

    public function getDisplayCurrentMedicationsAttribute()
    {
        return $this->current_medications ?: 'Chưa cập nhật.';
    }

    public function getDisplayDentalHistoryAttribute()
    {
        return $this->dental_history ?: 'Chưa cập nhật.';
    }

    public function getAgeAttribute()
    {
        return $this->dob ? $this->dob->age : null;
    }

    public function getDisplayAgeAttribute()
    {
        return $this->age ? $this->age . ' tuổi' : 'Chưa cập nhật';
    }

    public function getShortAddressAttribute()
    {
        if (!$this->address) {
            return 'Chưa cập nhật';
        }

        return mb_strlen($this->address) > 80
            ? mb_substr($this->address, 0, 80) . '...'
            : $this->address;
    }

    public function getEmergencyContactLabelAttribute()
    {
        if (!$this->emergency_contact_name && !$this->emergency_contact_phone) {
            return 'Chưa cập nhật';
        }

        return trim(($this->emergency_contact_name ?: '') . ' - ' . ($this->emergency_contact_phone ?: ''), ' -');
    }

    public function getMedicalWarningLabelAttribute()
    {
        $warnings = [];

        if (filled($this->allergies)) {
            $warnings[] = 'Dị ứng';
        }

        if (filled($this->medical_history)) {
            $warnings[] = 'Tiền sử bệnh lý';
        }

        if (filled($this->current_medications)) {
            $warnings[] = 'Đang dùng thuốc';
        }

        return count($warnings) ? implode(', ', $warnings) : 'Không có cảnh báo';
    }

    // ==================== HELPERS ====================

    public function toAppointmentSnapshot()
    {
        return [
            'patient_profile_id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'dob' => $this->dob ? $this->dob->format('Y-m-d') : null,
            'gender' => $this->gender,
            'gender_label' => $this->gender_label,
            'blood_type' => $this->blood_type,
            'address' => $this->address,
            'occupation' => $this->occupation,
            'identity_number' => $this->identity_number,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'allergies' => $this->allergies,
            'medical_history' => $this->medical_history,
            'current_medications' => $this->current_medications,
            'dental_history' => $this->dental_history,
            'source' => $this->source,
            'is_temporary' => $this->is_temporary,
            'snapshot_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    public function markVisited($visitedAt = null)
    {
        $this->update([
            'last_visit_at' => $visitedAt ?: now(),
        ]);
    }

    public function isComplete()
    {
        return filled($this->full_name)
            && filled($this->phone)
            && filled($this->dob)
            && filled($this->gender)
            && filled($this->address);
    }

    public function hasMedicalSafetyInfo()
    {
        return filled($this->allergies)
            || filled($this->medical_history)
            || filled($this->current_medications)
            || filled($this->dental_history);
    }
}