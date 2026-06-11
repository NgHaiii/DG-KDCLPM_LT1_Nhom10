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
        'address',
        'identity_number',
        'emergency_contact_name',
        'emergency_contact_phone',
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

    /**
     * Tài khoản người dùng nếu bệnh nhân có tài khoản đăng nhập.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tất cả lịch/lượt khám của bệnh nhân.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_profile_id');
    }

    /**
     * Lượt khám gần nhất.
     */
    public function latestAppointment()
    {
        return $this->hasOne(Appointment::class, 'patient_profile_id')
            ->latestOfMany('appointment_date');
    }

    /**
     * Hồ sơ bệnh án thông qua các lượt khám.
     */
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

    // ==================== SCOPES ====================

    /**
     * Tìm kiếm hồ sơ theo tên, SĐT, email, CCCD.
     */
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
                ->orWhere('identity_number', 'like', "%{$keyword}%");
        });
    }

    /**
     * Hồ sơ online.
     */
    public function scopeOnline($query)
    {
        return $query->where('source', 'online');
    }

    /**
     * Hồ sơ offline.
     */
    public function scopeOffline($query)
    {
        return $query->where('source', 'offline');
    }

    /**
     * Hồ sơ tạm.
     */
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

    public function getAgeAttribute()
    {
        if (!$this->dob) {
            return null;
        }

        return $this->dob->age;
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

    // ==================== HELPERS ====================

    /**
     * Tạo snapshot thông tin bệnh nhân để lưu vào appointments.patient_snapshot.
     */
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
            'address' => $this->address,
            'identity_number' => $this->identity_number,
            'source' => $this->source,
            'is_temporary' => $this->is_temporary,
            'snapshot_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Cập nhật lần khám gần nhất.
     */
    public function markVisited($visitedAt = null)
    {
        $this->update([
            'last_visit_at' => $visitedAt ?: now(),
        ]);
    }

    /**
     * Kiểm tra hồ sơ đã đủ thông tin cơ bản chưa.
     */
    public function isComplete()
    {
        return filled($this->full_name)
            && filled($this->phone)
            && filled($this->dob)
            && filled($this->gender)
            && filled($this->address);
    }
}