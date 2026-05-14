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
        'is_active',
    ];

    /**
     * Relationship: Service có nhiều Price
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Relationship: Lấy giá hiện tại (mới nhất)
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

    /**
     * Helper method: Lấy giá hiện tại (sử dụng khi cần gọi hàm)
     */
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

    /**
     * Accessor: Format price (nếu cần)
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->currentPrice) {
            return number_format($this->currentPrice->price, 0, ',', '.');
        }
        return 'Chưa có giá';
    }

    /**
     * Scope: Chỉ lấy dịch vụ active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Lấy dịch vụ có giá
     */
    public function scopeWithPrice($query)
    {
        return $query->has('prices');
    }
}