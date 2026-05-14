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
     * Get current price (helper method)
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
}