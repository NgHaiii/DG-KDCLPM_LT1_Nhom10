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
        'required_specialization',  // ✅ THÊM CỘT NÀY
        'is_active',
        'slots_required',
        'duration_minutes',
        'actual_duration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'slots_required' => 'integer',
        'duration_minutes' => 'integer',
        'actual_duration' => 'integer',
    ];

    /**
     * Mutator để đảm bảo actual_duration được lưu đúng
     */
    protected function setActualDurationAttribute($value)
    {
        if ($value === null || $value === '') {
            // Nếu null, tính lại từ slots_required * duration_minutes
            $this->attributes['actual_duration'] = ($this->slots_required ?? 1) * ($this->duration_minutes ?? 30);
        } else {
            // Nếu có giá trị, lưu trực tiếp
            $this->attributes['actual_duration'] = (int) $value;
        }
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithPrice($query)
    {
        return $query->has('prices');
    }
}