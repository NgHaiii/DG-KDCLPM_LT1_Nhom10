@extends('layouts.admin-layout')

@section('title', $report['title'] ?? 'Báo cáo lương')
@section('page-title', $report['title'] ?? 'Báo cáo lương bác sĩ')
@section('page-subtitle', 'Theo dõi tiền lương theo tháng, theo năm và theo từng bác sĩ')

@php
    $mode = $report['mode'] ?? 'monthly';
    $year = $report['year'] ?? now()->year;
    $month = $report['month'] ?? now()->month;
    $doctors = \App\Models\Employee::where('is_doctor', 1)->orderBy('name')->get();

    $fmt = fn ($n) => number_format((float) ($n ?? 0), 0, ',', '.') . ' đ';
    $num = fn ($n) => number_format((float) ($n ?? 0), 2, ',', '.');

    $isMonthTab = $mode === 'monthly';
    $isYearTab = in_array($mode, ['yearly', 'doctor-yearly'], true);

    $featureName = match ($mode) {
        'yearly' => 'Báo cáo năm',
        'doctor-yearly' => 'Báo cáo cá nhân theo năm',
        default => 'Báo cáo tháng',
    };

    $featureTitle = match ($mode) {
        'yearly' => 'Báo cáo tiền lương tất cả bác sĩ trong 1 năm',
        'doctor-yearly' => 'Báo cáo tiền lương của một bác sĩ trong 1 năm',
        default => 'Báo cáo tiền lương tất cả bác sĩ trong 1 tháng',
    };

    $featureDescription = match ($mode) {
        'yearly' => 'Tổng hợp lương theo từng bác sĩ trong cả năm, phục vụ đánh giá tổng chi phí nhân sự bác sĩ.',
        'doctor-yearly' => 'Theo dõi 12 tháng lương của một bác sĩ cụ thể, phục vụ đối soát cá nhân theo năm.',
        default => 'Liệt kê toàn bộ phiếu lương của các bác sĩ trong tháng được chọn.',
    };

    $modeLabel = $featureName . ' - ' . $featureTitle;

    $periodLabel = match ($mode) {
        'yearly' => 'Năm ' . $year,
        'doctor-yearly' => 'Năm ' . $year,
        default => 'Tháng ' . $month . '/' . $year,
    };
@endphp

@section('header-actions')
    <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>

    <button type="button" class="btn btn-primary" onclick="exportSalaryReportExcel()">
        <i class="ri-file-excel-2-line"></i>
        Xuất Excel
    </button>
@endsection

