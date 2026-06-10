<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'required_specialization',
        'room_id',
        'is_active',
        'slots_required',
        'duration_minutes',
        'actual_duration',
    ];

    protected $casts = [
        'room_id' => 'integer',
        'is_active' => 'boolean',
        'slots_required' => 'integer',
        'duration_minutes' => 'integer',
        'actual_duration' => 'integer',
    ];

    /**
     * Tự tính thời lượng thực tế nếu không truyền actual_duration.
     * actual_duration = slots_required * duration_minutes
     */
    protected function setActualDurationAttribute($value)
    {
        if ($value !== null && $value !== '') {
            $this->attributes['actual_duration'] = (int) $value;
            return;
        }

        $slotsRequired = (int) ($this->attributes['slots_required'] ?? 1);
        $durationMinutes = (int) ($this->attributes['duration_minutes'] ?? 30);

        $this->attributes['actual_duration'] = $slotsRequired * $durationMinutes;
    }

    /**
     * Phòng khám được gán cho dịch vụ.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Bảng giá của dịch vụ.
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Giá hiện tại của dịch vụ.
     */
    public function currentPrice()
    {
        return $this->hasOne(Price::class)
            ->where(function ($query) {
                $query->whereDate('applied_date', '<=', now())
                    ->orWhereNull('applied_date');
            })
            ->latest('applied_date');
    }

    public function getCurrentPrice()
    {
        return $this->prices()
            ->where(function ($query) {
                $query->whereDate('applied_date', '<=', now())
                    ->orWhereNull('applied_date');
            })
            ->latest('applied_date')
            ->first();
    }

    public function getFormattedPriceAttribute()
    {
        $price = $this->currentPrice;

        if ($price) {
            return number_format($price->price, 0, ',', '.');
        }

        return 'Chưa có giá';
    }

    /**
     * Scope lấy dịch vụ đang hoạt động.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope lấy dịch vụ đã có giá.
     */
    public function scopeWithPrice($query)
    {
        return $query->has('prices');
    }

    /**
     * Scope lọc dịch vụ theo loại.
     */
    public function scopeByType($query, $type)
    {
        if (!$type) {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Scope lấy dịch vụ đã được gán phòng.
     */
    public function scopeWithRoom($query)
    {
        return $query->whereNotNull('room_id');
    }
}