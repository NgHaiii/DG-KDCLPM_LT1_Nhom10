<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'floor',
        'location',
        'capacity',
        'base_status',
        'description',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'Khám',
        'Điều trị',
        'Thẩm mỹ',
        'Phẫu thuật',
    ];

    public const BASE_STATUSES = [
        'available',
        'maintenance',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getTypeLabelAttribute()
    {
        return $this->type ?: 'Chưa phân loại';
    }

    public function getBaseStatusLabelAttribute()
    {
        return match ($this->base_status) {
            'available' => 'Hoạt động',
            'maintenance' => 'Bảo trì',
            default => 'Không xác định',
        };
    }

    public function getActiveStatusLabelAttribute()
    {
        return $this->is_active ? 'Đang sử dụng' : 'Ngừng sử dụng';
    }

    public function getDisplayStatusAttribute()
    {
        if (!$this->is_active) {
            return 'Ngừng hoạt động';
        }

        if ($this->base_status === 'maintenance') {
            return 'Bảo trì';
        }

        return 'Hoạt động';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query
            ->where('is_active', true)
            ->where('base_status', 'available');
    }

    public function scopeByType($query, $type)
    {
        if (!$type) {
            return $query;
        }

        return $query->where('type', $type);
    }
}