<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'position',
        'is_doctor',
        'specialization',
        'code', // Thêm dòng này để cho phép lưu mã nhân viên/bác sĩ
    ];
}