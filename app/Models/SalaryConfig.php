<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryConfig extends Model
{
    use HasFactory;

    protected $table = 'salary_configs';

    protected $fillable = [
        'name',
        'base_hourly_rate',

        'degree_bachelor_coefficient',
        'degree_master_coefficient',
        'degree_doctor_coefficient',
        'degree_associate_professor_coefficient',
        'degree_professor_coefficient',
        'degree_default_coefficient',

        'weekday_office_coefficient',
        'weekday_overtime_coefficient',
        'weekend_coefficient',

        'morning_shift_coefficient',
        'evening_shift_coefficient',

        'office_start_time',
        'office_end_time',

        'morning_shift_start',
        'morning_shift_end',
        'evening_shift_start',
        'evening_shift_end',

        'exclude_lunch_break',
        'lunch_start_time',
        'lunch_end_time',

        'exclude_evening_break',
        'evening_break_start_time',
        'evening_break_end_time',

        'is_active',
        'notes',
    ];

    protected $casts = [
        'base_hourly_rate' => 'decimal:2',

        'degree_bachelor_coefficient' => 'decimal:2',
        'degree_master_coefficient' => 'decimal:2',
        'degree_doctor_coefficient' => 'decimal:2',
        'degree_associate_professor_coefficient' => 'decimal:2',
        'degree_professor_coefficient' => 'decimal:2',
        'degree_default_coefficient' => 'decimal:2',

        'weekday_office_coefficient' => 'decimal:2',
        'weekday_overtime_coefficient' => 'decimal:2',
        'weekend_coefficient' => 'decimal:2',

        'morning_shift_coefficient' => 'decimal:2',
        'evening_shift_coefficient' => 'decimal:2',

        'exclude_lunch_break' => 'boolean',
        'exclude_evening_break' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function payrolls()
    {
        return $this->hasMany(DoctorPayroll::class, 'salary_config_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function current()
    {
        return static::active()->latest('id')->first();
    }

    public function getDoctorCoefficientByDegree(?string $degree): float
    {
        $normalizedDegree = mb_strtolower(trim((string) $degree));

        if ($normalizedDegree === '') {
            return (float) ($this->degree_default_coefficient ?? 1.30);
        }

        if (
            str_contains($normalizedDegree, 'giáo sư') &&
            !str_contains($normalizedDegree, 'phó')
        ) {
            return (float) ($this->degree_professor_coefficient ?? 2.50);
        }

        if (
            str_contains($normalizedDegree, 'phó giáo sư') ||
            str_contains($normalizedDegree, 'pgs')
        ) {
            return (float) ($this->degree_associate_professor_coefficient ?? 2.00);
        }

        if (
            str_contains($normalizedDegree, 'tiến sĩ') ||
            str_contains($normalizedDegree, 'tien si')
        ) {
            return (float) ($this->degree_doctor_coefficient ?? 1.70);
        }

        if (
            str_contains($normalizedDegree, 'thạc sĩ') ||
            str_contains($normalizedDegree, 'thac si')
        ) {
            return (float) ($this->degree_master_coefficient ?? 1.50);
        }

        if (
            str_contains($normalizedDegree, 'đại học') ||
            str_contains($normalizedDegree, 'dai hoc') ||
            str_contains($normalizedDegree, 'bác sĩ') ||
            str_contains($normalizedDegree, 'bac si')
        ) {
            return (float) ($this->degree_bachelor_coefficient ?? 1.30);
        }

        return (float) ($this->degree_default_coefficient ?? 1.30);
    }

    public function getFormattedBaseHourlyRateAttribute(): string
    {
        return number_format((float) ($this->base_hourly_rate ?? 0), 0, ',', '.') . ' đ';
    }

    public function getMorningShiftLabelAttribute(): string
    {
        return $this->formatTimeRange(
            $this->morning_shift_start ?: '08:00:00',
            $this->morning_shift_end ?: '17:00:00'
        );
    }

    public function getEveningShiftLabelAttribute(): string
    {
        return $this->formatTimeRange(
            $this->evening_shift_start ?: '14:00:00',
            $this->evening_shift_end ?: '22:00:00'
        );
    }

    public function getLunchBreakLabelAttribute(): string
    {
        return $this->formatTimeRange(
            $this->lunch_start_time ?: '11:30:00',
            $this->lunch_end_time ?: '12:30:00'
        );
    }

    public function getEveningBreakLabelAttribute(): string
    {
        return $this->formatTimeRange(
            $this->evening_break_start_time ?: '17:30:00',
            $this->evening_break_end_time ?: '18:00:00'
        );
    }

    private function formatTimeRange($start, $end): string
    {
        return substr((string) $start, 0, 5) . ' - ' . substr((string) $end, 0, 5);
    }
}