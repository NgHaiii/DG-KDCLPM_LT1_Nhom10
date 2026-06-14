@extends('layouts.admin-layout')

@section('title', 'Chi tiết hóa đơn')
@section('page-title', 'Chi tiết hóa đơn')
@section('page-subtitle', 'Xem thông tin hóa đơn trong báo cáo doanh thu')

@section('styles')
<style>
    .invoice-detail-page {
        display: grid;
        gap: 20px;
    }

    .detail-header,
    .detail-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .detail-header {
        padding: 22px;
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
    }

    .invoice-code {
        color: #0369a1;
        font-size: 30px;
        font-weight: 950;
        margin-bottom: 8px;
    }

    .invoice-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .action-btn {
        height: 42px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
        transition: all .18s ease;
    }

    .action-btn.primary {
        background: #0ea5e9;
        border-color: #0ea5e9;
        color: #fff;
        box-shadow: 0 10px 22px rgba(14, 165, 233, .22);
    }

    .action-btn:hover {
        background: #f0f9ff;
        border-color: #7dd3fc;
        color: #0369a1;
    }

    .action-btn.primary:hover {
        background: #0284c7;
        color: #fff;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-badge.paid {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.unpaid {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.payment_pending {
        background: #e0f2fe;
        color: #075985;
    }

    .status-badge.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(360px, .85fr);
        gap: 20px;
    }

    .detail-card-head {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .detail-card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
    }

    .detail-card-title i {
        color: #0ea5e9;
    }

    .detail-card-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .info-box {
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        min-width: 0;
    }

    .info-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .info-value {
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.45;
        word-break: break-word;
    }

    .info-value.muted {
        color: #64748b;
        font-weight: 750;
    }

    .source-badge,
    .method-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    .source-badge.online {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .source-badge.offline {
        background: #dcfce7;
        color: #166534;
    }

    .method-badge {
        background: #f1f5f9;
        color: #334155;
    }

    .amount-list {
        display: grid;
        gap: 10px;
    }

    .amount-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #dbe3ef;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
    }

    .amount-row:last-child {
        border-bottom: 0;
    }

    .amount-row.total {
        margin-top: 6px;
        padding: 16px;
        border: 0;
        border-radius: 16px;
        background: linear-gradient(135deg, #e0f2fe, #f8fafc);
        color: #0369a1;
        font-size: 18px;
        font-weight: 950;
    }

    .amount-row.paid {
        color: #15803d;
    }

    .amount-row.remaining {
        color: #b45309;
    }

    .item-table-wrap {
        overflow-x: auto;
    }

    .item-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
    }

    .item-table th {
        text-align: left;
        padding: 11px 10px;
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }

    .item-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #edf2f7;
        color: #0f172a;
        font-size: 14px;
        vertical-align: top;
    }

    .item-table .money {
        font-weight: 950;
        white-space: nowrap;
        text-align: right;
    }

    .empty-state {
        padding: 28px 16px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
    }

    .proof-box {
        border: 1px solid #bae6fd;
        border-radius: 16px;
        background: #f0f9ff;
        padding: 14px;
        color: #075985;
        font-weight: 800;
        line-height: 1.55;
    }

    .proof-image {
        margin-top: 12px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        padding: 10px;
    }

    .proof-image img {
        width: 100%;
        max-height: 360px;
        object-fit: contain;
        display: block;
        border-radius: 10px;
        background: #fff;
    }

    @media (max-width: 1100px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-header {
            flex-direction: column;
        }

        .header-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 720px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .invoice-code {
            font-size: 24px;
        }

        .action-btn {
            width: 100%;
        }

        .header-actions {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@php
    $invoice->loadMissing([
        'appointment.service',
        'appointment.doctor',
        'appointment.room',
        'appointment.medicalRecord',
        'patient',
        'patientProfile',
        'doctor',
        'service',
    ]);

    $paymentLabels = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'card' => 'Thẻ',
        'momo' => 'MoMo',
        'other' => 'Khác',
        'unknown' => 'Không rõ',
    ];

    $statusLabels = [
        'unpaid' => 'Chờ thanh toán',
        'payment_pending' => 'Chờ xác nhận chuyển khoản',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ];

    $sourceLabels = [
        'online' => 'Đặt online',
        'offline' => 'Khám trực tiếp',
    ];

    $status = $invoice->status ?: 'unknown';
    $source = $invoice->appointment?->source ?? $invoice->patientProfile?->source ?? 'online';
    $method = $invoice->payment_method ?: 'unknown';

    $patientName = $invoice->display_patient_name
        ?? $invoice->patient_name
        ?? $invoice->patientProfile?->full_name
        ?? $invoice->patient?->name
        ?? 'Chưa có tên';

    $patientPhone = $invoice->display_patient_phone
        ?? $invoice->patient_phone
        ?? $invoice->patientProfile?->phone
        ?? $invoice->patient?->phone
        ?? 'Chưa có SĐT';

    $serviceName = $invoice->display_service_name
        ?? $invoice->service_name
        ?? $invoice->service?->name
        ?? $invoice->appointment?->service?->name
        ?? 'Chưa có dịch vụ';

    $doctorName = $invoice->display_doctor_name
        ?? $invoice->doctor_name
        ?? $invoice->doctor?->name
        ?? $invoice->appointment?->doctor?->name
        ?? 'Chưa có bác sĩ';

    $cashierName = $invoice->cashier?->name
        ?? $invoice->cashier?->full_name
        ?? 'Chưa ghi nhận';

    $appointmentDate = $invoice->appointment_date ?? $invoice->appointment?->appointment_date;
    $roomName = $invoice->appointment?->room?->name ?? 'Chưa cập nhật';

    $medicineItems = $invoice->medicine_items;
    if (is_string($medicineItems)) {
        $medicineItems = json_decode($medicineItems, true) ?: [];
    }
    $medicineItems = collect(is_array($medicineItems) ? $medicineItems : []);

    $extraItems = $invoice->extra_items;
    if (is_string($extraItems)) {
        $extraItems = json_decode($extraItems, true) ?: [];
    }
    $extraItems = collect(is_array($extraItems) ? $extraItems : []);

    $serviceAmount = (float) ($invoice->service_price ?? $invoice->service_amount ?? 0);
    $medicineTotal = (float) ($invoice->medicine_total ?? 0);
    $extraTotal = (float) ($invoice->extra_total ?? $invoice->extra_amount ?? 0);
    $discountAmount = (float) ($invoice->discount_amount ?? 0);
    $totalAmount = (float) ($invoice->total_amount ?? 0);
    $paidAmount = (float) ($invoice->paid_amount ?? 0);
    $remainingAmount = max((float) ($invoice->remaining_amount ?? ($totalAmount - $paidAmount)), 0);

    $paidAt = $invoice->paid_at ?? $invoice->updated_at ?? $invoice->created_at;
    $issuedAt = $invoice->issued_at ?? $invoice->created_at;

    $proofUrl = null;
    if (!empty($invoice->patient_payment_proof_url)) {
        $proofUrl = $invoice->patient_payment_proof_url;
    } elseif (!empty($invoice->patient_payment_proof)) {
        $path = ltrim(str_replace('\\', '/', $invoice->patient_payment_proof), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $proofUrl = $path;
        } else {
            if (str_starts_with($path, 'public/')) {
                $path = substr($path, strlen('public/'));
            }

            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, strlen('storage/'));
            }

            $proofUrl = asset('storage/' . $path);
        }
    }
