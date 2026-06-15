<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorCaseComplexity extends Model
{
    use HasFactory;

    protected $table = 'doctor_case_complexities';

    protected $fillable = [
        'doctor_id',
        'appointment_id',
        'patient_profile_id',
        'created_by',
        'salary_month',
        'salary_year',
        'case_date',
        'complexity_coefficient',
        'case_title',
        'reason',
        'notes',
    ];

    protected $casts = [
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'patient_profile_id' => 'integer',
        'created_by' => 'integer',
        'salary_month' => 'integer',
        'salary_year' => 'integer',
        'case_date' => 'date',
        'complexity_coefficient' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function getCoefficientLabelAttribute(): string
    {
        return '+' . number_format((float) $this->complexity_coefficient, 2);
    }

    public function getCaseDisplayNameAttribute(): string
    {
        if ($this->case_title) {
            return $this->case_title;
        }

        if ($this->appointment?->service?->name) {
            return $this->appointment->service->name;
        }

        return 'Ca bệnh phức tạp #' . $this->id;
    }
}