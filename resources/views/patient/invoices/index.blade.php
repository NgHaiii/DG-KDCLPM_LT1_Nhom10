@extends('layouts.patient-layout')

@section('title', 'Hóa đơn & Thanh toán')

@section('styles')
<style>
    .page-head {
        margin-bottom: 22px;
    }

    .page-title {
        color: #0f172a;
        font-size: 34px;
        font-weight: 950;
        margin-bottom: 6px;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 15px;
        font-weight: 600;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card,
    .filter-card,
    .invoice-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }

    .stat-card {
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.blue { background: #e0f2fe; color: #0284c7; }
    .stat-icon.green { background: #dcfce7; color: #16a34a; }
    .stat-icon.amber { background: #fef3c7; color: #d97706; }
    .stat-icon.red { background: #fee2e2; color: #dc2626; }

    .stat-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .stat-value {
        color: #0f172a;
        font-size: 25px;
        font-weight: 950;
        line-height: 1.1;
        word-break: break-word;
    }

    .filter-card {
        padding: 16px;
        margin-bottom: 20px;
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
    }

    .search-input {
        width: 100%;
        height: 46px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        padding: 0 14px 0 42px;
        outline: none;
        color: #0f172a;
        font-size: 14px;
    }

    .search-input:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .quick-note {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .invoice-card {
        overflow: hidden;
    }

    .card-head {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .card-title {
        color: #0f172a;
        font-size: 21px;
        font-weight: 950;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-title i {
        color: #0ea5e9;
    }

    .invoice-count {
        background: #e0f2fe;
        color: #0284c7;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .invoice-list {
        display: grid;
    }

    .invoice-head,
    .invoice-row {
        display: grid;
        grid-template-columns: 1.15fr 1.3fr .95fr 1.05fr 150px;
        gap: 18px;
        align-items: center;
    }

    .invoice-head {
        padding: 12px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .035em;
    }

    .invoice-row {
        padding: 18px 22px;
        border-bottom: 1px solid #edf2f7;
        transition: background .18s ease;
    }

    .invoice-row:hover {
        background: #f8fbff;
    }

    .invoice-row:last-child {
        border-bottom: 0;
    }

    .invoice-code {
        color: #0369a1;
        font-size: 16px;
        font-weight: 950;
        text-decoration: none;
        word-break: break-word;
    }

    .invoice-code:hover {
        color: #0284c7;
    }

    .primary-text {
        color: #0f172a;
        font-size: 15px;
        font-weight: 850;
        line-height: 1.35;
    }

    .muted {
        color: #64748b;
        font-size: 13px;
        margin-top: 4px;
        line-height: 1.35;
    }

    .money {
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
        white-space: nowrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
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

    .detail-btn {
        height: 40px;
        padding: 0 14px;
        border-radius: 12px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        box-shadow: 0 8px 18px rgba(14, 165, 233, .22);
        white-space: nowrap;
    }

    .detail-btn:hover {
        color: #fff;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
    }

    .empty-state {
        padding: 58px 20px;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        display: block;
        color: #bae6fd;
        font-size: 56px;
        margin-bottom: 10px;
    }

    .pagination-wrap {
        padding: 18px 22px;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .invoice-head {
            display: none;
        }

        .invoice-row {
            grid-template-columns: 1fr 1fr;
            align-items: start;
        }
    }

    @media (max-width: 760px) {
        .stats-grid,
        .invoice-row {
            grid-template-columns: 1fr;
        }

        .card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .detail-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@php
    $visibleInvoices = collect($invoices->items());

    $paidVisibleCount = $visibleInvoices->where('status', 'paid')->count();
    $unpaidVisibleCount = $visibleInvoices->where('status', 'unpaid')->count();
    $pendingVisibleCount = $visibleInvoices->where('status', 'payment_pending')->count();
    $paidVisibleTotal = $visibleInvoices->where('status', 'paid')->sum('paid_amount');
@endphp

<div class="page-head">
    <h1 class="page-title">Hóa đơn & Thanh toán</h1>
    <p class="page-subtitle">
        Theo dõi hóa đơn phòng khám đã gửi, thanh toán chuyển khoản và lịch sử thanh toán của bạn.
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="ri-file-list-3-line"></i>
        </div>
        <div>
            <div class="stat-label">Hóa đơn được gửi</div>
            <div class="stat-value">{{ $invoices->total() }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="ri-time-line"></i>
        </div>
        <div>
            <div class="stat-label">Chờ thanh toán</div>
            <div class="stat-value">{{ $unpaidVisibleCount }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="ri-bank-card-line"></i>
        </div>
        <div>
            <div class="stat-label">Chờ xác nhận bill</div>
            <div class="stat-value">{{ $pendingVisibleCount }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="ri-checkbox-circle-line"></i>
        </div>
        <div>
            <div class="stat-label">Đã thanh toán</div>
            <div class="stat-value">{{ number_format($paidVisibleTotal, 0, ',', '.') }}đ</div>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="search-box">
        <i class="ri-search-line"></i>
        <input
            type="text"
            id="invoiceQuickSearch"
            class="search-input"
            placeholder="Tìm nhanh theo mã hóa đơn, dịch vụ, bác sĩ, trạng thái..."
            autocomplete="off"
        >
    </div>

    
</div>

<div class="invoice-card">
    <div class="card-head">
        <div class="card-title">
            <i class="ri-receipt-line"></i>
            Danh sách hóa đơn
        </div>

        <div class="invoice-count js-invoice-count">
            {{ $invoices->count() }} hóa đơn đang hiển thị
        </div>
    </div>

    <div class="invoice-list">
        <div class="invoice-head">
            <div>Hóa đơn</div>
            <div>Dịch vụ</div>
            <div>Bác sĩ</div>
            <div>Thanh toán</div>
            <div style="text-align:right;">Thao tác</div>
        </div>

        @forelse($invoices as $invoice)
            @php
                $isOverdue = method_exists($invoice, 'isPaymentOverdue')
                    ? $invoice->isPaymentOverdue()
                    : ($invoice->status === 'unpaid' && $invoice->payment_due_at && now()->greaterThan($invoice->payment_due_at));

                $statusLabel = $invoice->status_label ?? match ($invoice->status) {
                    'paid' => 'Đã thanh toán',
                    'payment_pending' => 'Chờ xác nhận bill',
                    'cancelled' => 'Đã hủy',
                    default => 'Chờ thanh toán',
                };

                $displayStatusLabel = $isOverdue ? 'Quá hạn thanh toán' : $statusLabel;
                $statusClass = $isOverdue ? 'overdue' : $invoice->status;

                $searchText = implode(' ', array_filter([
                    $invoice->invoice_code,
                    $invoice->display_service_name,
                    $invoice->display_doctor_name,
                    $displayStatusLabel,
                    optional($invoice->appointment_date)->format('d/m/Y H:i'),
                    optional($invoice->payment_due_at)->format('d/m/Y H:i'),
                ]));
            @endphp

            <div class="invoice-row" data-search="{{ e($searchText) }}">
                <div>
                    <a href="{{ route('patient.invoices.show', $invoice) }}" class="invoice-code">
                        {{ $invoice->invoice_code }}
                    </a>

                    <div class="muted">
                        Gửi: {{ optional($invoice->sent_to_patient_at ?? $invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}
                    </div>

                    @if($invoice->payment_due_at && $invoice->status !== 'paid')
                        <div class="muted">
                            <i class="ri-alarm-line"></i>
                            Hạn: {{ optional($invoice->payment_due_at)->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>

                <div>
                    <div class="primary-text">{{ $invoice->display_service_name ?? $invoice->service_name ?? 'Chưa có dịch vụ' }}</div>

                    <div class="muted">
                        <i class="ri-calendar-line"></i>
                        {{ optional($invoice->appointment_date)->format('d/m/Y H:i') ?: 'Chưa có ngày khám' }}
                    </div>

                    @if($invoice->appointment?->room)
                        <div class="muted">
                            <i class="ri-building-line"></i>
                            {{ $invoice->appointment->room->name }}
                        </div>
                    @endif
                </div>

                <div>
                    <div class="primary-text">{{ $invoice->display_doctor_name ?? $invoice->doctor_name ?? 'Chưa có bác sĩ' }}</div>
                    <div class="muted">Bác sĩ phụ trách</div>
                </div>

                <div>
                    <div class="money">
                        {{ $invoice->formatted_total ?? number_format($invoice->total_amount ?? 0, 0, ',', '.') . 'đ' }}
                    </div>

                    <div style="margin-top:7px;">
                        <span class="badge {{ $statusClass }}">
                            @if($invoice->status === 'paid')
                                <i class="ri-checkbox-circle-line"></i>
                            @elseif($invoice->status === 'payment_pending')
                                <i class="ri-bank-card-line"></i>
                            @elseif($invoice->status === 'cancelled')
                                <i class="ri-close-circle-line"></i>
                            @elseif($isOverdue)
                                <i class="ri-alarm-warning-line"></i>
                            @else
                                <i class="ri-time-line"></i>
                            @endif
                            {{ $displayStatusLabel }}
                        </span>
                    </div>

                    @if($invoice->status === 'payment_pending' && $invoice->patient_paid_submitted_at)
                        <div class="muted">
                            Đã gửi bill: {{ optional($invoice->patient_paid_submitted_at)->format('d/m/Y H:i') }}
                        </div>
                    @elseif($invoice->status === 'paid' && $invoice->paid_at)
                        <div class="muted">
                            Thanh toán: {{ optional($invoice->paid_at)->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>

                <div>
                    <a href="{{ route('patient.invoices.show', $invoice) }}" class="detail-btn">
                        <i class="{{ $invoice->status === 'unpaid' ? 'ri-bank-card-line' : 'ri-eye-line' }}"></i>
                        {{ $invoice->status === 'unpaid' ? 'Thanh toán' : 'Chi tiết' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="ri-receipt-line"></i>
                <strong>Chưa có hóa đơn được gửi</strong>
                <div>Hóa đơn chỉ hiển thị khi phòng khám gửi hóa đơn thanh toán cho tài khoản của bạn.</div>
            </div>
        @endforelse

        <div id="clientEmptyRow" class="empty-state" style="display:none;">
            <i class="ri-search-line"></i>
            <strong>Không tìm thấy hóa đơn phù hợp</strong>
            <div>Thử nhập mã hóa đơn, dịch vụ, bác sĩ hoặc trạng thái khác.</div>
        </div>
    </div>

    @if($invoices->hasPages())
        <div class="pagination-wrap">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const searchInput = document.getElementById('invoiceQuickSearch');
    const rows = Array.from(document.querySelectorAll('.invoice-row'));
    const countEl = document.querySelector('.js-invoice-count');
    const statusEl = document.getElementById('quickSearchStatus');
    const emptyRow = document.getElementById('clientEmptyRow');

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .trim();
    }

    function filterRows() {
        const keyword = normalizeText(searchInput.value);
        let visible = 0;

        rows.forEach(row => {
            const text = normalizeText((row.dataset.search || '') + ' ' + row.innerText);
            const matched = keyword === '' || text.includes(keyword);

            row.style.display = matched ? '' : 'none';

            if (matched) {
                visible++;
            }
        });

        if (countEl) {
            countEl.textContent = `${visible} hóa đơn đang hiển thị`;
        }

        if (emptyRow) {
            emptyRow.style.display = visible === 0 && rows.length > 0 ? '' : 'none';
        }

        if (statusEl) {
            statusEl.innerHTML = keyword
                ? `<i class="ri-search-line"></i> Đang hiển thị ${visible} kết quả phù hợp.`
                : `<i class="ri-flashlight-line"></i> Kết quả tự cập nhật khi nhập, không tải lại trang.`;
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }
</script>
@endsection