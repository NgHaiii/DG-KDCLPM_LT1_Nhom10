<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomShift extends Model
{
    protected $fillable = [
        'name',
        'start_hour',
        'start_minute',
        'end_hour',
        'end_minute',
        'description',
        'is_for_doctor',
        'is_for_employee',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_for_doctor' => 'boolean',
        'is_for_employee' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Lấy giờ dạng chuỗi: "8:00 - 17:00"
    public function getTimeRangeAttribute()
    {
        $start = str_pad($this->start_hour, 2, '0', STR_PAD_LEFT) . ':' . 
                 str_pad($this->start_minute, 2, '0', STR_PAD_LEFT);
        $end = str_pad($this->end_hour, 2, '0', STR_PAD_LEFT) . ':' . 
               str_pad($this->end_minute, 2, '0', STR_PAD_LEFT);
        return "{$start} - {$end}";
    }
}