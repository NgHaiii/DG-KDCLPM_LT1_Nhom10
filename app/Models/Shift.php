<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quan hệ với ScheduleRequest
    public function scheduleRequests()
    {
        return $this->hasMany(ScheduleRequest::class);
    }

    // Quan hệ với ShiftAssignment
    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    // Kiểm tra xem có phải ca tối không
    public function isEvening()
    {
        return $this->name === 'Tối';
    }

    // Lấy ca theo tên
    public static function getByName($name)
    {
        return self::where('name', $name)->first();
    }
}