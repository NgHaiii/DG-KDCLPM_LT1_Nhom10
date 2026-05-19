<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * ✅ Relationship: User → Employee (1-to-1)
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    /**
     * ✅ Kiểm tra user có phải admin không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * ✅ Kiểm tra user có phải doctor không
     */
    public function isDoctor(): bool
    {
        return $this->employee && $this->employee->is_doctor;
    }

    /**
     * ✅ Kiểm tra user có phải employee/nhân viên không
     */
    public function isEmployee(): bool
    {
        return $this->employee && !$this->employee->is_doctor;
    }

    /**
     * ✅ Lấy tên role dễ hiểu
     */
    public function getRoleLabel(): string
    {
        return match($this->role) {
            'admin' => 'Quản trị viên',
            'doctor' => 'Bác sĩ',
            'employee' => 'Nhân viên',
            'patient' => 'Bệnh nhân',
            default => 'Người dùng',
        };
    }
}