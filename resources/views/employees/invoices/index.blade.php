@extends('layouts.employee-layout')

@section('title', 'Hóa đơn & Thanh toán')
@section('page-title', 'Hóa đơn & Thanh toán')
@section('page-subtitle', 'Thu ngân xử lý hóa đơn tự sinh từ ca khám đã hoàn thành')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .stat-card,
    .filter-card,
    .invoice-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055);
    }

    .stat-card {
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-icon.blue { background: #e0f2fe; color: #0284c7; }
    .stat-icon.green { background: #dcfce7; color: #16a34a; }
    .stat-icon.amber { background: #fef3c7; color: #d97706; }
    .stat-icon.red { background: #fee2e2; color: #dc2626; }

    .stat-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-bottom: 4px;
    }

    .stat-value {
        color: #0f172a;
        font-size: 24px;
        font-weight: 900;
        line-height: 1.1;
    }

    .filter-card {
        padding: 16px;
        margin-bottom: 20px;
    }

    .filter-form {
        display: grid;
        grid-template-columns: minmax(260px, 1.5fr) 160px 180px auto auto;
        gap: 10px;
        align-items: end;
    }

    .form-group label {
        display: block;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        height: 42px;
        border: 1px solid #dbe3ef;
        border-radius: 11px;
        padding: 0 12px;
        color: #0f172a;
        outline: none;
        transition: .2s;
        background: #fff;
        font-size: 13px;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .quick-note {
        margin-top: 7px;
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
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 20px;
        font-weight: 900;
        color: #0f172a;
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
        width: 100%;
    }

    .invoice-list-head {
        display: grid;
        grid-template-columns: 1.1fr 1.15fr .8fr 1.35fr 1fr 1fr 180px;
        gap: 16px;
        align-items: center;
        padding: 12px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .035em;
    }

    .invoice-row {
        display: grid;
        grid-template-columns: 1.1fr 1.15fr .8fr 1.35fr 1fr 1fr 180px;
        gap: 16px;
        align-items: center;
        padding: 18px 20px;
        border-bottom: 1px solid #edf2f7;
        transition: background .18s ease;
    }

    .invoice-row:hover {
        background: #f8fbff;
    }

    .invoice-row:last-child {
        border-bottom: 0;
    }

    .cell {
        min-width: 0;
    }

    .cell-actions {
        display: flex;
        justify-content: flex-end;
    }

    .invoice-code {
        display: inline-block;
        color: #0369a1;
        font-size: 15px;
        font-weight: 950;
        text-decoration: none;
        word-break: break-word;
        line-height: 1.3;
    }

    .invoice-code:hover {
        color: #0284c7;
    }

    .primary-text {
        color: #0f172a;
        font-size: 14px;
        font-weight: 850;
        line-height: 1.35;
        word-break: break-word;
    }

    .muted {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        line-height: 1.4;
        word-break: break-word;
    }

    .inline-meta {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge,
    .source-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        width: fit-content;
        max-width: 100%;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .badge.unpaid {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.paid {
        background: #dcfce7;
        color: #166534;
    }

    .badge.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .source-badge.online {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .source-badge.offline {
        background: #dcfce7;
        color: #166534;
    }

    .money {
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
        white-space: nowrap;
    }

    .payment-stack {
        display: grid;
        gap: 7px;
        align-items: start;
    }

    .action-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .action-btn {
        height: 38px;
        min-width: 92px;
        padding: 0 12px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        transition: .18s ease;
        white-space: nowrap;
    }

    .action-btn:hover {
        border-color: #0ea5e9;
        color: #0284c7;
        background: #f0f9ff;
        transform: translateY(-1px);
    }

    .action-btn.primary {
        border-color: #0ea5e9;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: #fff;
        box-shadow: 0 8px 18px rgba(14, 165, 233, .22);
    }

    .action-btn.primary:hover {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #fff;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 18px;
        transition: .18s ease;
        flex: 0 0 38px;
    }

    .icon-btn:hover {
        border-color: #0ea5e9;
        color: #0284c7;
        background: #f0f9ff;
        transform: translateY(-1px);
    }

    .empty-state {
        padding: 52px 20px;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        display: block;
        color: #bae6fd;
        font-size: 54px;
        margin-bottom: 10px;
    }

    .pagination-wrap {
        padding: 16px 20px;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 1280px) {
        .invoice-list-head {
            display: none;
        }

        .invoice-row {
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            align-items: start;
        }

        .cell-actions {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }

        .payment-stack {
            justify-items: start;
        }
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-form {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .stats-grid,
        .filter-form,
        .invoice-row {
            grid-template-columns: 1fr;
        }

        .card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .action-wrap {
            width: 100%;
        }

        .action-btn {
            flex: 1;
        }
    }
</style>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon amber">
                <i class="ri-bill-line"></i>
            </div>
            <div>
                <div class="stat-label">Chờ thanh toán</div>
                <div class="stat-value">{{ $unpaidCount }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="ri-checkbox-circle-line"></i>
            </div>
            <div>
                <div class="stat-label">Đã thanh toán hôm nay</div>
                <div class="stat-value">{{ $paidTodayCount }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div>
                <div class="stat-label">Doanh thu hôm nay</div>
                <div class="stat-value">{{ number_format($paidTodayTotal, 0, ',', '.') }}đ</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <i class="ri-close-circle-line"></i>
            </div>
            <div>
                <div class="stat-label">Hóa đơn đã hủy</div>
                <div class="stat-value">{{ $cancelledCount ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('employees.invoices.index') }}" class="filter-form">
            <div class="form-group">
                <label>Tìm kiếm nhanh</label>
                <input
                    type="text"
                    id="invoiceQuickSearch"
                    class="form-control"
                    placeholder="Nhập mã hóa đơn, tên bệnh nhân, SĐT, dịch vụ..."
                    autocomplete="off"
                >
                <div class="quick-note" id="quickSearchStatus">
                    <i class="ri-flashlight-line"></i>
                    Tìm nhanh theo dữ liệu đang hiển thị, không tải lại trang.
                </div>
            </div>

            <div class="form-group">
                <label>Ngày khám</label>
                <input
                    type="date"
                    name="date"
                    class="form-control"
                    value="{{ $date ?? '' }}"
                >
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="all" @selected($status === 'all')>Tất cả</option>
                    <option value="unpaid" @selected($status === 'unpaid')>Chờ thanh toán</option>
                    <option value="paid" @selected($status === 'paid')>Đã thanh toán</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Đã hủy</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="ri-filter-3-line"></i>
                Lọc
            </button>

            <a href="{{ route('employees.invoices.index') }}" class="btn btn-secondary">
                <i class="ri-close-line"></i>
                Xóa lọc
            </a>
        </form>
    </div>

    <div class="invoice-card">
        <div class="card-head">
            <div class="card-title">
                <i class="ri-file-list-3-line" style="color:#0ea5e9;"></i>
                Danh sách hóa đơn
            </div>
            <div class="invoice-count js-invoice-count">{{ $invoices->count() }} hóa đơn đang hiển thị</div>
        </div>

        <div class="invoice-list">
            <div class="invoice-list-head">
                <div>Hóa đơn</div>
                <div>Bệnh nhân</div>
                <div>Nguồn</div>
                <div>Dịch vụ</div>
                <div>Bác sĩ</div>
                <div>Thanh toán</div>
                <div style="text-align:right;">Thao tác</div>
            </div>

            @forelse($invoices as $invoice)
                @php
                    $source = $invoice->appointment?->source ?? $invoice->patientProfile?->source ?? 'online';
                    $sourceLabel = $source === 'offline' ? 'Khám trực tiếp' : 'Đặt online';
                    $sourceClass = $source === 'offline' ? 'offline' : 'online';

                    $searchText = implode(' ', array_filter([
                        $invoice->invoice_code,
                        $invoice->display_patient_name,
                        $invoice->display_patient_phone,
                        $sourceLabel,
                        $invoice->display_service_name,
                        $invoice->display_doctor_name,
                        $invoice->status_label,
                    ]));
                @endphp

                <div class="invoice-row" data-search="{{ e($searchText) }}">
                    <div class="cell">
                        <a href="{{ route('employees.invoices.show', $invoice) }}" class="invoice-code">
                            {{ $invoice->invoice_code }}
                        </a>
                        <div class="muted">
                            Lập: {{ optional($invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="cell">
                        <div class="primary-text">{{ $invoice->display_patient_name }}</div>
                        <div class="muted inline-meta">
                            <i class="ri-phone-line"></i>
                            {{ $invoice->display_patient_phone }}
                        </div>
                    </div>

                    <div class="cell">
                        <span class="source-badge {{ $sourceClass }}">
                            <i class="{{ $source === 'offline' ? 'ri-user-received-line' : 'ri-global-line' }}"></i>
                            {{ $sourceLabel }}
                        </span>
                    </div>

                    <div class="cell">
                        <div class="primary-text">{{ $invoice->display_service_name }}</div>

                        <div class="muted">
                            <span class="inline-meta">
                                <i class="ri-calendar-line"></i>
                                {{ optional($invoice->appointment_date)->format('d/m/Y H:i') ?: 'Chưa có ngày khám' }}
                            </span>
                        </div>

                        @if($invoice->appointment?->room)
                            <div class="muted">
                                <span class="inline-meta">
                                    <i class="ri-building-line"></i>
                                    {{ $invoice->appointment->room->name }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="cell">
                        <div class="primary-text">{{ $invoice->display_doctor_name }}</div>
                    </div>

                    <div class="cell">
                        <div class="payment-stack">
                            <div class="money">{{ $invoice->formatted_total }}</div>

                            <span class="badge {{ $invoice->status }}">
                                @if($invoice->status === 'paid')
                                    <i class="ri-checkbox-circle-line"></i>
                                @elseif($invoice->status === 'cancelled')
                                    <i class="ri-close-circle-line"></i>
                                @else
                                    <i class="ri-time-line"></i>
                                @endif
                                {{ $invoice->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="cell cell-actions">
                        <div class="action-wrap">
                            @if($invoice->isUnpaid())
                                <a href="{{ route('employees.invoices.show', $invoice) }}" class="action-btn primary" title="Thanh toán hóa đơn">
                                    <i class="ri-bank-card-line"></i>
                                    Thanh toán
                                </a>
                            @else
                                <a href="{{ route('employees.invoices.show', $invoice) }}" class="action-btn" title="Xem chi tiết hóa đơn">
                                    <i class="ri-file-text-line"></i>
                                    Chi tiết
                                </a>
                            @endif

                            <a
                                href="{{ route('employees.invoices.print', $invoice) }}"
                                class="icon-btn"
                                title="In hóa đơn"
                                target="_blank"
                                rel="noopener"
                            >
                                <i class="ri-printer-line"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="ri-file-list-3-line"></i>
                    <strong>Chưa có hóa đơn</strong>
                    <div>Hóa đơn sẽ tự sinh sau khi bác sĩ hoàn thành ca khám.</div>
                </div>
            @endforelse

            <div id="clientEmptyRow" class="empty-state" style="display:none;">
                <i class="ri-search-line"></i>
                <strong>Không tìm thấy hóa đơn phù hợp</strong>
                <div>Thử nhập mã hóa đơn, tên bệnh nhân, SĐT hoặc dịch vụ khác.</div>
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
            const text = normalizeText(row.dataset.search + ' ' + row.innerText);
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
                : `<i class="ri-flashlight-line"></i> Tìm nhanh theo dữ liệu đang hiển thị, không tải lại trang.`;
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }
</script>
@endsection