@section('styles')
<style>
    .report-wrap {
        display: grid;
        gap: 18px;
    }

    .panel {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .07);
        overflow: hidden;
    }

    .panel-head {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
    }

    .panel-title {
        font-size: 20px;
        font-weight: 900;
        color: #0f172a;
        display: flex;
        gap: 9px;
        align-items: center;
    }

    .panel-title i {
        color: #0ea5e9;
    }

    .panel-body {
        padding: 18px 20px;
    }

    .main-tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .main-tab {
        min-height: 76px;
        border: 1px solid #dbe3ef;
        background: #f8fafc;
        border-radius: 14px;
        padding: 14px 16px;
        text-decoration: none;
        color: #0f172a;
        display: grid;
        gap: 5px;
    }

    .main-tab.active {
        border-color: #38bdf8;
        background: #f0f9ff;
        box-shadow: 0 10px 22px rgba(14, 165, 233, .12);
    }

    .main-tab strong {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 950;
    }

    .main-tab i {
        color: #0ea5e9;
        font-size: 19px;
    }

    .main-tab span {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.35;
    }

    .feature-banner {
        border: 1px solid #bae6fd;
        background: linear-gradient(135deg, #f0f9ff, #ffffff);
        border-radius: 14px;
        padding: 14px 16px;
        display: grid;
        gap: 6px;
        margin-bottom: 14px;
    }

    .feature-banner-head {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .feature-badge {
        width: fit-content;
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .feature-banner-title {
        color: #0f172a;
        font-size: 17px;
        font-weight: 950;
    }

    .feature-banner-desc {
        color: #475569;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.5;
    }

    .filter-box {
        display: grid;
        gap: 12px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 1.3fr .8fr .8fr auto;
        gap: 10px;
        align-items: end;
    }

    .filter-row.year {
        grid-template-columns: 1fr .7fr auto 1fr .7fr auto;
    }

    .quick-filter {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(190px, .45fr);
        gap: 10px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
    }

    .form-group {
        display: grid;
        gap: 6px;
    }

    .form-label {
        font-size: 13px;
        font-weight: 850;
        color: #334155;
    }

    .form-control {
        height: 42px;
        border: 1px solid #dbe3ef;
        border-radius: 11px;
        padding: 0 12px;
        font-weight: 750;
        color: #0f172a;
        background: #fff;
        width: 100%;
    }

    .btn {
        height: 42px;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid #dbe3ef;
        cursor: pointer;
        background: #fff;
        color: #0f172a;
        white-space: nowrap;
    }

    .btn-primary {
        background: #0ea5e9;
        color: #fff;
        border-color: #0ea5e9;
    }

    .btn-secondary:hover,
    .btn:hover {
        background: #f0f9ff;
        color: #0284c7;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .metric {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
    }

    .metric span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .metric strong {
        color: #0f172a;
        font-size: 21px;
        font-weight: 950;
        line-height: 1.25;
    }

    .table-scroll {
        overflow: auto;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .salary-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
        background: #fff;
    }

    .salary-table th {
        background: #f1f5f9;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .02em;
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #dbe3ef;
        white-space: nowrap;
    }

    .salary-table td {
        padding: 12px;
        border-bottom: 1px solid #edf2f7;
        color: #0f172a;
        font-weight: 700;
        vertical-align: middle;
    }

    .salary-table tr:hover td {
        background: #f8fafc;
    }

    .salary-table tr.is-hidden {
        display: none;
    }

    .money {
        text-align: right;
        white-space: nowrap;
    }

    .muted {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .status {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status.draft {
        background: #f1f5f9;
        color: #475569;
    }

    .status.approved {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status.paid {
        background: #dcfce7;
        color: #047857;
    }

    .status.cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .empty {
        padding: 28px;
        text-align: center;
        color: #64748b;
        font-weight: 800;
        border: 1px dashed #bae6fd;
        border-radius: 14px;
        background: #f8fafc;
    }

    @media(max-width: 1280px) {
        .filter-row.year {
            grid-template-columns: 1fr .7fr auto;
        }
    }

    @media(max-width: 1100px) {
        .main-tabs,
        .summary-grid,
        .quick-filter,
        .filter-row,
        .filter-row.year {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 760px) {
        .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="report-wrap">
    <div class="panel">
        <div class="panel-head">
            <div>
                <div class="panel-title">
                    <i class="ri-bar-chart-box-line"></i>
                    Báo cáo lương bác sĩ
                </div>
                <div class="muted">Đang xem: {{ $modeLabel }}</div>
            </div>
        </div>

        <div class="panel-body">
            <div class="main-tabs">
                <a href="{{ route('admin.payroll.reports.monthly', ['month' => $month, 'year' => $year]) }}"
                   class="main-tab {{ $isMonthTab ? 'active' : '' }}">
                    <strong>
                        <i class="ri-calendar-check-line"></i>
                        Báo cáo tháng
                    </strong>
                    <span>Tổng hợp lương tất cả bác sĩ trong một tháng. Có thể tìm nhanh theo bác sĩ hoặc mã phiếu.</span>
                </a>

                <a href="{{ route('admin.payroll.reports.yearly', ['year' => $year]) }}"
                   class="main-tab {{ $isYearTab ? 'active' : '' }}">
                    <strong>
                        <i class="ri-line-chart-line"></i>
                        Báo cáo năm
                    </strong>
                    <span>Xem báo cáo tất cả bác sĩ trong năm hoặc chọn một bác sĩ để xem chi tiết 12 tháng.</span>
                </a>
            </div>

            <div class="feature-banner">
                <div class="feature-banner-head">
                    <span class="feature-badge">{{ $featureName }}</span>
                    <div class="feature-banner-title">{{ $featureTitle }}</div>
                </div>
                <div class="feature-banner-desc">{{ $featureDescription }}</div>
            </div>

            <div class="filter-box">
                @if($isMonthTab)
                    <form method="GET" action="{{ route('admin.payroll.reports.monthly') }}" class="filter-row">
                        <div class="form-group">
                            <label class="form-label">Phạm vi báo cáo</label>
                            <input type="text" class="form-control" value="Tất cả bác sĩ trong tháng" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tháng</label>
                            <input type="number" name="month" min="1" max="12" class="form-control" value="{{ $month }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Năm</label>
                            <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}">
                        </div>

                        <button class="btn btn-primary">
                            <i class="ri-search-line"></i>
                            Xem báo cáo
                        </button>
                    </form>
                @else
                    <div class="filter-row year">
                        <form method="GET" action="{{ route('admin.payroll.reports.yearly') }}" style="display: contents;">
                            <div class="form-group">
                                <label class="form-label">Phạm vi báo cáo</label>
                                <input type="text" class="form-control" value="Tất cả bác sĩ trong năm" disabled>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Năm</label>
                                <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}">
                            </div>

                            <button class="btn btn-primary">
                                <i class="ri-bar-chart-2-line"></i>
                                Xem tất cả
                            </button>
                        </form>

                        <form method="GET" action="{{ route('admin.payroll.reports.doctor-yearly') }}" style="display: contents;">
                            <div class="form-group">
                                <label class="form-label">Báo cáo cá nhân</label>
                                <select name="doctor_id" class="form-control" required>
                                    <option value="">-- Chọn bác sĩ --</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected(($report['doctor']->id ?? null) == $doctor->id)>
                                            {{ $doctor->name }}{{ $doctor->degree ? ' - ' . $doctor->degree : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Năm</label>
                                <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}">
                            </div>

                            <button class="btn btn-primary">
                                <i class="ri-user-search-line"></i>
                                Xem cá nhân
                            </button>
                        </form>
                    </div>
                @endif

                <div class="quick-filter">
                    <div class="form-group">
                        <label class="form-label">Tìm nhanh trong bảng</label>
                        <input type="text"
                               id="quickSearch"
                               class="form-control"
                               placeholder="Nhập tên bác sĩ, mã phiếu, tháng, trạng thái...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lọc theo trạng thái</label>
                        <select id="statusFilter" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="nháp">Nháp</option>
                            <option value="đã duyệt">Đã duyệt</option>
                            <option value="đã thanh toán">Đã thanh toán</option>
                            <option value="đã hủy">Đã hủy</option>
                            <option value="paid">Paid</option>
                            <option value="approved">Approved</option>
                            <option value="draft">Draft</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="metric">
            <span>Chức năng</span>
            <strong>{{ $featureName }}</strong>
        </div>

        @if($mode === 'doctor-yearly')
            <div class="metric">
                <span>Bác sĩ</span>
                <strong>{{ $report['doctor']->name ?? 'Không rõ' }}</strong>
            </div>
        @else
            <div class="metric">
                <span>Số bác sĩ / phiếu</span>
                <strong>{{ $report['doctor_count'] ?? ($report['payrolls']->count() ?? 0) }}</strong>
            </div>
        @endif

        <div class="metric">
            <span>Kỳ báo cáo</span>
            <strong>{{ $periodLabel }}</strong>
        </div>

        <div class="metric">
            <span>Tổng lương</span>
            <strong>{{ $fmt($report['net_amount'] ?? 0) }}</strong>
        </div>
    </div>

    <div class="summary-grid">
        @if($mode === 'doctor-yearly')
            <div class="metric">
                <span>Số tháng có phiếu</span>
                <strong>{{ ($report['payrolls'] ?? collect())->count() }}</strong>
            </div>
        @else
            <div class="metric">
                <span>Loại báo cáo</span>
                <strong>{{ $mode === 'yearly' ? 'Năm' : 'Tháng' }}</strong>
            </div>
        @endif

        <div class="metric">
            <span>Tổng giờ làm</span>
            <strong>{{ $num($report['total_work_hours'] ?? 0) }}</strong>
        </div>

        <div class="metric">
            <span>Giờ quy đổi</span>
            <strong>{{ $num($report['total_converted_hours'] ?? 0) }}</strong>
        </div>

        <div class="metric">
            <span>Dữ liệu</span>
            <strong>
                @if($mode === 'yearly')
                    {{ ($report['doctor_reports'] ?? collect())->count() }} bác sĩ
                @else
                    {{ ($report['payrolls'] ?? collect())->count() }} phiếu
                @endif
            </strong>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <div class="panel-title">
                    <i class="ri-table-line"></i>
                    {{ $featureTitle }}
                </div>
                <div class="muted">{{ $periodLabel }}</div>
            </div>
        </div>

        <div class="panel-body">
            @if($mode === 'yearly')
                @php $rows = $report['doctor_reports'] ?? collect(); @endphp

                @if($rows->count())
                    <div class="table-scroll">
                        <table id="salaryReportTable" class="salary-table">
                            <thead>
                                <tr>
                                    <th>Bác sĩ</th>
                                    <th>Số tháng có phiếu</th>
                                    <th>Giờ làm</th>
                                    <th>Giờ quy đổi</th>
                                    <th class="money">Lương gộp</th>
                                    <th class="money">Thực nhận</th>
                                    <th class="money">Đã thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row['doctor']->name ?? 'Không rõ' }}</strong>
                                            <div class="muted">{{ $row['doctor']->degree ?? 'Chưa có học vị' }}</div>
                                        </td>
                                        <td>{{ $row['months_count'] ?? 0 }}</td>
                                        <td>{{ $num($row['total_work_hours'] ?? 0) }}</td>
                                        <td>{{ $num($row['total_converted_hours'] ?? 0) }}</td>
                                        <td class="money">{{ $fmt($row['gross_amount'] ?? 0) }}</td>
                                        <td class="money">{{ $fmt($row['net_amount'] ?? 0) }}</td>
                                        <td class="money">{{ $fmt($row['paid_amount'] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty">Chưa có dữ liệu lương trong năm này.</div>
                @endif
            @elseif($mode === 'doctor-yearly')
                @php $rows = $report['payrolls'] ?? collect(); @endphp

                @if($rows->count())
                    <div class="table-scroll">
                        <table id="salaryReportTable" class="salary-table">
                            <thead>
                                <tr>
                                    <th>Tháng</th>
                                    <th>Mã phiếu</th>
                                    <th>Giờ làm</th>
                                    <th>Giờ quy đổi</th>
                                    <th>HS bác sĩ</th>
                                    <th class="money">Lương gộp</th>
                                    <th class="money">Thưởng</th>
                                    <th class="money">Khấu trừ</th>
                                    <th class="money">Thực nhận</th>
                                    <th>Trạng thái</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $payroll)
                                    <tr>
                                        <td>Tháng {{ $payroll->salary_month }}</td>
                                        <td><strong>{{ $payroll->payroll_code }}</strong></td>
                                        <td>{{ $num($payroll->total_work_hours) }}</td>
                                        <td>{{ $num($payroll->total_converted_hours) }}</td>
                                        <td>{{ $num($payroll->doctor_coefficient) }}</td>
                                        <td class="money">{{ $fmt($payroll->gross_amount) }}</td>
                                        <td class="money">{{ $fmt($payroll->bonus_amount) }}</td>
                                        <td class="money">{{ $fmt($payroll->deduction_amount) }}</td>
                                        <td class="money">{{ $fmt($payroll->net_amount) }}</td>
                                        <td>
                                            <span class="status {{ $payroll->status }}">
                                                {{ $payroll->status_label ?? $payroll->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn" href="{{ route('admin.payroll.show', $payroll) }}">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty">Bác sĩ này chưa có phiếu lương trong năm được chọn.</div>
                @endif
            @else
                @php $rows = $report['payrolls'] ?? collect(); @endphp

                @if($rows->count())
                    <div class="table-scroll">
                        <table id="salaryReportTable" class="salary-table">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Bác sĩ</th>
                                    <th>Kỳ lương</th>
                                    <th>Giờ làm</th>
                                    <th>Giờ quy đổi</th>
                                    <th>HS bác sĩ</th>
                                    <th class="money">Lương gộp</th>
                                    <th class="money">Thực nhận</th>
                                    <th>Trạng thái</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $payroll)
                                    <tr>
                                        <td><strong>{{ $payroll->payroll_code }}</strong></td>
                                        <td>
                                            <strong>{{ $payroll->doctor?->name ?? 'Không rõ' }}</strong>
                                            <div class="muted">{{ $payroll->doctor?->degree ?? 'Chưa có học vị' }}</div>
                                        </td>
                                        <td>{{ $payroll->salary_month }}/{{ $payroll->salary_year }}</td>
                                        <td>{{ $num($payroll->total_work_hours) }}</td>
                                        <td>{{ $num($payroll->total_converted_hours) }}</td>
                                        <td>{{ $num($payroll->doctor_coefficient) }}</td>
                                        <td class="money">{{ $fmt($payroll->gross_amount) }}</td>
                                        <td class="money">{{ $fmt($payroll->net_amount) }}</td>
                                        <td>
                                            <span class="status {{ $payroll->status }}">
                                                {{ $payroll->status_label ?? $payroll->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn" href="{{ route('admin.payroll.show', $payroll) }}">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty">Chưa có phiếu lương phù hợp.</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const salaryReportExport = {
    title: @json($featureTitle),
    modeLabel: @json($modeLabel),
    periodLabel: @json($periodLabel),
    generatedAt: @json(now()->format('d/m/Y H:i')),
    totalWorkHours: @json($num($report['total_work_hours'] ?? 0)),
    netAmount: @json($fmt($report['net_amount'] ?? 0)),
    fileName: @json('bao-cao-luong-' . $mode . '-' . $periodLabel),
};

function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function applyTableFilters() {
    const table = document.getElementById('salaryReportTable');
    if (!table) return;

    const keyword = normalizeText(document.getElementById('quickSearch')?.value || '');
    const status = normalizeText(document.getElementById('statusFilter')?.value || '');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = normalizeText(row.innerText);
        const matchKeyword = keyword === '' || text.includes(keyword);
        const matchStatus = status === '' || text.includes(status);

        row.classList.toggle('is-hidden', !(matchKeyword && matchStatus));
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('quickSearch')?.addEventListener('input', applyTableFilters);
    document.getElementById('statusFilter')?.addEventListener('change', applyTableFilters);
});

function xmlEscape(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function parseMoneyValue(text) {
    const normalized = String(text || '')
        .replace(/[^\d,-]/g, '')
        .replace(/\./g, '')
        .replace(',', '.');

    const number = Number(normalized);
    return Number.isFinite(number) ? number : 0;
}

function parseNumberValue(text) {
    const normalized = String(text || '')
        .replace(/[^\d,-]/g, '')
        .replace(',', '.');

    const number = Number(normalized);
    return Number.isFinite(number) ? number : 0;
}

function excelCell(value, style = 'Text', type = 'String') {
    if (type === 'Number') {
        return `<Cell ss:StyleID="${style}"><Data ss:Type="Number">${Number(value || 0)}</Data></Cell>`;
    }

    return `<Cell ss:StyleID="${style}"><Data ss:Type="String">${xmlEscape(value)}</Data></Cell>`;
}

function emptyCell(count = 1) {
    return '<Cell></Cell>'.repeat(count);
}

function exportSalaryReportExcel() {
    const table = document.getElementById('salaryReportTable');

    if (!table) {
        alert('Không có dữ liệu để xuất.');
        return;
    }

    const info = salaryReportExport;
    const visibleHeaders = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.innerText.replace(/\s+/g, ' ').trim())
        .filter(text => text !== 'Chi tiết');

    const bodyRows = Array.from(table.querySelectorAll('tbody tr:not(.is-hidden)')).map((tr, rowIndex) => {
        const cells = Array.from(tr.querySelectorAll('td'));
        const rowStyle = rowIndex % 2 === 0 ? 'Normal' : 'Alt';

        const exportedCells = cells.slice(0, visibleHeaders.length).map((td) => {
            const text = td.innerText.replace(/\s+/g, ' ').trim();
            const isMoney = td.classList.contains('money');
            const isNumeric = /^-?\d+([,.]\d+)?$/.test(text);

            if (isMoney) {
                return excelCell(parseMoneyValue(text), rowStyle + 'Currency', 'Number');
            }

            if (isNumeric) {
                return excelCell(parseNumberValue(text), rowStyle + 'Number', 'Number');
            }

            return excelCell(text, rowStyle);
        });

        return `<Row ss:Height="25">${exportedCells.join('')}</Row>`;
    });

    const headerRow = `
        <Row ss:Height="28">
            ${visibleHeaders.map((header, index) => {
                const isLast = index === visibleHeaders.length - 1;
                return excelCell(header, isLast ? 'HeaderRight' : 'Header');
            }).join('')}
        </Row>
    `;

    const rows = [];

    rows.push(`
        <Row ss:Height="34">
            <Cell ss:MergeAcross="${Math.max(visibleHeaders.length - 1, 1)}" ss:StyleID="Title">
                <Data ss:Type="String">${xmlEscape(info.title)}</Data>
            </Cell>
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            ${excelCell('Loại báo cáo', 'Label')}
            ${excelCell(info.modeLabel, 'Value')}
            ${excelCell('Kỳ báo cáo', 'Label')}
            ${excelCell(info.periodLabel, 'Value')}
            ${excelCell('Ngày xuất', 'Label')}
            ${excelCell(info.generatedAt, 'Value')}
            ${emptyCell(Math.max(visibleHeaders.length - 6, 0))}
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            ${excelCell('Tổng giờ làm', 'Label')}
            ${excelCell(info.totalWorkHours, 'Value')}
            ${excelCell('Tổng lương', 'Label')}
            ${excelCell(info.netAmount, 'Value')}
            ${emptyCell(Math.max(visibleHeaders.length - 4, 0))}
        </Row>
    `);

    rows.push('<Row ss:Height="10"></Row>');
    rows.push(headerRow);

    if (bodyRows.length) {
        rows.push(...bodyRows);
    } else {
        rows.push(`
            <Row ss:Height="28">
                <Cell ss:MergeAcross="${Math.max(visibleHeaders.length - 1, 1)}" ss:StyleID="Empty">
                    <Data ss:Type="String">Không có dữ liệu để xuất.</Data>
                </Cell>
            </Row>
        `);
    }

    const workbook =
        '<' + '?xml version="1.0" encoding="UTF-8"?>\n' +
        '<' + '?mso-application progid="Excel.Sheet"?>\n' +
        String.raw`<Workbook
    xmlns="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

    <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
        <Author>DentalCare</Author>
        <Company>DentalCare</Company>
    </DocumentProperties>

    <Styles>
        <Style ss:ID="Default" ss:Name="Normal">
            <Alignment ss:Vertical="Center"/>
            <Font ss:FontName="Calibri" ss:Size="11"/>
        </Style>

        <Style ss:ID="Title">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Font ss:FontName="Calibri" ss:Size="18" ss:Bold="1" ss:Color="#0F172A"/>
            <Interior ss:Color="#E0F2FE" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#38BDF8"/>
            </Borders>
        </Style>

        <Style ss:ID="Label">
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#334155"/>
            <Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="Value">
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#0F172A"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="Header">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#0284C7" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
            </Borders>
        </Style>

        <Style ss:ID="HeaderRight" ss:Parent="Header">
            <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
        </Style>

        <Style ss:ID="Normal">
            <Alignment ss:Vertical="Center" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="Alt">
            <Alignment ss:Vertical="Center" ss:WrapText="1"/>
            <Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="NormalNumber" ss:Parent="Normal">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <NumberFormat ss:Format="0.00"/>
        </Style>

        <Style ss:ID="AltNumber" ss:Parent="Alt">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <NumberFormat ss:Format="0.00"/>
        </Style>

        <Style ss:ID="NormalCurrency" ss:Parent="Normal">
            <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="AltCurrency" ss:Parent="Alt">
            <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="Empty">
            <Alignment ss:Horizontal="Center"/>
            <Font ss:FontName="Calibri" ss:Italic="1" ss:Color="#64748B"/>
        </Style>
    </Styles>

    <Worksheet ss:Name="Bao cao luong">
        <Table>
            <Column ss:Width="150"/>
            <Column ss:Width="165"/>
            <Column ss:Width="105"/>
            <Column ss:Width="105"/>
            <Column ss:Width="105"/>
            <Column ss:Width="130"/>
            <Column ss:Width="130"/>
            <Column ss:Width="130"/>
            <Column ss:Width="130"/>
            <Column ss:Width="120"/>
            ${rows.join('')}
        </Table>

        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
            <FreezePanes/>
            <FrozenNoSplit/>
            <SplitHorizontal>5</SplitHorizontal>
            <TopRowBottomPane>5</TopRowBottomPane>
            <ActivePane>2</ActivePane>
            <ProtectObjects>False</ProtectObjects>
            <ProtectScenarios>False</ProtectScenarios>
        </WorksheetOptions>
    </Worksheet>
</Workbook>`;

    const safeName = String(info.fileName || 'bao-cao-luong')
        .replace(/[\\/:*?"<>|]/g, '-')
        .replace(/\s+/g, '-');

    const blob = new Blob(["\uFEFF" + workbook], {
        type: 'application/vnd.ms-excel;charset=utf-8;'
    });

    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${safeName}-${new Date().toISOString().slice(0, 10)}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}
</script>
@endsection