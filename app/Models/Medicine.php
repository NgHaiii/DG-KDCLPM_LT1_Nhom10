<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicines';

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'strength',
        'unit',
        'sale_price',
        'stock_quantity',
        'min_stock_quantity',
        'category',
        'is_active',
        'description',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeSearch($query, $keyword)
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('code', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('generic_name', 'like', "%{$keyword}%")
                ->orWhere('strength', 'like', "%{$keyword}%")
                ->orWhere('category', 'like', "%{$keyword}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_quantity');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function hasEnoughStock(int $quantity): bool
    {
        return (int) $this->stock_quantity >= $quantity;
    }

    public function increaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return $this->increment('stock_quantity', $quantity);
    }

    public function decreaseStock(int $quantity): bool
    {
        if ($quantity <= 0 || !$this->hasEnoughStock($quantity)) {
            return false;
        }

        return $this->decrement('stock_quantity', $quantity);
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([
            $this->name,
            $this->strength,
        ]);

        return implode(' - ', $parts);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->sale_price, 0, ',', '.') . ' đ';
    }

    public function getStockStatusAttribute(): string
    {
        if ((int) $this->stock_quantity <= 0) {
            return 'out';
        }

        if ((int) $this->stock_quantity <= (int) $this->min_stock_quantity) {
            return 'low';
        }

        return 'ok';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out' => 'Hết hàng',
            'low' => 'Sắp hết',
            default => 'Đủ hàng',
        };
    }

    public function getActiveLabelAttribute(): string
    {
        return $this->is_active ? 'Đang sử dụng' : 'Ngừng sử dụng';
    }
}