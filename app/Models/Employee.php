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

    // ✅ Thêm relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}