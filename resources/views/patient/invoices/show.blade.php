@extends('layouts.patient-layout')

@section('title', 'Chi tiết hóa đơn')

@section('styles')
<style>
    .page-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
    }

    .back-btn {
        height: 42px;
        padding: 0 15px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 850;
    }

    .back-btn:hover {
        border-color: #0ea5e9;
        color: #0284c7;
        background: #f0f9ff;
    }

    .invoice-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) 390px;
        gap: 22px;
        align-items: start;
    }

    .panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .panel-head {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .panel-title {
        color: #0f172a;
        font-size: 21px;
        font-weight: 950;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-title i {
        color: #0ea5e9;
    }

    .panel-body {
        padding: 22px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .badge.unpaid { background: #fef3c7; color: #92400e; }
    .badge.payment_pending { background: #e0f2fe; color: #075985; }
    .badge.paid { background: #dcfce7; color: #166534; }
    .badge.cancelled { background: #fee2e2; color: #991b1b; }
    .badge.overdue { background: #fee2e2; color: #991b1b; }

    .invoice-code {
        color: #0369a1;
        font-size: 20px;
        font-weight: 950;
        margin-bottom: 6px;
        word-break: break-word;
    }

    .muted {
        color: #64748b;
        font-size: 13px;
        line-height: 1.45;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
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
        font-weight: 850;
        line-height: 1.4;
        word-break: break-word;
    }

    .bill-table-wrap {
        overflow-x: auto;
    }

    .bill-table {
        width: 100%;
        min-width: 680px;
        border-collapse: collapse;
        overflow: hidden;
        border-radius: 14px;
    }

    .bill-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        text-align: left;
        padding: 12px;
        border: 1px solid #e2e8f0;
    }

    .bill-table td {
        padding: 12px;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .text-right {
        text-align: right;
    }

    .money {
        color: #0f172a;
        font-weight: 950;
        white-space: nowrap;
    }

    .summary {
        display: grid;
        gap: 10px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        color: #334155;
        font-size: 15px;
    }

    .summary-row.total {
        padding-top: 12px;
        border-top: 2px solid #0f172a;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
    }

    .notice {
        padding: 14px;
        border-radius: 14px;
        line-height: 1.5;
        font-size: 14px;
        font-weight: 750;
    }

    .notice.paid {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .notice.unpaid {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .notice.pending {
        background: #f0f9ff;
        color: #075985;
        border: 1px solid #bae6fd;
    }

    .notice.cancelled,
    .notice.overdue {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .prescription-box,
    .note-box {
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        color: #0f172a;
        line-height: 1.65;
        white-space: pre-line;
    }

    .qr-card {
        padding: 15px;
        border: 1px solid #bae6fd;
        border-radius: 16px;
        background: #f0f9ff;
    }

    .qr-wrap {
        width: 100%;
        display: flex;
        justify-content: center;
        padding: 12px;
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        margin-bottom: 12px;
        cursor: zoom-in;
    }

    .qr-wrap img {
        width: 240px;
        max-width: 100%;
        height: auto;
        display: block;
    }

    .transfer-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 8px 0;
        border-bottom: 1px dashed #bae6fd;
        color: #334155;
        font-size: 13px;
    }

    .transfer-row:last-child {
        border-bottom: 0;
    }

    .transfer-row strong {
        color: #0f172a;
        text-align: right;
        word-break: break-word;
    }

    .upload-form {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed #bae6fd;
    }

    .form-group {
        margin-bottom: 13px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 850;
        color: #334155;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        min-height: 43px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 0 12px;
        outline: none;
        color: #0f172a;
        background: #fff;
    }

    textarea.form-control {
        min-height: 84px;
        padding: 11px 12px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .btn-pay {
        width: 100%;
        min-height: 44px;
        border: 0;
        border-radius: 13px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(14, 165, 233, .22);
    }

    .btn-pay:hover {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
    }

    .proof-image {
    width: 100%;
    border: 1px solid #dbe3ef;
    border-radius: 14px;
    overflow: hidden;
    background: #f8fafc;
    margin-top: 12px;
    padding: 12px;
}

.proof-image img {
    width: 100%;
    display: block;
    max-height: 420px;
    object-fit: contain;
    background: #fff;
    border-radius: 10px;
}

.proof-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.proof-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.proof-action-btn {
    height: 32px;
    padding: 0 10px;
    border-radius: 10px;
    border: 1px solid #bae6fd;
    background: #f0f9ff;
    color: #0369a1;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 850;
}

.proof-action-btn:hover {
    background: #e0f2fe;
    color: #075985;
}

.proof-preview {
    display: none;
    margin-top: 10px;
    border: 1px solid #dbe3ef;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
}

.proof-preview img {
    width: 100%;
    display: block;
    max-height: 320px;
    object-fit: contain;
}
    .qr-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 22px;
    }

    .qr-modal.show {
        display: flex;
    }

    .qr-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(3px);
    }

    .qr-modal-card {
        position: relative;
        width: min(460px, 100%);
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .35);
        padding: 22px;
    }

    .qr-modal-close {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 12px;
        background: #f1f5f9;
        color: #0f172a;
        cursor: pointer;
        font-size: 20px;
    }

    .qr-modal-title {
        padding-right: 42px;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
        margin-bottom: 6px;
    }

    .qr-modal-subtitle {
        color: #64748b;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .qr-modal-image {
        display: flex;
        justify-content: center;
        padding: 16px;
        border: 1px solid #dbeafe;
        border-radius: 18px;
        background: #f8fafc;
    }

    .qr-modal-image img {
        width: 320px;
        max-width: 100%;
        height: auto;
        display: block;
    }

    .error-box {
        margin-bottom: 16px;
        padding: 13px 14px;
        border-radius: 14px;
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-weight: 750;
    }

    @media (max-width: 1100px) {
        .invoice-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .page-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $invoice->loadMissing([
        'appointment.room',
        'appointment.medicalRecord',
        'doctor',
        'service',
        'payments',
        'cashier',
        'verifier',
    ]);

    $medicalRecord = $invoice->appointment?->medicalRecord;
    $prescription = trim((string) ($medicalRecord?->prescription ?? ''));

    $medicineItems = collect($invoice->medicine_items ?: []);
    $extraItems = collect($invoice->extra_items ?: []);

    $servicePrice = (float) ($invoice->service_price ?? $invoice->service_amount ?? 0);
    $medicineTotal = (float) ($invoice->medicine_total ?? $medicineItems->sum(fn ($item) => (float) ($item['total'] ?? 0)));
    $extraTotal = (float) ($invoice->extra_total ?? $invoice->extra_amount ?? $extraItems->sum(fn ($item) => (float) ($item['total'] ?? 0)));
    $discountAmount = (float) ($invoice->discount_amount ?? 0);
    $totalAmount = (float) ($invoice->total_amount ?? max($servicePrice + $medicineTotal + $extraTotal - $discountAmount, 0));
    $paidAmount = (float) ($invoice->paid_amount ?? 0);
    $remainingAmount = (float) ($invoice->remaining_amount ?? max($totalAmount - $paidAmount, 0));

    $statusLabel = $invoice->status_label ?? match ($invoice->status) {
        'paid' => 'Đã thanh toán',
        'payment_pending' => 'Chờ xác nhận chuyển khoản',
        'cancelled' => 'Đã hủy',
        default => 'Chờ thanh toán',
    };

    $isPaid = $invoice->status === 'paid';
    $isCancelled = $invoice->status === 'cancelled';
    $isPaymentPending = $invoice->status === 'payment_pending';
    $isUnpaid = $invoice->status === 'unpaid';

    $isOverdue = method_exists($invoice, 'isPaymentOverdue')
        ? $invoice->isPaymentOverdue()
        : ($isUnpaid && $invoice->payment_due_at && now()->greaterThan($invoice->payment_due_at));

    $displayStatusLabel = $isOverdue ? 'Quá hạn thanh toán' : $statusLabel;
    $statusClass = $isOverdue ? 'overdue' : $invoice->status;

    $canPatientSubmitPayment = method_exists($invoice, 'canPatientSubmitPayment')
        ? $invoice->canPatientSubmitPayment()
        : ($isUnpaid && !$isOverdue && !empty($invoice->sent_to_patient_at));

    $bankId = config('services.bank.bank_id', env('BANK_ID', ''));
    $bankAccountNo = config('services.bank.account_no', env('BANK_ACCOUNT_NO', ''));
    $bankAccountName = config('services.bank.account_name', env('BANK_ACCOUNT_NAME', 'DENTALCARE'));

    $transferAmount = (int) round((float) ($remainingAmount > 0 ? $remainingAmount : $totalAmount));
    $transferContent = preg_replace('/[^A-Za-z0-9]/', '', $invoice->invoice_code);

    $vietQrUrl = ($bankId && $bankAccountNo)
        ? 'https://img.vietqr.io/image/' . $bankId . '-' . $bankAccountNo . '-compact2.png?amount=' . $transferAmount . '&addInfo=' . urlencode($transferContent) . '&accountName=' . urlencode($bankAccountName)
        : null;

    $proofUrl = $invoice->patient_payment_proof_url ?? null;
@endphp

<div class="page-actions">
    <a href="{{ route('patient.invoices.index') }}" class="back-btn">
        <i class="ri-arrow-left-line"></i>
        Quay lại lịch sử hóa đơn
    </a>
</div>

@if($errors->any())
    <div class="error-box">
        <strong>Lỗi:</strong>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

<div class="invoice-layout">
    <div>
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ri-receipt-line"></i>
                    Chi tiết hóa đơn
                </div>

                <span class="badge {{ $statusClass }}">
                    @if($isPaid)
                        <i class="ri-checkbox-circle-line"></i>
                    @elseif($isCancelled)
                        <i class="ri-close-circle-line"></i>
                    @elseif($isPaymentPending)
                        <i class="ri-bank-card-line"></i>
                    @elseif($isOverdue)
                        <i class="ri-alarm-warning-line"></i>
                    @else
                        <i class="ri-time-line"></i>
                    @endif
                    {{ $displayStatusLabel }}
                </span>
            </div>

            <div class="panel-body">
                <div class="invoice-code">{{ $invoice->invoice_code }}</div>

                <div class="muted">
                    Ngày lập: {{ optional($invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">Bệnh nhân</div>
                        <div class="info-value">{{ $invoice->display_patient_name ?? $invoice->patient_name ?? 'Chưa có tên' }}</div>
                        <div class="muted">SĐT: {{ $invoice->display_patient_phone ?? $invoice->patient_phone ?? 'Chưa có SĐT' }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Bác sĩ</div>
                        <div class="info-value">{{ $invoice->display_doctor_name ?? $invoice->doctor_name ?? 'Chưa có bác sĩ' }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Dịch vụ</div>
                        <div class="info-value">{{ $invoice->display_service_name ?? $invoice->service_name ?? 'Chưa có dịch vụ' }}</div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Ngày khám</div>
                        <div class="info-value">
                            {{ optional($invoice->appointment_date)->format('d/m/Y H:i') ?: 'Chưa có' }}
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Phòng khám</div>
                        <div class="info-value">
                            {{ $invoice->appointment?->room?->name ?? 'Chưa có phòng' }}
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-label">Phương thức thanh toán</div>
                        <div class="info-value">{{ $invoice->payment_method_label ?? 'Chưa thanh toán' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($prescription !== '')
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-capsule-line"></i>
                        Đơn thuốc / chỉ định bác sĩ
                    </div>
                </div>

                <div class="panel-body">
                    <div class="prescription-box">{{ $prescription }}</div>
                </div>
            </div>
        @endif

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ri-file-list-3-line"></i>
                    Chi tiết chi phí
                </div>
            </div>

            <div class="panel-body">
                <div class="bill-table-wrap">
                    <table class="bill-table">
                        <thead>
                            <tr>
                                <th>Nội dung</th>
                                <th class="text-right">SL</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-right">Thành tiền</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <strong>{{ $invoice->display_service_name ?? $invoice->service_name ?? 'Dịch vụ khám' }}</strong>
                                    <div class="muted">Chi phí dịch vụ khám/điều trị</div>
                                </td>
                                <td class="text-right">1</td>
                                <td class="text-right">{{ number_format($servicePrice, 0, ',', '.') }}đ</td>
                                <td class="text-right money">{{ number_format($servicePrice, 0, ',', '.') }}đ</td>
                            </tr>

                            @foreach($medicineItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['name'] ?? 'Thuốc' }}</strong>
                                        <div class="muted">
                                            {{ $item['code'] ?? '-' }} · {{ $item['unit'] ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-right">{{ $item['quantity'] ?? 0 }}</td>
                                    <td class="text-right">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }}đ</td>
                                    <td class="text-right money">{{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach

                            @foreach($extraItems as $item)
                                @if(!empty($item['name']))
                                    <tr>
                                        <td><strong>{{ $item['name'] }}</strong></td>
                                        <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="text-right">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }}đ</td>
                                        <td class="text-right money">
                                            {{ number_format((float) ($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0))), 0, ',', '.') }}đ
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($invoice->notes)
                    <div style="margin-top:16px;">
                        <div class="info-label">Ghi chú</div>
                        <div class="note-box">{{ $invoice->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <aside>
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ri-money-dollar-circle-line"></i>
                    Tổng thanh toán
                </div>
            </div>

            <div class="panel-body">
                <div class="summary">
                    <div class="summary-row">
                        <span>Tiền dịch vụ</span>
                        <strong>{{ number_format($servicePrice, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="summary-row">
                        <span>Tiền thuốc</span>
                        <strong>{{ number_format($medicineTotal, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="summary-row">
                        <span>Phụ phí</span>
                        <strong>{{ number_format($extraTotal, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="summary-row">
                        <span>Giảm giá</span>
                        <strong>-{{ number_format($discountAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="summary-row total">
                        <span>Tổng tiền</span>
                        <strong>{{ number_format($totalAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="summary-row">
                        <span>Đã thanh toán</span>
                        <strong>{{ number_format($paidAmount, 0, ',', '.') }}đ</strong>
                    </div>

                    <div class="summary-row">
                        <span>Còn lại</span>
                        <strong>{{ number_format($remainingAmount, 0, ',', '.') }}đ</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                @if($isPaid)
                    <div class="notice paid">
                        <i class="ri-checkbox-circle-line"></i>
                        Hóa đơn đã được thanh toán.
                        @if($invoice->paid_at)
                            <br>Thời gian: {{ optional($invoice->paid_at)->format('d/m/Y H:i') }}
                        @endif
                        @if($invoice->cashier?->name || $invoice->verifier?->name)
                            <br>Người xác nhận: {{ $invoice->cashier?->name ?? $invoice->verifier?->name }}
                        @endif
                    </div>
                @elseif($isCancelled)
                    <div class="notice cancelled">
                        <i class="ri-close-circle-line"></i>
                        Hóa đơn này đã bị hủy.
                    </div>
                @elseif($isPaymentPending)
                    <div class="notice pending">
                        <i class="ri-bank-card-line"></i>
                        Bạn đã gửi ảnh xác nhận thanh toán. Phòng khám đang kiểm tra giao dịch.
                        @if($invoice->patient_paid_submitted_at)
                            <br>Gửi lúc: {{ optional($invoice->patient_paid_submitted_at)->format('d/m/Y H:i') }}
                        @endif
                    </div>

                    @if($invoice->patient_payment_note)
                        <div style="margin-top:12px;">
                            <div class="info-label">Ghi chú đã gửi</div>
                            <div class="note-box">{{ $invoice->patient_payment_note }}</div>
                        </div>
                    @endif

                    @if($proofUrl)
    <div class="proof-image">
        <div class="proof-head">
            <div class="info-label" style="margin-bottom:0;">Ảnh bill đã gửi</div>

            <div class="proof-actions">
                <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="proof-action-btn">
                    <i class="ri-external-link-line"></i>
                    Mở ảnh
                </a>

                <a href="{{ $proofUrl }}" download class="proof-action-btn">
                    <i class="ri-download-2-line"></i>
                    Tải ảnh
                </a>
            </div>
        </div>

        <img src="{{ $proofUrl }}" alt="Ảnh bill đã gửi">
    </div>
@elseif($invoice->patient_payment_proof)
    <div class="notice overdue" style="margin-top:12px;">
        Không tải được ảnh bill. Vui lòng kiểm tra liên kết storage.
        <br>
        File đang lưu: {{ $invoice->patient_payment_proof }}
    </div>
@endif
                @elseif($isOverdue)
                    <div class="notice overdue">
                        <i class="ri-alarm-warning-line"></i>
                        Hóa đơn đã quá hạn thanh toán online. Vui lòng liên hệ phòng khám hoặc thanh toán tại quầy.
                    </div>
                @elseif($isUnpaid)
                    <div class="notice unpaid">
                        <i class="ri-time-line"></i>
                        Hóa đơn đang chờ thanh toán.
                        @if($invoice->payment_due_at)
                            <br>Hạn thanh toán: {{ optional($invoice->payment_due_at)->format('d/m/Y H:i') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($canPatientSubmitPayment)
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-qr-code-line"></i>
                        Thanh toán chuyển khoản
                    </div>
                </div>

                <div class="panel-body">
                    <div class="qr-card">
                        @if($vietQrUrl)
                            <button type="button" class="qr-wrap js-open-qr-modal" aria-label="Phóng to mã QR chuyển khoản">
                                <img src="{{ $vietQrUrl }}" alt="QR chuyển khoản hóa đơn {{ $invoice->invoice_code }}">
                            </button>

                            <div class="transfer-row">
                                <span>Ngân hàng</span>
                                <strong>{{ $bankId }}</strong>
                            </div>

                            <div class="transfer-row">
                                <span>Số tài khoản</span>
                                <strong>{{ $bankAccountNo }}</strong>
                            </div>

                            <div class="transfer-row">
                                <span>Chủ tài khoản</span>
                                <strong>{{ $bankAccountName }}</strong>
                            </div>

                            <div class="transfer-row">
                                <span>Số tiền</span>
                                <strong>{{ number_format($transferAmount, 0, ',', '.') }}đ</strong>
                            </div>

                            <div class="transfer-row">
                                <span>Nội dung</span>
                                <strong>{{ $transferContent }}</strong>
                            </div>
                        @else
                            <div class="notice overdue">
                                Phòng khám chưa cấu hình QR chuyển khoản. Vui lòng thanh toán trực tiếp tại quầy.
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('patient.invoices.submit-payment-proof', $invoice) }}"
                            enctype="multipart/form-data"
                            class="upload-form"
                        >
                            @csrf

                            <div class="form-group">
                                <label>Ảnh bill chuyển khoản *</label>
                                <input
                                    type="file"
                                    name="payment_proof"
                                    id="paymentProofInput"
                                    class="form-control"
                                    accept="image/png,image/jpeg,image/jpg,image/webp"
                                    required
                                >
                                <div class="muted" style="margin-top:6px;">
                                    Chấp nhận PNG, JPG, JPEG, WEBP. Dung lượng tối đa 5MB.
                                </div>

                                <div class="proof-preview" id="proofPreview">
                                    <img src="" alt="Xem trước ảnh bill">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Ghi chú thanh toán</label>
                                <textarea
                                    name="patient_payment_note"
                                    class="form-control"
                                    placeholder="Ví dụ: Đã chuyển khoản lúc 09:30, người chuyển Nguyễn Văn A..."
                                >{{ old('patient_payment_note') }}</textarea>
                            </div>

                            <button type="submit" class="btn-pay" onclick="return confirm('Bạn chắc chắn đã chuyển khoản đúng số tiền và muốn gửi bill cho phòng khám kiểm tra?');">
                                <i class="ri-upload-cloud-2-line"></i>
                                Gửi bill xác nhận thanh toán
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </aside>
</div>

@if($vietQrUrl)
    <div class="qr-modal" id="qrModal" aria-hidden="true">
        <div class="qr-modal-backdrop js-close-qr-modal"></div>

        <div class="qr-modal-card" role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">
            <button type="button" class="qr-modal-close js-close-qr-modal" aria-label="Đóng mã QR">
                <i class="ri-close-line"></i>
            </button>

            <div class="qr-modal-title" id="qrModalTitle">
                QR chuyển khoản hóa đơn
            </div>

            <div class="qr-modal-subtitle">
                {{ $invoice->invoice_code }} · {{ number_format($transferAmount, 0, ',', '.') }}đ · Nội dung: {{ $transferContent }}
            </div>

            <div class="qr-modal-image">
                <img src="{{ $vietQrUrl }}" alt="QR chuyển khoản hóa đơn {{ $invoice->invoice_code }}">
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    const qrModal = document.getElementById('qrModal');
    const openQrButtons = document.querySelectorAll('.js-open-qr-modal');
    const closeQrButtons = document.querySelectorAll('.js-close-qr-modal');
    const proofInput = document.getElementById('paymentProofInput');
    const proofPreview = document.getElementById('proofPreview');

    function openQrModal() {
        if (!qrModal) {
            return;
        }

        qrModal.classList.add('show');
        qrModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeQrModal() {
        if (!qrModal) {
            return;
        }

        qrModal.classList.remove('show');
        qrModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    openQrButtons.forEach(button => {
        button.addEventListener('click', openQrModal);
    });

    closeQrButtons.forEach(button => {
        button.addEventListener('click', closeQrModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeQrModal();
        }
    });

    if (proofInput && proofPreview) {
        proofInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) {
                proofPreview.style.display = 'none';
                proofPreview.querySelector('img').src = '';
                return;
            }

            const url = URL.createObjectURL(file);
            proofPreview.querySelector('img').src = url;
            proofPreview.style.display = 'block';
        });
    }
</script>
@endsection