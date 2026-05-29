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

    protected $casts = [
        'is_active' => 'boolean',
    ];

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