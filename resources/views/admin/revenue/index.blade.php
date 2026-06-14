@extends('layouts.admin-layout')

@section('title', 'Thống kê doanh thu')
@section('page-title', 'Thống kê doanh thu')
@section('page-subtitle', 'Theo dõi doanh thu từ hóa đơn đã thanh toán')

@section('styles')
<style>
    .revenue-page {
        display: grid;
        gap: 20px;
    }

    .filter-card,
    .metric-card,
    .report-card,
    .chart-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .06);
    }

    .filter-card {
        padding: 18px;
    }

    .filter-form {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
        align-items: end;
    }

    .form-group label {
        display: block;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        height: 42px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 0 12px;
        outline: none;
        color: #0f172a;
        background: #fff;
        font-size: 13px;
        font-weight: 650;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .metric-card {
        padding: 18px;
        display: flex;
        gap: 14px;
        align-items: center;
        min-width: 0;
    }

    .metric-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .metric-icon.blue { background: #e0f2fe; color: #0284c7; }
    .metric-icon.green { background: #dcfce7; color: #16a34a; }
    .metric-icon.amber { background: #fef3c7; color: #d97706; }
    .metric-icon.purple { background: #ede9fe; color: #7c3aed; }
    .metric-icon.red { background: #fee2e2; color: #dc2626; }

    .metric-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        margin-bottom: 5px;
    }

    .metric-value {
        color: #0f172a;
        font-size: 24px;
        font-weight: 950;
        line-height: 1.1;
        word-break: break-word;
    }

    .metric-sub {
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    .metric-sub.positive { color: #15803d; }
    .metric-sub.negative { color: #dc2626; }

    .chart-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(380px, .55fr);
        gap: 20px;
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .report-card,
    .chart-card {
        overflow: hidden;
    }

    .report-head {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .report-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
    }

    .report-title i {
        color: #0ea5e9;
    }

    .report-note {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 4px;
    }

    .report-body {
        padding: 20px;
    }

    .daily-chart-wrap {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 14px;
        min-height: 310px;
    }

    .chart-axis {
        display: grid;
        grid-template-rows: repeat(4, 1fr);
        align-items: start;
        padding-top: 6px;
        padding-bottom: 34px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 800;
        text-align: right;
    }

    .daily-chart {
        position: relative;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(42px, 1fr));
        gap: 10px;
        align-items: end;
        min-height: 280px;
        padding: 8px 0 0;
        border-bottom: 1px solid #e2e8f0;
        background:
            linear-gradient(to bottom, rgba(226, 232, 240, .7) 1px, transparent 1px) 0 0 / 100% 25%;
    }

    .daily-column {
        min-width: 0;
        height: 100%;
        display: grid;
        grid-template-rows: 1fr auto;
        gap: 8px;
        text-align: center;
        position: relative;
    }

    .daily-bar-holder {
        display: flex;
        align-items: end;
        justify-content: center;
        min-height: 230px;
    }

    .daily-bar {
        width: min(100%, 42px);
        min-height: 8px;
        border-radius: 12px 12px 5px 5px;
        background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%);
        box-shadow: 0 10px 18px rgba(14, 165, 233, .2);
        transition: transform .2s ease, filter .2s ease;
        position: relative;
    }

    .daily-bar:hover {
        transform: translateY(-4px);
        filter: brightness(1.04);
    }

    .daily-bar::after {
        content: attr(data-value);
        position: absolute;
        left: 50%;
        bottom: calc(100% + 8px);
        transform: translateX(-50%);
        background: #0f172a;
        color: #fff;
        border-radius: 10px;
        padding: 6px 8px;
        font-size: 11px;
        font-weight: 850;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity .15s ease;
        z-index: 5;
    }

    .daily-bar:hover::after {
        opacity: 1;
    }

    .daily-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 850;
        white-space: nowrap;
    }

    .donut-layout {
        display: grid;
        gap: 18px;
        justify-items: center;
    }

    .donut-chart {
        width: 218px;
        height: 218px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .06), 0 18px 34px rgba(15, 23, 42, .08);
    }

    .donut-hole {
        width: 132px;
        height: 132px;
        border-radius: 50%;
        background: #fff;
        display: grid;
        place-items: center;
        text-align: center;
        padding: 16px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .08);
    }

    .donut-hole span {
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
    }

    .donut-hole strong {
        color: #0f172a;
        font-size: 18px;
        font-weight: 950;
        line-height: 1.25;
        word-break: break-word;
    }

    .donut-legend {
        width: 100%;
        display: grid;
        gap: 12px;
    }

    .legend-item {
        display: grid;
        grid-template-columns: 14px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        padding: 13px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        margin-top: 4px;
    }

    .legend-dot.service { background: #0ea5e9; }
    .legend-dot.medicine { background: #7c3aed; }
    .legend-dot.extra { background: #f59e0b; }

    .legend-item strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
    }

    .legend-item small {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 3px;
        line-height: 1.4;
    }

    .breakdown-list,
    .rank-list {
        display: grid;
        gap: 14px;
    }

    .rank-item {
        display: grid;
        gap: 8px;
    }

    .rank-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .rank-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rank-money {
        white-space: nowrap;
        color: #0369a1;
    }

    .rank-track {
        height: 11px;
        border-radius: 999px;
        background: #eef2f7;
        overflow: hidden;
    }

    .rank-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
    }

    .rank-fill.green {
        background: linear-gradient(135deg, #4ade80, #16a34a);
    }

    .rank-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        min-width: 1080px;
        border-collapse: collapse;
    }

    .report-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .report-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #edf2f7;
        color: #0f172a;
        vertical-align: middle;
    }

    .report-table tr:hover {
        background: #f8fbff;
    }

    .money {
        font-weight: 950;
        white-space: nowrap;
    }

    .muted {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
        line-height: 1.4;
        font-weight: 650;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .badge.online {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.offline {
        background: #dcfce7;
        color: #166534;
    }

    .badge.cash {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.bank_transfer {
        background: #e0f2fe;
        color: #075985;
    }

    .badge.card,
    .badge.momo,
    .badge.other,
    .badge.unknown {
        background: #f1f5f9;
        color: #334155;
    }

    .empty-state {
        padding: 42px 20px;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        display: block;
        color: #bae6fd;
        font-size: 50px;
        margin-bottom: 10px;
    }

    .pagination-wrap {
        padding: 16px 20px;
        border-top: 1px solid #e2e8f0;
    }
.pagination-wrap {
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
}

.detail-btn {
    height: 38px;
    padding: 0 13px;
    border-radius: 12px;
    border: 1px solid #bae6fd;
    background: #f0f9ff;
    color: #0369a1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 900;
    white-space: nowrap;
    transition: all .18s ease;
}

.detail-btn:hover {
    background: #0ea5e9;
    border-color: #0ea5e9;
    color: #fff;
    box-shadow: 0 10px 20px rgba(14, 165, 233, .2);
}

@media (max-width: 1280px) {}
    @media (max-width: 1280px) {
        .filter-form {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .metrics-grid,
        .report-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .chart-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .filter-form,
        .metrics-grid,
        .report-grid,
        .chart-grid {
            grid-template-columns: 1fr;
        }

        .daily-chart-wrap {
            grid-template-columns: 1fr;
        }

        .chart-axis {
            display: none;
        }

        .filter-form > .btn,
        .filter-form > a {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
@php
    $previousRevenue = (float) ($previousSummary['revenue_total'] ?? 0);
    $currentRevenue = (float) ($summary['revenue_total'] ?? 0);
    $growth = $previousRevenue > 0
        ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
        : null;

    $maxDailyRevenue = max((float) $dailyRevenue->max('revenue_total'), 1);
    $maxServiceRevenue = max((float) $serviceRevenue->max('revenue_total'), 1);
    $maxDoctorRevenue = max((float) $doctorRevenue->max('revenue_total'), 1);

    $paymentLabels = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'card' => 'Thẻ',
        'momo' => 'MoMo',
        'other' => 'Khác',
        'unknown' => 'Không rõ',
    ];

    $sourceLabels = [
        'online' => 'Đặt online',
        'offline' => 'Khám trực tiếp',
    ];

    $axisMax = $maxDailyRevenue;
    $axisLabels = [
        $axisMax,
        $axisMax * 0.75,
        $axisMax * 0.5,
        $axisMax * 0.25,
    ];

    $serviceAmount = (float) ($summary['service_total'] ?? 0);
    $medicineAmount = (float) ($summary['medicine_total'] ?? 0);
    $extraAmount = (float) ($summary['extra_total'] ?? 0);
    $structureTotal = $serviceAmount + $medicineAmount + $extraAmount;
    $donutBase = max($structureTotal, 1);

    $servicePercent = $structureTotal > 0 ? ($serviceAmount / $donutBase) * 100 : 0;
    $medicinePercent = $structureTotal > 0 ? ($medicineAmount / $donutBase) * 100 : 0;
    $extraPercent = $structureTotal > 0 ? ($extraAmount / $donutBase) * 100 : 0;

    $medicineStart = $servicePercent;
    $extraStart = $servicePercent + $medicinePercent;

    $donutBackground = $structureTotal > 0
        ? "conic-gradient(#0ea5e9 0% {$servicePercent}%, #7c3aed {$servicePercent}% {$extraStart}%, #f59e0b {$extraStart}% 100%)"
        : '#e2e8f0';

    $paymentItems = collect($paymentRevenue)->map(function ($item) use ($paymentLabels, $summary) {
        $value = (float) ($item->revenue_total ?? 0);
        $total = max((float) ($summary['revenue_total'] ?? 0), 1);

        return [
            'label' => $paymentLabels[$item->label] ?? $item->label,
            'value' => $value,
            'count' => (int) ($item->invoice_count ?? 0),
            'percent' => ($value / $total) * 100,
        ];
    });

    $sourceItems = collect($sourceRevenue)->map(function ($item) use ($sourceLabels, $summary) {
        $key = is_array($item) ? ($item['label'] ?? 'unknown') : ($item->label ?? 'unknown');
        $value = (float) (is_array($item) ? ($item['revenue_total'] ?? 0) : ($item->revenue_total ?? 0));
        $count = (int) (is_array($item) ? ($item['invoice_count'] ?? 0) : ($item->invoice_count ?? 0));
        $total = max((float) ($summary['revenue_total'] ?? 0), 1);

        return [
            'key' => $key,
            'label' => $sourceLabels[$key] ?? $key,
            'value' => $value,
            'count' => $count,
            'percent' => ($value / $total) * 100,
        ];
    });
@endphp

<div class="revenue-page">
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.revenue.index') }}" class="filter-form">
            <div class="form-group">
                <label>Từ ngày</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label>Đến ngày</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label>Dịch vụ</label>
                <select name="service_id" class="form-control">
                    <option value="">Tất cả dịch vụ</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected((string) $serviceId === (string) $service->id)>
                            {{ $service->type ? $service->type . ' - ' : '' }}{{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Bác sĩ</label>
                <select name="doctor_id" class="form-control">
                    <option value="">Tất cả bác sĩ</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) $doctorId === (string) $doctor->id)>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Phương thức</label>
                <select name="payment_method" class="form-control">
                    <option value="all" @selected(($paymentMethod ?? 'all') === 'all')>Tất cả</option>
                    <option value="cash" @selected($paymentMethod === 'cash')>Tiền mặt</option>
                    <option value="bank_transfer" @selected($paymentMethod === 'bank_transfer')>Chuyển khoản</option>
                    <option value="card" @selected($paymentMethod === 'card')>Thẻ</option>
                    <option value="momo" @selected($paymentMethod === 'momo')>MoMo</option>
                    <option value="other" @selected($paymentMethod === 'other')>Khác</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nguồn khách</label>
                <select name="source" class="form-control">
                    <option value="all" @selected(($source ?? 'all') === 'all')>Tất cả</option>
                    <option value="online" @selected($source === 'online')>Đặt online</option>
                    <option value="offline" @selected($source === 'offline')>Khám trực tiếp</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="ri-filter-3-line"></i>
                Lọc báo cáo
            </button>

            <a href="{{ route('admin.revenue.index') }}" class="btn btn-secondary">
                <i class="ri-close-line"></i>
                Xóa lọc
            </a>
        </form>
    </div>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon green">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div>
                <div class="metric-label">Tổng doanh thu</div>
                <div class="metric-value">{{ number_format($summary['revenue_total'], 0, ',', '.') }}đ</div>
                <div class="metric-sub {{ !is_null($growth) ? ($growth >= 0 ? 'positive' : 'negative') : '' }}">
                    @if(!is_null($growth))
                        {{ $growth >= 0 ? 'Tăng' : 'Giảm' }} {{ number_format(abs($growth), 1, ',', '.') }}% so với kỳ trước
                    @else
                        Chưa có dữ liệu kỳ trước
                    @endif
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="ri-receipt-line"></i>
            </div>
            <div>
                <div class="metric-label">Hóa đơn đã thanh toán</div>
                <div class="metric-value">{{ number_format($summary['invoice_count'], 0, ',', '.') }}</div>
                <div class="metric-sub">Chỉ tính hóa đơn đã thanh toán</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="ri-bar-chart-box-line"></i>
            </div>
            <div>
                <div class="metric-label">Trung bình / hóa đơn</div>
                <div class="metric-value">{{ number_format($summary['average_invoice'], 0, ',', '.') }}đ</div>
                <div class="metric-sub">Dựa trên tổng tiền đã thu</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="ri-calendar-check-line"></i>
            </div>
            <div>
                <div class="metric-label">Khoảng thời gian</div>
                <div class="metric-value" style="font-size:18px;">
                    {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="ri-service-line"></i>
            </div>
            <div>
                <div class="metric-label">Doanh thu dịch vụ</div>
                <div class="metric-value">{{ number_format($summary['service_total'], 0, ',', '.') }}đ</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="ri-capsule-line"></i>
            </div>
            <div>
                <div class="metric-label">Doanh thu thuốc</div>
                <div class="metric-value">{{ number_format($summary['medicine_total'], 0, ',', '.') }}đ</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="ri-add-circle-line"></i>
            </div>
            <div>
                <div class="metric-label">Phụ phí</div>
                <div class="metric-value">{{ number_format($summary['extra_total'], 0, ',', '.') }}đ</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon red">
                <i class="ri-discount-percent-line"></i>
            </div>
            <div>
                <div class="metric-label">Giảm giá</div>
                <div class="metric-value">{{ number_format($summary['discount_total'], 0, ',', '.') }}đ</div>
            </div>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <div class="report-head">
                <div>
                    <div class="report-title">
                        <i class="ri-line-chart-line"></i>
                        Doanh thu theo ngày
                    </div>
                    <div class="report-note">Di chuột vào từng cột để xem số tiền và số hóa đơn</div>
                </div>
            </div>

            <div class="report-body">
                @if($dailyRevenue->count())
                    <div class="daily-chart-wrap">
                        <div class="chart-axis">
                            @foreach($axisLabels as $axis)
                                <span>{{ number_format($axis, 0, ',', '.') }}đ</span>
                            @endforeach
                        </div>

                        <div class="daily-chart">
                            @foreach($dailyRevenue as $day)
                                @php
                                    $revenue = (float) $day->revenue_total;
                                    $height = max(8, ($revenue / $maxDailyRevenue) * 230);
                                    $tooltip = number_format($revenue, 0, ',', '.') . 'đ · ' . $day->invoice_count . ' hóa đơn';
                                @endphp

                                <div class="daily-column">
                                    <div class="daily-bar-holder">
                                        <div
                                            class="daily-bar"
                                            style="height: {{ $height }}px;"
                                            data-value="{{ $tooltip }}"
                                        ></div>
                                    </div>
                                    <div class="daily-label">{{ \Carbon\Carbon::parse($day->report_date)->format('d/m') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="ri-bar-chart-line"></i>
                        <strong>Chưa có doanh thu trong khoảng thời gian này</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="chart-card">
            <div class="report-head">
                <div>
                    <div class="report-title">
                        <i class="ri-pie-chart-2-line"></i>
                        Cơ cấu doanh thu
                    </div>
                    <div class="report-note">Tỷ trọng dịch vụ, thuốc và phụ phí</div>
                </div>
            </div>

            <div class="report-body">
                <div class="donut-layout">
                    <div class="donut-chart" style="background: {{ $donutBackground }};">
                        <div class="donut-hole">
                            <span>Tổng cấu phần</span>
                            <strong>{{ number_format($structureTotal, 0, ',', '.') }}đ</strong>
                        </div>
                    </div>

                    <div class="donut-legend">
                        <div class="legend-item">
                            <span class="legend-dot service"></span>
                            <div>
                                <strong>Dịch vụ</strong>
                                <small>{{ number_format($serviceAmount, 0, ',', '.') }}đ · {{ number_format($servicePercent, 1, ',', '.') }}%</small>
                            </div>
                        </div>

                        <div class="legend-item">
                            <span class="legend-dot medicine"></span>
                            <div>
                                <strong>Thuốc</strong>
                                <small>{{ number_format($medicineAmount, 0, ',', '.') }}đ · {{ number_format($medicinePercent, 1, ',', '.') }}%</small>
                            </div>
                        </div>

                        <div class="legend-item">
                            <span class="legend-dot extra"></span>
                            <div>
                                <strong>Phụ phí</strong>
                                <small>{{ number_format($extraAmount, 0, ',', '.') }}đ · {{ number_format($extraPercent, 1, ',', '.') }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <div class="report-head">
                <div class="report-title">
                    <i class="ri-hospital-line"></i>
                    Top dịch vụ theo doanh thu
                </div>
            </div>

            <div class="report-body">
                <div class="rank-list">
                    @forelse($serviceRevenue as $item)
                        @php $percent = ((float) $item->revenue_total / $maxServiceRevenue) * 100; @endphp

                        <div class="rank-item">
                            <div class="rank-top">
                                <span class="rank-name">{{ $item->label }}</span>
                                <span class="rank-money">{{ number_format($item->revenue_total, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="rank-track">
                                <div class="rank-fill" style="width: {{ $percent }}%;"></div>
                            </div>
                            <div class="rank-meta">{{ $item->invoice_count }} hóa đơn</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="ri-hospital-line"></i>
                            <strong>Chưa có dữ liệu dịch vụ</strong>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="report-card">
            <div class="report-head">
                <div class="report-title">
                    <i class="ri-stethoscope-line"></i>
                    Doanh thu theo bác sĩ
                </div>
            </div>

            <div class="report-body">
                <div class="rank-list">
                    @forelse($doctorRevenue as $item)
                        @php $percent = ((float) $item->revenue_total / $maxDoctorRevenue) * 100; @endphp

                        <div class="rank-item">
                            <div class="rank-top">
                                <span class="rank-name">{{ $item->label }}</span>
                                <span class="rank-money">{{ number_format($item->revenue_total, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="rank-track">
                                <div class="rank-fill green" style="width: {{ $percent }}%;"></div>
                            </div>
                            <div class="rank-meta">{{ $item->invoice_count }} hóa đơn</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="ri-stethoscope-line"></i>
                            <strong>Chưa có dữ liệu bác sĩ</strong>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <div class="report-head">
                <div class="report-title">
                    <i class="ri-bank-card-line"></i>
                    Theo phương thức thanh toán
                </div>
            </div>

            <div class="report-body">
                <div class="breakdown-list">
                    @forelse($paymentItems as $item)
                        <div class="rank-item">
                            <div class="rank-top">
                                <span>{{ $item['label'] }}</span>
                                <span class="rank-money">{{ number_format($item['value'], 0, ',', '.') }}đ</span>
                            </div>
                            <div class="rank-track">
                                <div class="rank-fill" style="width: {{ $item['percent'] }}%;"></div>
                            </div>
                            <div class="rank-meta">{{ $item['count'] }} hóa đơn · {{ number_format($item['percent'], 1, ',', '.') }}%</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="ri-bank-card-line"></i>
                            <strong>Chưa có dữ liệu thanh toán</strong>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="report-card">
            <div class="report-head">
                <div class="report-title">
                    <i class="ri-user-location-line"></i>
                    Theo nguồn khách
                </div>
            </div>

            <div class="report-body">
                <div class="breakdown-list">
                    @forelse($sourceItems as $item)
                        <div class="rank-item">
                            <div class="rank-top">
                                <span>{{ $item['label'] }}</span>
                                <span class="rank-money">{{ number_format($item['value'], 0, ',', '.') }}đ</span>
                            </div>
                            <div class="rank-track">
                                <div class="rank-fill green" style="width: {{ $item['percent'] }}%;"></div>
                            </div>
                            <div class="rank-meta">{{ $item['count'] }} hóa đơn · {{ number_format($item['percent'], 1, ',', '.') }}%</div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="ri-user-location-line"></i>
                            <strong>Chưa có dữ liệu nguồn khách</strong>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-head">
            <div class="report-title">
                <i class="ri-file-list-3-line"></i>
                Danh sách hóa đơn đã thanh toán
            </div>
            <div class="report-note">{{ $invoices->total() }} hóa đơn</div>
        </div>

        <div class="table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Hóa đơn</th>
                        <th>Bệnh nhân</th>
                        <th>Nguồn</th>
                        <th>Dịch vụ</th>
                        <th>Bác sĩ</th>
                        <th>Phương thức</th>
                        <th>Tổng tiền</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $invoiceSource = $invoice->appointment?->source ?? $invoice->patientProfile?->source ?? 'online';
                            $invoiceSourceLabel = $sourceLabels[$invoiceSource] ?? $invoiceSource;

                            $method = $invoice->payment_method ?: 'unknown';
                            $methodLabel = $paymentLabels[$method] ?? $method;

                            $paidAt = $invoice->paid_at ?? $invoice->updated_at ?? $invoice->created_at;
                        @endphp

                        <tr>
                            <td>
                                <a href="{{ route('employees.invoices.show', $invoice) }}" style="color:#0369a1;font-weight:950;text-decoration:none;">
                                    {{ $invoice->invoice_code }}
                                </a>
                                <div class="muted">Lập: {{ optional($invoice->issued_at ?? $invoice->created_at)->format('d/m/Y H:i') }}</div>
                            </td>

                            <td>
                                <strong>{{ $invoice->display_patient_name }}</strong>
                                <div class="muted">{{ $invoice->display_patient_phone }}</div>
                            </td>

                            <td>
                                <span class="badge {{ $invoiceSource }}">
                                    {{ $invoiceSourceLabel }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $invoice->display_service_name }}</strong>
                                @if($invoice->appointment?->room)
                                    <div class="muted">{{ $invoice->appointment->room->name }}</div>
                                @endif
                            </td>

                            <td>{{ $invoice->display_doctor_name }}</td>

                            <td>
                                <span class="badge {{ $method }}">
                                    {{ $methodLabel }}
                                </span>
                            </td>

                            <td>{{ optional($paidAt)->format('d/m/Y H:i') }}</td>

                           <td class="money">
                                {{ number_format((float) (($invoice->paid_amount ?? 0) > 0 ? $invoice->paid_amount : $invoice->total_amount), 0, ',', '.') }}đ
                           </td>

                           <td>
                              <a href="{{ route('employees.invoices.show', $invoice) }}" class="detail-btn">
                                  <i class="ri-eye-line"></i>
                                  Xem chi tiết
                              </a>
                           </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="ri-file-list-3-line"></i>
                                    <strong>Không có hóa đơn phù hợp</strong>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="pagination-wrap">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection