<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',           // Mã bác sĩ/nhân viên
        'name',
        'dob',            // Ngày sinh
        'gender',         // Giới tính
        'phone',
        'email',
        'address',
        'workplace',      // Nơi công tác chính thức
        'degree',         // Bằng cấp
        'specialization', // Chuyên môn
        'position',
        'is_doctor',
        'status',         // Trạng thái (Hoạt động/Tạm nghỉ)
        'linkedUser',     // Tài khoản liên kết
    ];
}