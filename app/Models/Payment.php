<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'payment_code',
        'invoice_id',
        'appointment_id',
        'patient_id',
        'patient_profile_id',
        'cashier_id',
        'amount',
        'payment_method',
        'status',
        'payer_name',
        'payer_phone',
        'transaction_reference',
        'note',
        'paid_at',
    ];

    protected $casts = [
        'invoice_id' => 'integer',
        'appointment_id' => 'integer',
        'patient_id' => 'integer',
        'patient_profile_id' => 'integer',
        'cashier_id' => 'integer',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeBetweenPaidDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        if (!$method) {
            return $query;
        }

        return $query->where('payment_method', $method);
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match ($this->payment_method) {
            'cash' => 'Tiền mặt',
            'bank_transfer' => 'Chuyển khoản',
            'card' => 'Thẻ',
            'momo' => 'MoMo',
            'e_wallet' => 'Ví điện tử',
            'other' => 'Khác',
            default => 'Không xác định',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'cancelled' => 'Đã hủy',
            default => 'Thành công',
        };
    }

    public function getFormattedAmountAttribute()
    {
        return number_format((float) $this->amount, 0, ',', '.') . ' đ';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public static function generateCode(): string
    {
        $prefix = 'PAY' . now()->format('Ymd');

        $lastPayment = self::where('payment_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastPayment && preg_match('/(\d{4})$/', $lastPayment->payment_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}