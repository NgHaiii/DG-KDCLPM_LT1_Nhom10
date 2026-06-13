@extends('layouts.employee-layout')

@section('title', 'Hóa đơn & Thanh toán')
@section('page-title', 'Hóa đơn & Thanh toán')
@section('page-subtitle', 'Thu ngân xử lý hóa đơn tự sinh từ ca khám đã hoàn thành')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card, .filter-card, .invoice-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .stat-card {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
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
        font-weight: 750;
        margin-bottom: 4px;
    }

    .stat-value {
        color: #0f172a;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .filter-card {
        padding: 18px;
        margin-bottom: 22px;
    }

    .filter-form {
        display: grid;
        grid-template-columns: 1.4fr 180px 220px auto auto;
        gap: 12px;
        align-items: end;
    }

    .form-group label {
        display: block;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        height: 44px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 0 14px;
        color: #0f172a;
        outline: none;
        transition: .2s;
        background: #fff;
    }

    .form-control:focus {
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
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
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
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        min-width: 1450px;
        border-collapse: collapse;
    }

    th {
        background: #f8fafc;
        color: #475569;
        text-align: left;
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1px solid #e2e8f0;
    }

    td {
        padding: 16px;
        border-bottom: 1px solid #edf2f7;
        color: #0f172a;
        vertical-align: middle;
    }

    tbody tr:hover td {
        background: #f8fbff;
    }

    .invoice-code {
        font-weight: 900;
        color: #0369a1;
    }

    .muted {
        color: #64748b;
        font-size: 13px;
        margin-top: 4px;
    }

    .patient-name {
        font-weight: 850;
        color: #0f172a;
    }

    .money {
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
    }

    .badge, .source-badge {
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
    .badge.paid { background: #dcfce7; color: #166534; }
    .badge.cancelled { background: #fee2e2; color: #991b1b; }

    .source-badge.online {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .source-badge.offline {
        background: #dcfce7;
        color: #166534;
    }

    .prescription-box {
        max-width: 240px;
        display: flex;
        align-items: flex-start;
        gap: 7px;
        color: #334155;
        font-size: 13px;
        line-height: 1.45;
    }

    .prescription-box i {
        color: #0ea5e9;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .action-wrap {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-btn {
        min-height: 40px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 850;
        transition: .2s;
        white-space: nowrap;
    }

    .action-btn:hover {
        border-color: #0ea5e9;
        color: #0284c7;
        background: #f0f9ff;
    }

    .action-btn.primary {
        border-color: #0ea5e9;
        background: #0ea5e9;
        color: #fff;
        box-shadow: 0 8px 18px rgba(14, 165, 233, .24);
    }

    .action-btn.primary:hover {
        background: #0284c7;
        color: #fff;
    }

    .icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 18px;
        transition: .2s;
    }

    .icon-btn:hover {
        border-color: #0ea5e9;
        color: #0284c7;
        background: #f0f9ff;
    }

    .empty-state {
        padding: 56px 20px;
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
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .filter-form { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 640px) {
        .stats-grid,
        .filter-form {
            grid-template-columns: 1fr;
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

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Hóa đơn</th>
                        <th>Bệnh nhân</th>
                        <th>Nguồn</th>
                        <th>Dịch vụ</th>
                        <th>Đơn thuốc bác sĩ kê</th>
                        <th>Bác sĩ</th>
                        <th>Ngày khám</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align:right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $statusClass = $invoice->status;
                            $source = $invoice->appointment?->source ?? $invoice->patientProfile?->source ?? 'online';
                            $sourceLabel = $source === 'offline' ? 'Khám trực tiếp' : 'Đặt online';
                            $sourceClass = $source === 'offline' ? 'offline' : 'online';
                            $prescription = $invoice->appointment?->medicalRecord?->prescription;

                            $searchText = implode(' ', array_filter([
                                $invoice->invoice_code,
                                $invoice->display_patient_name,
                                $invoice->display_patient_phone,
                                $sourceLabel,
                                $invoice->display_service_name,
                                $prescription,
                                $invoice->display_doctor_name,
                                $invoice->status_label,
                            ]));
                        @endphp

                        <tr class="invoice-row" data-search="{{ e($searchText) }}">
                            <td>
                                <div class="invoice-code">{{ $invoice->invoice_code }}</div>
                                <div class="muted">
                                    Lập: {{ optional($invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td>
                                <div class="patient-name">{{ $invoice->display_patient_name }}</div>
                                <div class="muted">
                                    <i class="ri-phone-line"></i>
                                    {{ $invoice->display_patient_phone }}
                                </div>
                            </td>

                            <td>
                                <span class="source-badge {{ $sourceClass }}">
                                    <i class="{{ $source === 'offline' ? 'ri-user-received-line' : 'ri-global-line' }}"></i>
                                    {{ $sourceLabel }}
                                </span>
                            </td>

                            <td>
                                <div>{{ $invoice->display_service_name }}</div>
                                @if($invoice->appointment?->room)
                                    <div class="muted">
                                        <i class="ri-building-line"></i>
                                        {{ $invoice->appointment->room->name }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($prescription)
                                    <div class="prescription-box">
                                        <i class="ri-capsule-line"></i>
                                        <span>{{ \Illuminate\Support\Str::limit($prescription, 90) }}</span>
                                    </div>
                                @else
                                    <span class="muted">Chưa có đơn thuốc</span>
                                @endif
                            </td>

                            <td>{{ $invoice->display_doctor_name }}</td>

                            <td>
                                {{ optional($invoice->appointment_date)->format('d/m/Y H:i') ?: 'Chưa có' }}
                            </td>

                            <td>
                                <span class="money">{{ $invoice->formatted_total }}</span>
                            </td>

                            <td>
                                <span class="badge {{ $statusClass }}">
                                    @if($invoice->status === 'paid')
                                        <i class="ri-checkbox-circle-line"></i>
                                    @elseif($invoice->status === 'cancelled')
                                        <i class="ri-close-circle-line"></i>
                                    @else
                                        <i class="ri-time-line"></i>
                                    @endif
                                    {{ $invoice->status_label }}
                                </span>
                            </td>

                            <td>
                                <div class="action-wrap">
                                    @if($invoice->isUnpaid())
                                        <a href="{{ route('employees.invoices.show', $invoice) }}" class="action-btn primary" title="Thanh toán hóa đơn">
                                            <i class="ri-bank-card-line"></i>
                                            Thanh toán
                                        </a>
                                    @else
                                        <a href="{{ route('employees.invoices.show', $invoice) }}" class="action-btn" title="Xem hóa đơn">
                                            <i class="ri-file-text-line"></i>
                                            Hóa đơn
                                        </a>
                                    @endif

                                    <a href="{{ route('employees.invoices.print', $invoice) }}" class="icon-btn" title="In hóa đơn">
                                        <i class="ri-printer-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="ri-file-list-3-line"></i>
                                    <strong>Chưa có hóa đơn</strong>
                                    <div>Hóa đơn sẽ tự sinh sau khi bác sĩ hoàn thành ca khám.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="clientEmptyRow" style="display:none;">
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="ri-search-line"></i>
                                <strong>Không tìm thấy hóa đơn phù hợp</strong>
                                <div>Thử nhập mã hóa đơn, tên bệnh nhân, SĐT, dịch vụ hoặc đơn thuốc khác.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
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

            if (matched) visible++;
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