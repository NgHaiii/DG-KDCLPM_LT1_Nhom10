<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'invoice_code',
        'appointment_id',
        'patient_id',
        'patient_profile_id',
        'doctor_id',
        'service_id',
        'created_by',
        'cashier_id',

        'patient_name',
        'patient_phone',
        'doctor_name',
        'service_name',
        'appointment_date',

        'service_amount',
        'service_price',
        'medicine_items',
        'extra_items',
        'medicine_total',
        'extra_amount',
        'extra_total',
        'subtotal',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',

        'payment_method',
        'status',
        'issued_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'appointment_id' => 'integer',
        'patient_id' => 'integer',
        'patient_profile_id' => 'integer',
        'doctor_id' => 'integer',
        'service_id' => 'integer',
        'created_by' => 'integer',
        'cashier_id' => 'integer',

        'appointment_date' => 'datetime',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        'service_amount' => 'decimal:2',
        'service_price' => 'decimal:2',
        'medicine_items' => 'array',
        'extra_items' => 'array',
        'medicine_total' => 'decimal:2',
        'extra_amount' => 'decimal:2',
        'extra_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

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

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function successfulPayments()
    {
        return $this->hasMany(Payment::class, 'invoice_id')
            ->where('status', 'success');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForCurrentPatient($query)
    {
        return $query->where('patient_id', auth()->id());
    }

    public function scopeBetweenPaidDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy',
            default => 'Chờ thanh toán',
        };
    }

    public function getStatusClassAttribute()
    {
        return match ($this->status) {
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'warning',
        };
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
            default => 'Chưa thanh toán',
        };
    }

    public function getDisplayPatientNameAttribute()
    {
        return $this->patient_name
            ?: $this->patientProfile?->full_name
            ?: $this->patient?->name
            ?: 'Bệnh nhân #' . ($this->patient_id ?: $this->id);
    }

    public function getDisplayPatientPhoneAttribute()
    {
        return $this->patient_phone
            ?: $this->patientProfile?->phone
            ?: $this->patient?->phone
            ?: $this->patient?->phone_number
            ?: 'Chưa có SĐT';
    }

    public function getDisplayDoctorNameAttribute()
    {
        return $this->doctor_name
            ?: $this->doctor?->name
            ?: 'Chưa có bác sĩ';
    }

    public function getDisplayServiceNameAttribute()
    {
        return $this->service_name
            ?: $this->service?->name
            ?: 'Dịch vụ khám';
    }

    public function getFormattedTotalAttribute()
    {
        return number_format((float) $this->total_amount, 0, ',', '.') . ' đ';
    }

    public function getFormattedPaidAttribute()
    {
        return number_format((float) $this->paid_amount, 0, ',', '.') . ' đ';
    }

    public function getFormattedRemainingAttribute()
    {
        return number_format((float) $this->remaining_amount, 0, ',', '.') . ' đ';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function recalculateTotals(): void
    {
        $medicineTotal = collect($this->medicine_items ?: [])
            ->sum(fn ($item) => (float) ($item['total'] ?? 0));

        $extraTotal = collect($this->extra_items ?: [])
            ->sum(fn ($item) => (float) ($item['total'] ?? 0));

        $servicePrice = (float) ($this->service_price ?: $this->service_amount ?: 0);
        $discount = (float) ($this->discount_amount ?: 0);
        $paid = (float) ($this->paid_amount ?: 0);

        $subtotal = $servicePrice + $medicineTotal + $extraTotal;
        $total = max(0, $subtotal - $discount);
        $remaining = max(0, $total - $paid);

        $this->forceFill([
            'service_amount' => $servicePrice,
            'service_price' => $servicePrice,
            'medicine_total' => $medicineTotal,
            'extra_amount' => $extraTotal,
            'extra_total' => $extraTotal,
            'subtotal' => $subtotal,
            'total_amount' => $total,
            'remaining_amount' => $remaining,
        ]);
    }

    public function markAsPaid(?string $paymentMethod = null, $cashierId = null): void
    {
        $this->forceFill([
            'status' => 'paid',
            'payment_method' => $paymentMethod ?: $this->payment_method,
            'cashier_id' => $cashierId ?: $this->cashier_id,
            'paid_amount' => $this->total_amount,
            'remaining_amount' => 0,
            'paid_at' => now(),
        ])->save();
    }

    public static function generateCode(): string
    {
        $prefix = 'HD' . now()->format('Ymd');

        $lastInvoice = self::where('invoice_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastInvoice && preg_match('/(\d{4})$/', $lastInvoice->invoice_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}