@extends('layouts.employee-layout')

@section('title', 'Chi tiết hóa đơn')
@section('page-title', 'Chi tiết hóa đơn')
@section('page-subtitle', 'Kiểm tra chi phí khám, đơn thuốc, phụ phí và xác nhận thanh toán')

@section('header-actions')
    <a href="{{ route('employees.invoices.index') }}" class="btn btn-secondary no-print">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>

    <a
        href="{{ route('employees.invoices.print', $invoice) }}"
        class="btn btn-primary no-print"
        target="_blank"
        rel="noopener"
    >
        <i class="ri-printer-line"></i>
        In hóa đơn
    </a>
@endsection

@section('styles')
<style>
    .invoice-shell {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) 420px;
        gap: 22px;
        align-items: start;
    }

    .panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 22px;
    }

    .panel-head {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-size: 20px;
        font-weight: 900;
    }

    .panel-title i {
        color: #0ea5e9;
    }

    .panel-body {
        padding: 22px;
    }

    .invoice-print-box {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 18px;
        padding: 28px;
    }

    .print-header {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 18px;
        margin-bottom: 20px;
    }

    .brand {
        display: flex;
        gap: 14px;
        align-items: center;
    }

    .brand-logo {
        width: 54px;
        height: 54px;
        border-radius: 15px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
    }

    .brand-name {
        font-size: 25px;
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
        font-size: 24px;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .invoice-code {
        color: #0284c7;
        font-size: 16px;
        font-weight: 900;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        margin-top: 8px;
    }

    .status-badge.unpaid {
        background: #fef3c7;
        color: #92400e;
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
        gap: 14px;
        margin-bottom: 20px;
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
        font-size: 16px;
        font-weight: 850;
        line-height: 1.4;
    }

    .prescription-box {
        margin: 18px 0 20px;
        padding: 16px;
        border: 1px solid #bae6fd;
        border-radius: 15px;
        background: #f0f9ff;
    }

    .prescription-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0369a1;
        font-size: 15px;
        font-weight: 900;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .prescription-content {
        color: #0f172a;
        line-height: 1.65;
        white-space: pre-line;
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
        padding: 12px;
        text-align: left;
        border: 1px solid #e2e8f0;
    }

    .bill-table td {
        padding: 12px;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        vertical-align: top;
    }

    .text-right {
        text-align: right;
    }

    .money {
        font-weight: 900;
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

    .form-group {
        margin-bottom: 15px;
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
        min-height: 44px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 0 13px;
        outline: none;
        color: #0f172a;
        background: #fff;
    }

    textarea.form-control {
        min-height: 92px;
        padding: 12px 13px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .inline-grid {
        display: grid;
        grid-template-columns: 1fr 110px;
        gap: 10px;
    }

    .extra-row {
        display: grid;
        grid-template-columns: 1fr 90px 130px;
        gap: 8px;
        margin-bottom: 9px;
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
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        margin-bottom: 9px;
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

    .transfer-row:last-child {
        border-bottom: 0;
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

    .transfer-warning {
        padding: 12px;
        border-radius: 12px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        line-height: 1.5;
        font-weight: 750;
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
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(3px);
    }

    .qr-modal-card {
        position: relative;
        width: min(460px, 100%);
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.35);
        padding: 22px;
        animation: qrPop .18s ease;
    }

    @keyframes qrPop {
        from {
            transform: translateY(8px) scale(.98);
            opacity: .6;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
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

    .paid-box {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #bbf7d0;
        padding: 14px;
        border-radius: 14px;
        font-weight: 750;
        line-height: 1.5;
    }

    .cancelled-box {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        padding: 14px;
        border-radius: 14px;
        font-weight: 750;
        line-height: 1.5;
    }

    .print-footer {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 30px;
        margin-top: 36px;
        text-align: center;
        color: #0f172a;
    }

    .signature-space {
        height: 70px;
    }

    @media (max-width: 1180px) {
        .invoice-shell {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            background: #fff !important;
        }

        .sidebar,
        .header,
        .no-print,
        .right-column,
        .alert,
        .qr-modal,
        .ajax-toast {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .container {
            max-width: none !important;
            margin: 0 !important;
        }

        .invoice-shell {
            display: block !important;
        }

        .panel {
            box-shadow: none !important;
            border: 0 !important;
            margin: 0 !important;
        }

        .panel-head {
            display: none !important;
        }

        .panel-body {
            padding: 0 !important;
        }

        .invoice-print-box {
            border: 0 !important;
            border-radius: 0 !important;
            padding: 0 !important;
        }
    }
</style>
@endsection

@section('content')
@php
    $medicalRecord = $invoice->appointment?->medicalRecord;
    $doctorPrescription = trim((string) ($medicalRecord?->prescription ?? ''));

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
@endphp

    <div id="invoiceDynamicRoot" class="invoice-shell">
        <div class="left-column">
            <div class="panel">
                <div class="panel-head no-print">
                    <div class="panel-title">
                        <i class="ri-file-paper-2-line"></i>
                        Hóa đơn khám bệnh
                    </div>

                    <span class="status-badge {{ $invoice->status }}">
                        {{ $invoice->status_label }}
                    </span>
                </div>

                <div class="panel-body">
                    <div class="invoice-print-box" id="invoicePrintArea">
                        <div class="print-header">
                            <div class="brand">
                                <div class="brand-logo">
                                    <i class="ri-tooth-line"></i>
                                </div>
                                <div>
                                    <div class="brand-name">DENTALCARE</div>
                                    <div class="brand-sub">Phòng khám nha khoa</div>
                                    <div class="brand-sub">Địa chỉ: Hà Đông, Hà Nội</div>
                                    <div class="brand-sub">Hotline: 0327745018</div>
                                </div>
                            </div>

                            <div class="invoice-meta">
                                <h2>HÓA ĐƠN KHÁM BỆNH</h2>
                                <div class="invoice-code">{{ $invoice->invoice_code }}</div>
                                <div style="margin-top:6px;color:#64748b;">
                                    Ngày lập: {{ optional($invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}
                                </div>
                                <div class="status-badge {{ $invoice->status }}">
                                    {{ $invoice->status_label }}
                                </div>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-box">
                                <div class="info-label">Bệnh nhân</div>
                                <div class="info-value">{{ $invoice->display_patient_name }}</div>
                                <div style="color:#64748b;margin-top:4px;">
                                    SĐT: {{ $invoice->display_patient_phone }}
                                </div>
                            </div>

                            <div class="info-box">
                                <div class="info-label">Bác sĩ</div>
                                <div class="info-value">{{ $invoice->display_doctor_name }}</div>
                                <div style="color:#64748b;margin-top:4px;">
                                    Dịch vụ: {{ $invoice->display_service_name }}
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
                            <div class="prescription-box">
                                <div class="prescription-title">
                                    <i class="ri-capsule-line"></i>
                                    Đơn thuốc / chỉ định bác sĩ kê
                                </div>
                                <div class="prescription-content">{{ $doctorPrescription }}</div>
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
                                        <strong>{{ $invoice->display_service_name }}</strong>
                                        <div style="color:#64748b;font-size:13px;margin-top:4px;">Chi phí dịch vụ khám/điều trị</div>
                                    </td>
                                    <td class="text-right">1</td>
                                    <td class="text-right">{{ number_format($invoice->service_price, 0, ',', '.') }} đ</td>
                                    <td class="text-right money">{{ number_format($invoice->service_price, 0, ',', '.') }} đ</td>
                                </tr>

                                @foreach(($invoice->medicine_items ?: []) as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item['name'] ?? 'Thuốc' }}</strong>
                                            <div style="color:#64748b;font-size:13px;margin-top:4px;">
                                                Mã: {{ $item['code'] ?? '-' }} · Đơn vị: {{ $item['unit'] ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="text-right">{{ $item['quantity'] ?? 0 }}</td>
                                        <td class="text-right">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }} đ</td>
                                        <td class="text-right money">{{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach

                                @foreach(($invoice->extra_items ?: []) as $item)
                                    <tr>
                                        <td><strong>{{ $item['name'] ?? 'Phụ phí' }}</strong></td>
                                        <td class="text-right">{{ $item['quantity'] ?? 0 }}</td>
                                        <td class="text-right">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }} đ</td>
                                        <td class="text-right money">{{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="summary">
                            <div class="summary-row">
                                <span>Tiền dịch vụ</span>
                                <strong>{{ number_format($invoice->service_price, 0, ',', '.') }} đ</strong>
                            </div>

                            <div class="summary-row">
                                <span>Tiền thuốc</span>
                                <strong>{{ number_format($invoice->medicine_total, 0, ',', '.') }} đ</strong>
                            </div>

                            <div class="summary-row">
                                <span>Phụ phí</span>
                                <strong>{{ number_format($invoice->extra_total, 0, ',', '.') }} đ</strong>
                            </div>

                            <div class="summary-row">
                                <span>Giảm giá</span>
                                <strong>-{{ number_format($invoice->discount_amount, 0, ',', '.') }} đ</strong>
                            </div>

                            <div class="summary-row total">
                                <span>Tổng thanh toán</span>
                                <strong>{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</strong>
                            </div>

                            <div class="summary-row">
                                <span>Đã thanh toán</span>
                                <strong>{{ number_format($invoice->paid_amount, 0, ',', '.') }} đ</strong>
                            </div>

                            <div class="summary-row">
                                <span>Còn lại</span>
                                <strong>{{ number_format($invoice->remaining_amount, 0, ',', '.') }} đ</strong>
                            </div>
                        </div>

                        @if($invoice->notes)
                            <div style="margin-top:20px;padding:14px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc;">
                                <strong>Ghi chú:</strong>
                                <div style="margin-top:6px;white-space:pre-line;">{{ $invoice->notes }}</div>
                            </div>
                        @endif

                        <div class="print-footer">
                            <div>
                                <strong>Người thanh toán</strong>
                                <div class="signature-space"></div>
                                <div>{{ $invoice->display_patient_name }}</div>
                            </div>

                            <div>
                                <strong>Thu ngân</strong>
                                <div class="signature-space"></div>
                                <div>{{ $cashierDisplayName }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <aside class="right-column no-print">
            @if($invoice->isUnpaid())
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title">
                            <i class="ri-capsule-line"></i>
                            Thêm thuốc tính phí
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
                                        @foreach($medicines as $medicine)
                                            <option value="{{ $medicine->id }}">
                                                {{ $medicine->display_name }} · Tồn {{ $medicine->stock_quantity }} {{ $medicine->unit }} · {{ $medicine->formatted_price }}
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

                        @if(count($invoice->medicine_items ?: []))
                            <div style="margin-top:16px;">
                                @foreach(($invoice->medicine_items ?: []) as $index => $item)
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

                            @php
                                $extraItems = collect($invoice->extra_items ?: [])->values()->all();
                                $extraRows = max(3, count($extraItems) + 1);
                            @endphp

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
                                    value="{{ $invoice->discount_amount }}"
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

                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title">
                            <i class="ri-bank-card-line"></i>
                            Xác nhận thanh toán
                        </div>
                    </div>

                    <div class="panel-body">
                        <form method="POST" action="{{ route('employees.invoices.pay', $invoice) }}" onsubmit="return confirm('Xác nhận bệnh nhân đã thanh toán hóa đơn này?');">
                            @csrf

                            <div class="form-group">
                                <label>Thu ngân</label>
                                <input type="text" class="form-control" value="{{ $cashierDisplayName }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Phương thức thanh toán</label>
                                <select name="payment_method" id="paymentMethodSelect" class="form-control" required>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="bank_transfer">Chuyển khoản</option>
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
                                        QR đã chứa đúng số tiền và nội dung hóa đơn. Thu ngân chỉ bấm xác nhận sau khi kiểm tra giao dịch đã vào tài khoản.
                                    </div>
                                @else
                                    <div class="transfer-warning">
                                        Chưa cấu hình tài khoản ngân hàng. Thêm `BANK_ID`, `BANK_ACCOUNT_NO`, `BANK_ACCOUNT_NAME` trong file `.env` để hiển thị QR chuyển khoản.
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

                        <form method="POST" action="{{ route('employees.invoices.cancel', $invoice) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy hóa đơn này?');" style="margin-top:10px;">
                            @csrf
                            <button type="submit" class="btn btn-danger-soft btn-full">
                                <i class="ri-close-circle-line"></i>
                                Hủy hóa đơn
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($invoice->isPaid())
                <div class="panel">
                    <div class="panel-body">
                        <div class="paid-box">
                            <i class="ri-checkbox-circle-line"></i>
                            Hóa đơn đã thanh toán lúc {{ optional($invoice->paid_at)->format('d/m/Y H:i') }}.
                            <br>
                            Phương thức: {{ $invoice->payment_method_label }}.
                            <br>
                            Thu ngân: {{ $invoice->cashier?->name ?? $cashierDisplayName }}.
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
                        <img src="{{ $vietQrUrl }}" alt="QR chuyển khoản hóa đơn ">
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
            paymentMethodSelect.removeEventListener('change', toggleBankTransferPanel);
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
            button.onclick = function () {
                closeQrModal();
            };
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

@if(!empty($printMode))
<script>
    window.addEventListener('load', () => {
        setTimeout(() => window.print(), 300);
    });
</script>
@endif
@endsection