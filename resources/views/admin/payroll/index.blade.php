@extends('layouts.admin-layout')

@section('title', 'Tính lương bác sĩ')
@section('page-title', 'Tính lương bác sĩ')
@section('page-subtitle', 'Lập phiếu lương theo ca làm việc đã duyệt và hệ số ca bệnh phức tạp')

@php
    $settingsUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.settings')
        ? route('admin.payroll.settings')
        : '#';

    $caseComplexitiesUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.case-complexities')
        ? route('admin.payroll.case-complexities')
        : '#';

    $generateUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.generate')
        ? route('admin.payroll.generate')
        : '#';

    $indexUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.index')
        ? route('admin.payroll.index')
        : '#';

    $monthlyReportUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.reports.monthly')
        ? route('admin.payroll.reports.monthly', ['month' => $month ?? now()->month, 'year' => $year ?? now()->year])
        : '#';

    $yearlyReportUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.reports.yearly')
        ? route('admin.payroll.reports.yearly', ['year' => $year ?? now()->year])
        : '#';
@endphp

@section('header-actions')
    <a href="{{ $settingsUrl }}" class="btn btn-secondary">
        <i class="ri-settings-4-line"></i>
        Cấu hình
    </a>

    <a href="{{ $caseComplexitiesUrl }}" class="btn btn-primary">
        <i class="ri-add-circle-line"></i>
        Hệ số ca bệnh
    </a>
@endsection

