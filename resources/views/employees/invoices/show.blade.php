@extends('layouts.employee-layout')

@section('title', 'Chi tiết hóa đơn')
@section('page-title', 'Chi tiết hóa đơn')
@section('page-subtitle', 'Kiểm tra hóa đơn, gửi cho bệnh nhân, xác nhận thanh toán và in hóa đơn')

@section('header-actions')
    <a href="{{ route('employees.invoices.index') }}" class="btn btn-secondary no-print">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>

    <a href="{{ route('employees.invoices.print', $invoice) }}" class="btn btn-primary no-print" target="_blank" rel="noopener">
        <i class="ri-printer-line"></i>
        In hóa đơn
    </a>
@endsection

@section('styles')
<style>
    .invoice-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 390px;
        gap: 22px;
        align-items: start;
    }

    .panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
        overflow: hidden;
        margin-bottom: 18px;
    }

    .panel-head {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #0f172a;
        font-size: 19px;
        font-weight: 900;
    }

    .panel-title i {
        color: #0ea5e9;
    }

    .panel-body {
        padding: 20px;
    }

    .invoice-box {
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        padding: 24px;
        background: #fff;
    }

    .invoice-top {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 16px;
        margin-bottom: 18px;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .brand-logo {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 26px;
    }

    .brand-name {
        font-size: 24px;
        font-weight: 950;
        color: #0f172a;
        letter-spacing: .03em;
    }

    .brand-sub {
        color: #64748b;
        font-size: 13px;
        margin-top: 3px;
    }

    .invoice-meta {
        text-align: right;
    }

    .invoice-meta h2 {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 23px;
        font-weight: 950;
    }

    .invoice-code {
        color: #0284c7;
        font-weight: 950;
        font-size: 16px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .status-badge.unpaid {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.payment_pending {
        background: #e0f2fe;
        color: #075985;
    }

    .status-badge.paid {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 13px;
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
    }

    .bill-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        overflow: hidden;
        border-radius: 14px;
    }

    .bill-table th {
        background: #f1f5f9;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        padding: 11px;
        text-align: left;
        border: 1px solid #e2e8f0;
    }

    .bill-table td {
        padding: 11px;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        vertical-align: top;
    }

    .text-right {
        text-align: right;
    }

    .money {
        font-weight: 950;
        white-space: nowrap;
    }

    .summary {
        margin-left: auto;
        margin-top: 18px;
        width: min(420px, 100%);
        display: grid;
        gap: 10px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        color: #334155;
        font-size: 15px;
    }

    .summary-row.total {
        border-top: 2px solid #0f172a;
        padding-top: 12px;
        margin-top: 4px;
        font-size: 20px;
        font-weight: 950;
        color: #0f172a;
    }

    .note-box {
        padding: 13px;
        border-radius: 14px;
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        color: #0f172a;
        line-height: 1.55;
        white-space: pre-line;
    }

    .note-title {
        color: #0369a1;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .form-group {
        margin-bottom: 14px;
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
        min-height: 86px;
        padding: 11px 12px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .inline-grid {
        display: grid;
        grid-template-columns: 1fr 100px;
        gap: 10px;
    }

    .extra-row {
        display: grid;
        grid-template-columns: 1fr 76px 116px;
        gap: 8px;
        margin-bottom: 8px;
    }

    .btn-full {
        width: 100%;
    }

    .btn-danger-soft {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .btn-danger-soft:hover {
        background: #fecaca;
    }

    .medicine-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 11px;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        margin-bottom: 8px;
        background: #f8fafc;
    }

    .medicine-name {
        font-weight: 850;
        color: #0f172a;
    }

    .medicine-meta {
        color: #64748b;
        font-size: 13px;
        margin-top: 3px;
    }

    .danger-link {
        border: 0;
        background: #fee2e2;
        color: #991b1b;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 17px;
    }

    .locked-box,
    .paid-box,
    .cancelled-box,
    .pending-box {
        padding: 14px;
        border-radius: 14px;
        font-weight: 750;
        line-height: 1.5;
    }

    .locked-box {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .paid-box {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .pending-box {
        background: #f0f9ff;
        color: #075985;
        border: 1px solid #bae6fd;
    }

    .cancelled-box {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .bank-transfer-panel {
        display: none;
        margin: 4px 0 16px;
        padding: 15px;
        border: 1px solid #bae6fd;
        border-radius: 16px;
        background: #f0f9ff;
    }

    .bank-transfer-panel.show {
        display: block;
    }

    .bank-transfer-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0369a1;
        font-weight: 900;
        margin-bottom: 12px;
    }

    .qr-wrap {
        width: 100%;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #fff;
        padding: 12px;
        display: flex;
        justify-content: center;
        cursor: zoom-in;
    }

    .qr-wrap img {
        width: 230px;
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

    .transfer-row strong {
        color: #0f172a;
        text-align: right;
    }

    .transfer-note {
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        background: #ecfeff;
        color: #155e75;
        font-size: 13px;
        line-height: 1.5;
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
    .send-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: end;
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

    .ajax-toast {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 10000;
        display: none;
        max-width: 360px;
        padding: 13px 15px;
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .18);
        font-weight: 850;
        line-height: 1.4;
    }

    .ajax-toast.show {
        display: block;
    }

    .ajax-toast.success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .ajax-toast.error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .is-loading {
        opacity: .7;
        pointer-events: none;
    }

    @media (max-width: 1180px) {
        .invoice-shell {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .invoice-top,
        .info-grid,
        .send-grid,
        .extra-row {
            grid-template-columns: 1fr;
            display: grid;
        }

        .invoice-meta {
            text-align: left;
        }
    }
</style>
@endsection

@section('content')
@php
    $medicalRecord = $invoice->appointment?->medicalRecord;
    $doctorPrescription = trim((string) ($medicalRecord?->prescription ?? ''));

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
    $isEditable = $isUnpaid && empty($invoice->sent_to_patient_at);

    $hasPatientAccount = method_exists($invoice, 'hasPatientAccount')
        ? $invoice->hasPatientAccount()
        : !empty($invoice->patient_id);

    $canSendToPatient = method_exists($invoice, 'canSendToPatient')
        ? $invoice->canSendToPatient()
        : ($isUnpaid && $hasPatientAccount && empty($invoice->sent_to_patient_at));

    $cashierDisplayName = $invoice->cashier?->name
        ?? auth()->user()?->name
        ?? 'Nhân viên thu ngân';

    $bankId = config('services.bank.bank_id', env('BANK_ID', ''));
    $bankAccountNo = config('services.bank.account_no', env('BANK_ACCOUNT_NO', ''));
    $bankAccountName = config('services.bank.account_name', env('BANK_ACCOUNT_NAME', 'DENTALCARE'));

    $transferAmount = (int) round((float) (($invoice->remaining_amount ?? 0) > 0 ? $invoice->remaining_amount : $invoice->total_amount));
    $transferContent = preg_replace('/[^A-Za-z0-9]/', '', $invoice->invoice_code);

    $vietQrUrl = ($bankId && $bankAccountNo)
        ? 'https://img.vietqr.io/image/' . $bankId . '-' . $bankAccountNo . '-compact2.png?amount=' . $transferAmount . '&addInfo=' . urlencode($transferContent) . '&accountName=' . urlencode($bankAccountName)
        : null;

    $medicineItems = $invoice->medicine_items ?: [];
    $extraItems = collect($invoice->extra_items ?: [])->values()->all();
    $extraRows = max(3, count($extraItems) + 1);
@endphp

<div id="invoiceDynamicRoot" class="invoice-shell">
    <main>
        <div class="panel">
            <div class="panel-head no-print">
                <div class="panel-title">
                    <i class="ri-file-paper-2-line"></i>
                    Hóa đơn khám bệnh
                </div>

                <span class="status-badge {{ $invoice->status }}">
                    @if($isPaid)
                        <i class="ri-checkbox-circle-line"></i>
                    @elseif($isCancelled)
                        <i class="ri-close-circle-line"></i>
                    @elseif($isPaymentPending)
                        <i class="ri-bank-card-line"></i>
                    @else
                        <i class="ri-time-line"></i>
                    @endif
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="panel-body">
                <div class="invoice-box">
                    <div class="invoice-top">
                        <div class="brand">
                            <div class="brand-logo">
                                <i class="ri-tooth-line"></i>
                            </div>
                            <div>
                                <div class="brand-name">DENTALCARE</div>
                                <div class="brand-sub">Phòng khám nha khoa</div>
                                <div class="brand-sub">Hà Đông, Hà Nội</div>
                                <div class="brand-sub">Hotline: 0327745018</div>
                            </div>
                        </div>

                        <div class="invoice-meta">
                            <h2>HÓA ĐƠN KHÁM BỆNH</h2>
                            <div class="invoice-code">{{ $invoice->invoice_code }}</div>
                            <div style="margin-top:6px;color:#64748b;">
                                Ngày lập: {{ optional($invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">Bệnh nhân</div>
                            <div class="info-value">{{ $invoice->display_patient_name ?? $invoice->patient_name ?? 'Chưa có tên' }}</div>
                            <div style="color:#64748b;margin-top:4px;">
                                SĐT: {{ $invoice->display_patient_phone ?? $invoice->patient_phone ?? 'Chưa có SĐT' }}
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="info-label">Bác sĩ</div>
                            <div class="info-value">{{ $invoice->display_doctor_name ?? $invoice->doctor_name ?? 'Chưa có bác sĩ' }}</div>
                            <div style="color:#64748b;margin-top:4px;">
                                Dịch vụ: {{ $invoice->display_service_name ?? $invoice->service_name ?? 'Chưa có dịch vụ' }}
                            </div>
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
                    </div>

                    @if($doctorPrescription !== '')
                        <div class="note-box" style="margin-bottom:18px;">
                            <div class="note-title">
                                <i class="ri-capsule-line"></i>
                                Đơn thuốc / chỉ định bác sĩ kê
                            </div>
                            {{ $doctorPrescription }}
                        </div>
                    @endif

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
                                    <div style="color:#64748b;font-size:13px;margin-top:4px;">Chi phí dịch vụ khám/điều trị</div>
                                </td>
                                <td class="text-right">1</td>
                                <td class="text-right money">{{ number_format((float) ($invoice->service_price ?? $invoice->service_amount ?? 0), 0, ',', '.') }} đ</td>
                                <td class="text-right money">{{ number_format((float) ($invoice->service_price ?? $invoice->service_amount ?? 0), 0, ',', '.') }} đ</td>
                            </tr>

                            @foreach($medicineItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['name'] ?? 'Thuốc' }}</strong>
                                        @if(!empty($item['unit']))
                                            <div style="color:#64748b;font-size:13px;margin-top:4px;">Đơn vị: {{ $item['unit'] }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                                    <td class="text-right money">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }} đ</td>
                                    <td class="text-right money">{{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach

                            @foreach($extraItems as $item)
                                @if(!empty($item['name']))
                                    <tr>
                                        <td><strong>{{ $item['name'] }}</strong></td>
                                        <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                                        <td class="text-right money">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }} đ</td>
                                        <td class="text-right money">{{ number_format((float) ($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0))), 0, ',', '.') }} đ</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    <div class="summary">
                        <div class="summary-row">
                            <span>Tiền dịch vụ</span>
                            <strong>{{ number_format((float) ($invoice->service_price ?? $invoice->service_amount ?? 0), 0, ',', '.') }} đ</strong>
                        </div>

                        <div class="summary-row">
                            <span>Tiền thuốc</span>
                            <strong>{{ number_format((float) ($invoice->medicine_total ?? 0), 0, ',', '.') }} đ</strong>
                        </div>

                        <div class="summary-row">
                            <span>Phụ phí</span>
                            <strong>{{ number_format((float) ($invoice->extra_total ?? $invoice->extra_amount ?? 0), 0, ',', '.') }} đ</strong>
                        </div>

                        <div class="summary-row">
                            <span>Giảm giá</span>
                            <strong>-{{ number_format((float) ($invoice->discount_amount ?? 0), 0, ',', '.') }} đ</strong>
                        </div>

                        <div class="summary-row total">
                            <span>Tổng thanh toán</span>
                            <strong>{{ $invoice->formatted_total ?? number_format((float) ($invoice->total_amount ?? 0), 0, ',', '.') . ' đ' }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Đã thanh toán</span>
                            <strong>{{ number_format((float) ($invoice->paid_amount ?? 0), 0, ',', '.') }} đ</strong>
                        </div>

                        <div class="summary-row">
                            <span>Còn lại</span>
                            <strong>{{ number_format((float) ($invoice->remaining_amount ?? max(($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0), 0)), 0, ',', '.') }} đ</strong>
                        </div>
                    </div>

                    @if($invoice->notes)
                        <div class="note-box" style="margin-top:18px;">
                            <div class="note-title">
                                <i class="ri-sticky-note-line"></i>
                                Ghi chú hóa đơn
                            </div>
                            {{ $invoice->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <aside class="right-column no-print">
        @if($isUnpaid)
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-send-plane-line"></i>
                        Gửi hóa đơn cho bệnh nhân
                    </div>
                </div>

                <div class="panel-body">
                    @if($invoice->sent_to_patient_at)
                        <div class="pending-box">
                            <i class="ri-check-line"></i>
                            Hóa đơn đã gửi cho bệnh nhân lúc {{ optional($invoice->sent_to_patient_at)->format('d/m/Y H:i') }}.
                            @if($invoice->payment_due_at)
                                <br>Hạn thanh toán: {{ optional($invoice->payment_due_at)->format('d/m/Y H:i') }}.
                            @endif
                        </div>
                    @elseif($canSendToPatient)
                        <form method="POST" action="{{ route('employees.invoices.send-to-patient', $invoice) }}" onsubmit="return confirm('Gửi hóa đơn này cho bệnh nhân thanh toán trên tài khoản cá nhân?');">
                            @csrf
                            <div class="send-grid">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Hạn thanh toán</label>
                                    <input
                                        type="datetime-local"
                                        name="payment_due_at"
                                        class="form-control"
                                        value="{{ now()->addDays(3)->format('Y-m-d\TH:i') }}"
                                    >
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-send-plane-line"></i>
                                    Gửi BN
                                </button>
                            </div>
                        </form>
                    @elseif(!$hasPatientAccount)
                        <div class="locked-box">
                            <i class="ri-user-unfollow-line"></i>
                            Bệnh nhân này chưa có tài khoản, không thể gửi hóa đơn để thanh toán online. Thu ngân xử lý thanh toán trực tiếp tại quầy.
                        </div>
                    @else
                        <div class="locked-box">
                            Không thể gửi hóa đơn này cho bệnh nhân ở trạng thái hiện tại.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($isPaymentPending)
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-image-line"></i>
                        Bill bệnh nhân gửi
                    </div>
                </div>

                <div class="panel-body">
                    <div class="pending-box">
                        Bệnh nhân đã gửi xác nhận thanh toán lúc {{ optional($invoice->patient_paid_submitted_at)->format('d/m/Y H:i') }}.
                        Thu ngân cần kiểm tra tiền đã vào tài khoản trước khi xác nhận.
                    </div>

                    @if($invoice->patient_payment_note)
                        <div class="note-box" style="margin-top:12px;">
                            <div class="note-title">
                                <i class="ri-message-2-line"></i>
                                Ghi chú bệnh nhân
                            </div>
                            {{ $invoice->patient_payment_note }}
                        </div>
                    @endif

                    @if($invoice->patient_payment_proof_url ?? false)
    <div class="proof-image">
        <div class="proof-head">
            <div class="info-label" style="margin-bottom:0;">Ảnh bill bệnh nhân đã gửi</div>

            <div class="proof-actions">
                <a href="{{ $invoice->patient_payment_proof_url }}" target="_blank" rel="noopener" class="proof-action-btn">
                    <i class="ri-external-link-line"></i>
                    Mở ảnh
                </a>

                <a href="{{ $invoice->patient_payment_proof_url }}" download class="proof-action-btn">
                    <i class="ri-download-2-line"></i>
                    Tải ảnh
                </a>
            </div>
        </div>

        <img src="{{ $invoice->patient_payment_proof_url }}" alt="Bill thanh toán của bệnh nhân">
    </div>
@elseif($invoice->patient_payment_proof)
    <div class="locked-box" style="margin-top:12px;">
        Không tải được ảnh bill. Vui lòng kiểm tra `php artisan storage:link`.
        <br>
        File đang lưu: {{ $invoice->patient_payment_proof }}
    </div>
@endif
                </div>
            </div>
        @endif

        @if($isEditable)
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-capsule-line"></i>
                        Thuốc tính tiền
                    </div>
                </div>

                <div class="panel-body">
                    <form method="POST" action="{{ route('employees.invoices.medicines.add', $invoice) }}" class="js-ajax-invoice-form" data-success-message="Đã thêm thuốc vào hóa đơn.">
                        @csrf

                        <div class="inline-grid">
                            <div class="form-group">
                                <label>Thuốc</label>
                                <select name="medicine_id" class="form-control" required>
                                    <option value="">-- Chọn thuốc --</option>
                                    @foreach($medicines ?? [] as $medicine)
                                        <option value="{{ $medicine->id }}">
                                            {{ $medicine->display_name ?? $medicine->name }}
                                            · Tồn {{ $medicine->stock_quantity ?? 0 }} {{ $medicine->unit ?? '' }}
                                            · {{ $medicine->formatted_price ?? number_format((float) ($medicine->selling_price ?? $medicine->price ?? 0), 0, ',', '.') . ' đ' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>SL</label>
                                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-full" type="submit">
                            <i class="ri-add-line"></i>
                            Thêm thuốc
                        </button>
                    </form>

                    @if(count($medicineItems))
                        <div style="margin-top:16px;">
                            @foreach($medicineItems as $index => $item)
                                <div class="medicine-line">
                                    <div>
                                        <div class="medicine-name">{{ $item['name'] ?? 'Thuốc' }}</div>
                                        <div class="medicine-meta">
                                            SL: {{ $item['quantity'] ?? 0 }} ·
                                            {{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }} đ
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('employees.invoices.medicines.remove', [$invoice, $index]) }}" class="js-ajax-invoice-form" data-success-message="Đã xóa thuốc khỏi hóa đơn.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger-link" type="submit" title="Xóa thuốc">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-money-dollar-circle-line"></i>
                        Phụ phí / giảm giá
                    </div>
                </div>

                <div class="panel-body">
                    <form method="POST" action="{{ route('employees.invoices.extras.update', $invoice) }}" class="js-ajax-invoice-form" data-success-message="Đã cập nhật phụ phí và giảm giá.">
                        @csrf
                        @method('PUT')

                        @for($i = 0; $i < $extraRows; $i++)
                            @php $extra = $extraItems[$i] ?? []; @endphp
                            <div class="extra-row">
                                <input
                                    type="text"
                                    name="extra_items[{{ $i }}][name]"
                                    class="form-control"
                                    placeholder="Tên phụ phí"
                                    value="{{ $extra['name'] ?? '' }}"
                                >

                                <input
                                    type="number"
                                    name="extra_items[{{ $i }}][quantity]"
                                    class="form-control"
                                    placeholder="SL"
                                    min="0"
                                    step="1"
                                    value="{{ $extra['quantity'] ?? 1 }}"
                                >

                                <input
                                    type="number"
                                    name="extra_items[{{ $i }}][unit_price]"
                                    class="form-control"
                                    placeholder="Đơn giá"
                                    min="0"
                                    step="1000"
                                    value="{{ $extra['unit_price'] ?? 0 }}"
                                >
                            </div>
                        @endfor

                        <div class="form-group">
                            <label>Giảm giá</label>
                            <input
                                type="number"
                                name="discount_amount"
                                class="form-control"
                                min="0"
                                step="1000"
                                value="{{ $invoice->discount_amount ?? 0 }}"
                            >
                        </div>

                        <div class="form-group">
                            <label>Ghi chú hóa đơn</label>
                            <textarea name="notes" class="form-control">{{ $invoice->notes }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-secondary btn-full">
                            <i class="ri-save-line"></i>
                            Cập nhật hóa đơn
                        </button>
                    </form>
                </div>
            </div>
        @elseif($isUnpaid && $invoice->sent_to_patient_at)
            <div class="panel">
                <div class="panel-body">
                    <div class="locked-box">
                        <i class="ri-lock-line"></i>
                        Hóa đơn đã gửi cho bệnh nhân nên không chỉnh thuốc/phụ phí tại đây để tránh lệch số tiền bệnh nhân đang thanh toán.
                    </div>
                </div>
            </div>
        @endif

        @if($isUnpaid || $isPaymentPending)
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="ri-bank-card-line"></i>
                        Xác nhận thanh toán
                    </div>
                </div>

                <div class="panel-body">
                    <form method="POST" action="{{ route('employees.invoices.pay', $invoice) }}" onsubmit="return confirm('Xác nhận hóa đơn này đã thanh toán?');">
                        @csrf

                        <div class="form-group">
                            <label>Thu ngân</label>
                            <input type="text" class="form-control" value="{{ $cashierDisplayName }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Phương thức thanh toán</label>
                            <select name="payment_method" id="paymentMethodSelect" class="form-control" required>
                                <option value="cash">Tiền mặt</option>
                                <option value="bank_transfer" @selected($isPaymentPending)>Chuyển khoản</option>
                                <option value="card">Thẻ</option>
                                <option value="momo">MoMo</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="bank-transfer-panel" id="bankTransferPanel">
                            <div class="bank-transfer-title">
                                <i class="ri-qr-code-line"></i>
                                QR chuyển khoản cho hóa đơn này
                            </div>

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
                                    <strong>{{ number_format($transferAmount, 0, ',', '.') }} đ</strong>
                                </div>

                                <div class="transfer-row">
                                    <span>Nội dung</span>
                                    <strong>{{ $transferContent }}</strong>
                                </div>

                                <div class="transfer-note">
                                    QR đã chứa đúng số tiền và nội dung hóa đơn. Thu ngân chỉ xác nhận sau khi kiểm tra giao dịch đã vào tài khoản.
                                </div>
                            @else
                                <div class="locked-box">
                                    Chưa cấu hình tài khoản ngân hàng. Thêm `BANK_ID`, `BANK_ACCOUNT_NO`, `BANK_ACCOUNT_NAME` trong file `.env`.
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Mã giao dịch</label>
                            <input type="text" name="transaction_reference" class="form-control" placeholder="Nhập mã giao dịch nếu có">
                        </div>

                        <div class="form-group">
                            <label>Ghi chú thanh toán</label>
                            <textarea name="note" class="form-control" placeholder="Ghi chú nếu cần..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="ri-checkbox-circle-line"></i>
                            Xác nhận đã thanh toán
                        </button>
                    </form>

                    @if($isUnpaid)
                        <form method="POST" action="{{ route('employees.invoices.cancel', $invoice) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy hóa đơn này?');" style="margin-top:10px;">
                            @csrf
                            <button type="submit" class="btn btn-danger-soft btn-full">
                                <i class="ri-close-circle-line"></i>
                                Hủy hóa đơn
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @elseif($isPaid)
            <div class="panel">
                <div class="panel-body">
                    <div class="paid-box">
                        <i class="ri-checkbox-circle-line"></i>
                        Hóa đơn đã thanh toán lúc {{ optional($invoice->paid_at)->format('d/m/Y H:i') }}.
                        <br>
                        Phương thức: {{ $invoice->payment_method_label ?? $invoice->payment_method ?? 'Chưa cập nhật' }}.
                        <br>
                        Thu ngân: {{ $invoice->cashier?->name ?? $invoice->verifier?->name ?? $cashierDisplayName }}.
                    </div>
                </div>
            </div>
        @else
            <div class="panel">
                <div class="panel-body">
                    <div class="cancelled-box">
                        <i class="ri-close-circle-line"></i>
                        Hóa đơn này đã bị hủy.
                    </div>
                </div>
            </div>
        @endif
    </aside>

    @if($vietQrUrl)
        <div class="qr-modal no-print" id="qrModal" aria-hidden="true">
            <div class="qr-modal-backdrop js-close-qr-modal"></div>

            <div class="qr-modal-card" role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">
                <button type="button" class="qr-modal-close js-close-qr-modal" aria-label="Đóng mã QR">
                    <i class="ri-close-line"></i>
                </button>

                <div class="qr-modal-title" id="qrModalTitle">
                    QR chuyển khoản hóa đơn
                </div>

                <div class="qr-modal-subtitle">
                    {{ $invoice->invoice_code }} · {{ number_format($transferAmount, 0, ',', '.') }} đ · Nội dung: {{ $transferContent }}
                </div>

                <div class="qr-modal-image">
                    <img src="{{ $vietQrUrl }}" alt="QR chuyển khoản hóa đơn {{ $invoice->invoice_code }}">
                </div>
            </div>
        </div>
    @endif
</div>

<div class="ajax-toast no-print" id="invoiceAjaxToast"></div>
@endsection

@section('scripts')
<script>
    function initInvoicePage() {
        const paymentMethodSelect = document.getElementById('paymentMethodSelect');
        const bankTransferPanel = document.getElementById('bankTransferPanel');

        function toggleBankTransferPanel() {
            if (!paymentMethodSelect || !bankTransferPanel) {
                return;
            }

            bankTransferPanel.classList.toggle('show', paymentMethodSelect.value === 'bank_transfer');
        }

        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', toggleBankTransferPanel);
            toggleBankTransferPanel();
        }

        bindQrModal();
        bindInvoiceAjaxForms();
    }

    function bindQrModal() {
        const modal = document.getElementById('qrModal');
        const openButtons = document.querySelectorAll('.js-open-qr-modal');
        const closeButtons = document.querySelectorAll('.js-close-qr-modal');

        if (!modal) {
            return;
        }

        openButtons.forEach((button) => {
            button.onclick = function () {
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };
        });

        closeButtons.forEach((button) => {
            button.onclick = closeQrModal;
        });
    }

    function closeQrModal() {
        const modal = document.getElementById('qrModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function showInvoiceToast(message, type = 'success') {
        const toast = document.getElementById('invoiceAjaxToast');

        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.className = 'ajax-toast no-print show ' + type;

        window.clearTimeout(window.invoiceToastTimer);
        window.invoiceToastTimer = window.setTimeout(() => {
            toast.classList.remove('show');
        }, 2600);
    }

    function bindInvoiceAjaxForms() {
        document.querySelectorAll('.js-ajax-invoice-form').forEach((form) => {
            form.onsubmit = async function (event) {
                event.preventDefault();

                const submitButton = form.querySelector('[type="submit"]');
                const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

                if (submitButton) {
                    submitButton.classList.add('is-loading');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="ri-loader-4-line"></i> Đang lưu...';
                }

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const html = await response.text();

                    if (!response.ok) {
                        throw new Error('Không thể cập nhật hóa đơn. Vui lòng kiểm tra lại dữ liệu.');
                    }

                    const parser = new DOMParser();
                    const nextDocument = parser.parseFromString(html, 'text/html');
                    const nextRoot = nextDocument.querySelector('#invoiceDynamicRoot');
                    const currentRoot = document.querySelector('#invoiceDynamicRoot');

                    if (!nextRoot || !currentRoot) {
                        window.location.reload();
                        return;
                    }

                    currentRoot.innerHTML = nextRoot.innerHTML;
                    initInvoicePage();

                    showInvoiceToast(form.dataset.successMessage || 'Đã cập nhật hóa đơn.', 'success');
                } catch (error) {
                    showInvoiceToast(error.message || 'Có lỗi khi cập nhật hóa đơn.', 'error');
                } finally {
                    if (submitButton) {
                        submitButton.classList.remove('is-loading');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonHtml;
                    }
                }
            };
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initInvoicePage();

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeQrModal();
            }
        });
    });
</script>
@endsection