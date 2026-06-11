<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalChart extends Model
{
    use HasFactory;

    protected $table = 'dental_charts';

    protected $fillable = [
        'patient_profile_id',
        'appointment_id',
        'doctor_id',
        'tooth_number',
        'status',
        'note',
    ];

    protected $casts = [
        'patient_profile_id' => 'integer',
        'appointment_id' => 'integer',
        'doctor_id' => 'integer',
        'tooth_number' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function histories()
    {
        return $this->hasMany(DentalChartHistory::class, 'dental_chart_id');
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'healthy' => 'Khỏe mạnh',
            'caries' => 'Sâu răng',
            'filled' => 'Đã trám',
            'crown' => 'Bọc sứ',
            'root_canal' => 'Điều trị tủy',
            'missing' => 'Đã mất',
            default => 'Không rõ',
        };
    }
}