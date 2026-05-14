<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'price',
        'applied_date',
    ];

    protected $casts = [
        'applied_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}