@endphp

<div class="invoice-detail-page">
    <div class="detail-header">
        <div>
            <div class="invoice-code">{{ $invoice->invoice_code ?? 'HD-' . $invoice->id }}</div>

            <div class="invoice-meta">
                <span><i class="ri-calendar-line"></i> Lập: {{ optional($issuedAt)->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}</span>
                <span><i class="ri-check-double-line"></i> Thanh toán: {{ optional($paidAt)->format('d/m/Y H:i') ?? 'Chưa thanh toán' }}</span>
                <span class="status-badge {{ $status }}">
                    <i class="ri-checkbox-circle-line"></i>
                    {{ $statusLabels[$status] ?? $status }}
                </span>
            </div>
        </div>

        <div class="header-actions">
            <a href="{{ route('admin.revenue.index') }}" class="action-btn">
                <i class="ri-arrow-left-line"></i>
                Quay lại thống kê
            </a>

            @if(\Illuminate\Support\Facades\Route::has('employees.invoices.print'))
                <a href="{{ route('employees.invoices.print', $invoice) }}" class="action-btn primary" target="_blank">
                    <i class="ri-printer-line"></i>
                    In hóa đơn
                </a>
            @endif
        </div>
    </div>

    <div class="detail-grid">
        <div class="detail-card">
            <div class="detail-card-head">
                <div class="detail-card-title">
                    <i class="ri-user-heart-line"></i>
                    Thông tin ca khám
                </div>
            </div>

            <div class="detail-card-body">
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">Bệnh nhân</div>
                        <div class="info-value">{{ $patientName }}</div>
                        <div class="info-value muted">{{ $patientPhone }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Nguồn khách</div>
                        <div class="info-value">
                            <span class="source-badge {{ $source }}">
                                <i class="{{ $source === 'offline' ? 'ri-user-received-line' : 'ri-global-line' }}"></i>
                                {{ $sourceLabels[$source] ?? $source }}
                            </span>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Dịch vụ</div>
                        <div class="info-value">{{ $serviceName }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Bác sĩ phụ trách</div>
                        <div class="info-value">{{ $doctorName }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Ngày khám</div>
                        <div class="info-value">
                            {{ optional($appointmentDate)->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Phòng khám</div>
                        <div class="info-value">{{ $roomName }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Thu ngân</div>
                        <div class="info-value">{{ $cashierName }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Phương thức thanh toán</div>
                        <div class="info-value">
                            <span class="method-badge">
                                <i class="ri-bank-card-line"></i>
                                {{ $paymentLabels[$method] ?? $method }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-card-head">
                <div class="detail-card-title">
                    <i class="ri-money-dollar-circle-line"></i>
                    Tổng hợp thanh toán
                </div>
            </div>

            <div class="detail-card-body">
                <div class="amount-list">
                    <div class="amount-row">
                        <span>Tiền dịch vụ</span>
                        <strong>{{ number_format($serviceAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="amount-row">
                        <span>Tiền thuốc</span>
                        <strong>{{ number_format($medicineTotal, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="amount-row">
                        <span>Phụ phí</span>
                        <strong>{{ number_format($extraTotal, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="amount-row">
                        <span>Giảm giá</span>
                        <strong>-{{ number_format($discountAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="amount-row total">
                        <span>Tổng tiền</span>
                        <strong>{{ number_format($totalAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="amount-row paid">
                        <span>Đã thanh toán</span>
                        <strong>{{ number_format($paidAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="amount-row remaining">
                        <span>Còn lại</span>
                        <strong>{{ number_format($remainingAmount, 0, ',', '.') }}đ</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-head">
            <div class="detail-card-title">
                <i class="ri-service-line"></i>
                Chi tiết dịch vụ
            </div>
        </div>

        <div class="detail-card-body">
            <div class="item-table-wrap">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th>Nội dung</th>
                            <th>Ghi chú</th>
                            <th class="money">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ $serviceName }}</strong>
                                <div class="muted">Dịch vụ khám/điều trị chính</div>
                            </td>
                            <td>{{ $invoice->notes ?: 'Không có ghi chú.' }}</td>
                            <td class="money">{{ number_format($serviceAmount, 0, ',', '.') }}đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-head">
            <div class="detail-card-title">
                <i class="ri-capsule-line"></i>
                Thuốc trong hóa đơn
            </div>
        </div>

        <div class="detail-card-body">
            @if($medicineItems->count())
                <div class="item-table-wrap">
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th class="money">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicineItems as $item)
                                @php
                                    $name = $item['name'] ?? $item['medicine_name'] ?? 'Thuốc';
                                    $quantity = (float) ($item['quantity'] ?? 1);
                                    $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
                                    $lineTotal = (float) ($item['total'] ?? $item['line_total'] ?? ($quantity * $unitPrice));
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ $name }}</strong>
                                        @if(!empty($item['dosage']) || !empty($item['note']))
                                            <div class="muted">{{ $item['dosage'] ?? $item['note'] }}</div>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    <td>{{ number_format($unitPrice, 0, ',', '.') }}đ</td>
                                    <td class="money">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">Hóa đơn này không có thuốc tính tiền.</div>
            @endif
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-head">
            <div class="detail-card-title">
                <i class="ri-add-circle-line"></i>
                Phụ phí
            </div>
        </div>

        <div class="detail-card-body">
            @if($extraItems->count())
                <div class="item-table-wrap">
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th>Nội dung</th>
                                <th>Ghi chú</th>
                                <th class="money">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($extraItems as $item)
                                @php
                                    $name = $item['name'] ?? $item['label'] ?? 'Phụ phí';
                                    $note = $item['note'] ?? $item['description'] ?? null;
                                    $amount = (float) ($item['amount'] ?? $item['total'] ?? 0);
                                @endphp

                                <tr>
                                    <td><strong>{{ $name }}</strong></td>
                                    <td>{{ $note ?: 'Không có ghi chú.' }}</td>
                                    <td class="money">{{ number_format($amount, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">Hóa đơn này không có phụ phí.</div>
            @endif
        </div>
    </div>

    @if($invoice->patient_payment_proof || $proofUrl)
        <div class="detail-card">
            <div class="detail-card-head">
                <div class="detail-card-title">
                    <i class="ri-image-line"></i>
                    Ảnh xác nhận thanh toán
                </div>
            </div>

            <div class="detail-card-body">
                <div class="proof-box">
                    Bệnh nhân đã gửi ảnh xác nhận thanh toán
                    @if($invoice->patient_paid_submitted_at)
                        lúc {{ optional($invoice->patient_paid_submitted_at)->format('d/m/Y H:i') }}.
                    @endif
                </div>

                @if($proofUrl)
                    <div class="proof-image">
                        <img src="{{ $proofUrl }}" alt="Ảnh xác nhận thanh toán">
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection