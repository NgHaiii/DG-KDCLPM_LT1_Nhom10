<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',        // ✅ Thêm dòng này
        'code',
        'name',
        'dob',
        'gender',
        'phone',
        'email',
        'address',
        'workplace',
        'degree',
        'specialization',
        'position',
        'is_doctor',
        'status',
    ];

    // ✅ Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Quan hệ với ScheduleRequest (Đơn đăng ký lịch làm)
    public function scheduleRequests()
    {
        return $this->hasMany(ScheduleRequest::class, 'employee_id');
    }
}