@section('styles')
<style>
    .payroll-page {
        display: grid;
        gap: 22px;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .metric-card {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 16px 34px rgba(15, 23, 42, .07);
        display: flex;
        gap: 14px;
        align-items: center;
        min-width: 0;
    }

    .metric-icon {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: #e0f2fe;
        color: #0284c7;
        font-size: 24px;
        flex-shrink: 0;
    }

    .metric-card span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .metric-card strong {
        color: #0f172a;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.25;
        word-break: break-word;
    }

    .layout-grid {
        display: grid;
        grid-template-columns: 420px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .panel {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 18px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, .08);
        overflow: hidden;
        min-width: 0;
    }

    .panel-head {
        padding: 20px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .panel-title {
        font-family: var(--font-title);
        font-size: 20px;
        font-weight: 850;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .panel-title i {
        color: #0ea5e9;
    }

    .panel-body {
        padding: 22px;
    }

    .form-grid {
        display: grid;
        gap: 14px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .form-group {
        display: grid;
        gap: 7px;
    }

    .label {
        font-size: 13px;
        font-weight: 850;
        color: #334155;
    }

    .input,
    .select,
    .textarea {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        padding: 12px 13px;
        background: #fff;
        color: #0f172a;
        font-weight: 650;
        outline: none;
        min-height: 46px;
    }

    .input:focus,
    .select:focus,
    .textarea:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .textarea {
        min-height: 82px;
        resize: vertical;
    }

    .filter-box {
        display: grid;
        grid-template-columns: 120px 140px minmax(220px, 1fr) auto;
        gap: 12px;
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 18px;
    }

    .payroll-list {
        display: grid;
        gap: 14px;
    }

    .payroll-card {
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        background: #f8fafc;
        padding: 18px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 16px;
        align-items: center;
        min-width: 0;
    }

    .payroll-main {
        min-width: 0;
    }

    .payroll-code {
        color: #0284c7;
        font-weight: 950;
        font-size: 14px;
        margin-bottom: 5px;
        word-break: break-word;
    }

    .doctor-name {
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
        margin-bottom: 8px;
        word-break: break-word;
    }

    .meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.45;
    }

    .meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .amount-box {
        text-align: right;
        display: grid;
        gap: 10px;
        justify-items: end;
    }

    .amount {
        font-size: 22px;
        font-weight: 950;
        color: #0f172a;
        white-space: nowrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 34px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 850;
        white-space: nowrap;
    }

    .badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.secondary {
        background: #f1f5f9;
        color: #475569;
    }

    .empty {
        padding: 42px;
        text-align: center;
        color: #64748b;
        font-weight: 750;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
    }

    .report-actions {
        display: grid;
        gap: 10px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed #cbd5e1;
    }

    .report-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .btn-report-full {
    width: 100%;
    justify-content: center;
}

    .hint-box {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        color: #075985;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
        margin-bottom: 16px;
    }

    .error-list {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 16px;
        padding: 16px 18px;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .error-list ul {
        margin: 8px 0 0 20px;
    }

    @media (max-width: 1200px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .layout-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .metrics-grid,
        .form-row,
        .filter-box,
        .report-mini-grid {
            grid-template-columns: 1fr;
        }

        .payroll-card {
            grid-template-columns: 1fr;
        }

        .amount-box {
            text-align: left;
            justify-items: start;
        }

        .amount {
            white-space: normal;
        }
    }
</style>
@endsection

@section('content')
<div class="payroll-page">
    @if($errors->any())
        <div class="error-list">
            <div><i class="ri-error-warning-line"></i> Dữ liệu chưa hợp lệ:</div>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <i class="ri-file-list-3-line"></i>
            </div>
            <div>
                <span>Phiếu lương</span>
                <strong>{{ number_format((float) ($summary['total_payrolls'] ?? 0), 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="ri-money-dollar-circle-line"></i>
            </div>
            <div>
                <span>Tổng trước điều chỉnh</span>
                <strong>{{ number_format((float) ($summary['gross_amount'] ?? 0), 0, ',', '.') }} đ</strong>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="ri-wallet-3-line"></i>
            </div>
            <div>
                <span>Thực nhận</span>
                <strong>{{ number_format((float) ($summary['net_amount'] ?? 0), 0, ',', '.') }} đ</strong>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon">
                <i class="ri-checkbox-circle-line"></i>
            </div>
            <div>
                <span>Đã thanh toán</span>
                <strong>{{ number_format((float) ($summary['paid_amount'] ?? 0), 0, ',', '.') }} đ</strong>
            </div>
        </div>
    </div>

    <div class="layout-grid">
        <section class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ri-calculator-line"></i>
                    Lập phiếu lương
                </div>
            </div>

            <div class="panel-body">
                <div class="hint-box">
                    Công thức hiện tại là lương theo ca/giờ quy đổi:
                    số giờ quy đổi = số giờ ca x (hệ số ca + tổng hệ số bệnh nhân phức tạp).
                </div>

                <form method="POST" action="{{ $generateUrl }}" class="form-grid">
                    @csrf

                    <div class="form-group">
                        <label class="label">Bác sĩ</label>
                        <select name="doctor_id" class="select" required>
                            <option value="">-- Chọn bác sĩ --</option>
                            @foreach(($doctors ?? []) as $doctor)
                                <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>
                                    {{ $doctor->name }}
                                    @if(!empty($doctor->degree))
                                        - {{ $doctor->degree }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="label">Tháng</label>
                            <input type="number"
                                   name="salary_month"
                                   min="1"
                                   max="12"
                                   class="input"
                                   value="{{ old('salary_month', $month ?? now()->month) }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="label">Năm</label>
                            <input type="number"
                                   name="salary_year"
                                   min="2000"
                                   max="2100"
                                   class="input"
                                   value="{{ old('salary_year', $year ?? now()->year) }}"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="label">Thưởng thêm</label>
                            <input type="number"
                                   name="bonus_amount"
                                   min="0"
                                   step="1000"
                                   class="input"
                                   value="{{ old('bonus_amount', 0) }}">
                        </div>

                        <div class="form-group">
                            <label class="label">Khấu trừ</label>
                            <input type="number"
                                   name="deduction_amount"
                                   min="0"
                                   step="1000"
                                   class="input"
                                   value="{{ old('deduction_amount', 0) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Ghi chú</label>
                        <textarea name="notes"
                                  class="textarea"
                                  placeholder="Ghi chú khi lập phiếu lương...">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" @if($generateUrl === '#') disabled @endif>
                        <i class="ri-play-circle-line"></i>
                        Lập / tính lại phiếu lương
                    </button>
                </form>

                <div class="report-actions">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.payroll.case-complexities') ? route('admin.payroll.case-complexities', ['month' => $month ?? now()->month, 'year' => $year ?? now()->year]) : '#' }}"
                       class="btn btn-secondary">
                        <i class="ri-file-add-line"></i>
                        Nhập hệ số ca bệnh
                    </a>

                    <a href="{{ $settingsUrl }}" class="btn btn-secondary">
                        <i class="ri-settings-4-line"></i>
                        Cấu hình hệ số
                    </a>

                    <a href="{{ $monthlyReportUrl }}" class="btn btn-secondary btn-report-full">
    <i class="ri-bar-chart-box-line"></i>
    Báo cáo
</a>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ri-file-list-2-line"></i>
                    Danh sách phiếu lương
                </div>
            </div>

            <div class="panel-body">
                <form method="GET" action="{{ $indexUrl }}" class="filter-box">
                    <input type="number"
                           name="month"
                           min="1"
                           max="12"
                           class="input"
                           value="{{ request('month', $month ?? now()->month) }}">

                    <input type="number"
                           name="year"
                           min="2000"
                           max="2100"
                           class="input"
                           value="{{ request('year', $year ?? now()->year) }}">

                    <select name="doctor_id" class="select">
                        <option value="">Tất cả bác sĩ</option>
                        @foreach(($doctors ?? []) as $doctor)
                            <option value="{{ $doctor->id }}" @selected((string) ($doctorId ?? '') === (string) $doctor->id)>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-3-line"></i>
                        Lọc
                    </button>
                </form>

                <div class="payroll-list">
                    @forelse(($payrolls ?? []) as $payroll)
                        @php
                            $status = $payroll->status ?? 'draft';

                            $statusLabels = [
                                'draft' => 'Nháp',
                                'approved' => 'Đã duyệt',
                                'paid' => 'Đã thanh toán',
                                'cancelled' => 'Đã hủy',
                            ];

                            $statusClasses = [
                                'draft' => 'warning',
                                'approved' => 'info',
                                'paid' => 'success',
                                'cancelled' => 'danger',
                            ];

                            $statusLabel = $payroll->status_label ?? ($statusLabels[$status] ?? 'Không rõ');
                            $statusClass = $payroll->status_class ?? ($statusClasses[$status] ?? 'secondary');

                            $showUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.show')
                                ? route('admin.payroll.show', $payroll)
                                : '#';
                        @endphp

                        <article class="payroll-card">
                            <div class="payroll-main">
                                <div class="payroll-code">
                                    {{ $payroll->payroll_code ?? ('PHIEU-LUONG-' . $payroll->id) }}
                                </div>

                                <div class="doctor-name">
                                    {{ $payroll->doctor?->name ?? 'Bác sĩ không xác định' }}
                                </div>

                                <div class="meta">
                                    <span>
                                        <i class="ri-calendar-line"></i>
                                        Tháng {{ $payroll->salary_month }}/{{ $payroll->salary_year }}
                                    </span>

                                    <span>
                                        <i class="ri-time-line"></i>
                                        {{ number_format((float) ($payroll->total_work_hours ?? 0), 2) }} giờ
                                    </span>

                                    <span>
                                        <i class="ri-loop-right-line"></i>
                                        {{ number_format((float) ($payroll->total_converted_hours ?? 0), 2) }} giờ quy đổi
                                    </span>

                                    <span>
                                        <i class="ri-percent-line"></i>
                                        HS bác sĩ {{ number_format((float) ($payroll->doctor_coefficient ?? 0), 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="amount-box">
                                <div class="amount">
                                    {{ number_format((float) ($payroll->net_amount ?? 0), 0, ',', '.') }} đ
                                </div>

                                <div class="badge {{ $statusClass }}">
                                    <i class="ri-circle-fill" style="font-size:8px;"></i>
                                    {{ $statusLabel }}
                                </div>

                                <a href="{{ $showUrl }}" class="btn btn-secondary btn-sm">
                                    <i class="ri-eye-line"></i>
                                    Chi tiết
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="empty">
                            Chưa có phiếu lương trong kỳ này. Hãy chọn bác sĩ và lập phiếu lương.
                        </div>
                    @endforelse
                </div>

                @if(isset($payrolls) && method_exists($payrolls, 'links'))
                    <div style="margin-top:18px;">
                        {{ $payrolls->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection