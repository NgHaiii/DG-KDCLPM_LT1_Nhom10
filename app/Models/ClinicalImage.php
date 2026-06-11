<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalImage extends Model
{
    use HasFactory;

    protected $table = 'clinical_images';

    protected $fillable = [
        'appointment_id',
        'patient_profile_id',
        'doctor_id',
        'image_type',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'taken_date',
        'notes',
    ];

    protected $casts = [
        'appointment_id' => 'integer',
        'patient_profile_id' => 'integer',
        'doctor_id' => 'integer',
        'file_size' => 'integer',
        'taken_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function getImageTypeLabelAttribute()
    {
        return match ($this->image_type) {
            'xray' => 'X-quang',
            'panorama' => 'Panorama',
            'intraoral' => 'Ảnh trong miệng',
            'clinical' => 'Ảnh lâm sàng',
            default => 'Khác',
        };
    }
}