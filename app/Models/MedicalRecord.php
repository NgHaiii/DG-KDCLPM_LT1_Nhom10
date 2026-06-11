<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'patient_profile_id',
        'doctor_id',
        'service_id',
        'chief_complaint',
        'diagnosis',
        'treatment_plan',
        'prescription',
        'doctor_notes',
        'follow_up_date',
    ];

    protected $casts = [
        'appointment_id' => 'integer',
        'patient_id' => 'integer',
        'patient_profile_id' => 'integer',
        'doctor_id' => 'integer',
        'service_id' => 'integer',
        'follow_up_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function getPatientDisplayNameAttribute()
    {
        return $this->patientProfile?->full_name
            ?? $this->appointment?->patient_display_name
            ?? $this->patient?->name
            ?? 'Chưa có tên';
    }

    public function getPatientDisplayPhoneAttribute()
    {
        return $this->patientProfile?->phone
            ?? $this->appointment?->patient_display_phone
            ?? $this->patient?->phone
            ?? $this->patient?->phone_number
            ?? 'Chưa có SĐT';
    }
}