<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalChartHistory extends Model
{
    use HasFactory;

    protected $table = 'dental_chart_histories';

    protected $fillable = [
        'patient_profile_id',
        'appointment_id',
        'doctor_id',
        'dental_chart_id',
        'action_type',
        'tooth_number',
        'old_status',
        'new_status',
        'old_note',
        'new_note',
    ];

    protected $casts = [
        'patient_profile_id' => 'integer',
        'appointment_id' => 'integer',
        'doctor_id' => 'integer',
        'dental_chart_id' => 'integer',
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

    public function dentalChart()
    {
        return $this->belongsTo(DentalChart::class, 'dental_chart_id');
    }

    public function getActionLabelAttribute()
    {
        return match ($this->action_type) {
            'quick_note' => 'Ghi chú nhanh',
            'update_tooth' => 'Cập nhật răng',
            default => 'Cập nhật',
        };
    }

    public function getNewStatusLabelAttribute()
    {
        return match ($this->new_status) {
            'healthy' => 'Khỏe mạnh',
            'caries' => 'Sâu răng',
            'filled' => 'Đã trám',
            'crown' => 'Bọc sứ',
            'root_canal' => 'Điều trị tủy',
            'missing' => 'Đã mất',
            default => $this->new_status ?: 'Ghi chú',
        };
    }
}