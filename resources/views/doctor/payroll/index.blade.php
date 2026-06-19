@extends('layouts.doctor-layout')

@section('title', 'Bảng lương của tôi')
@section('page-title', 'Bảng lương của tôi')
@section('page-subtitle', 'Thống kê chi tiết thu nhập, ca làm việc và hệ số ca bệnh đã được ban quản trị xác nhận')

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

    .filter-box {
        display: grid;
        grid-template-columns: 150px 150px auto;
        gap: 12px;
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 18px;
        align-items: center;
    }

    .input,
    .btn {
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

    .input:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        font-weight: 900;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-primary {
        background: #0ea5e9;
        color: #fff;
        border-color: #0ea5e9;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
    }

    .btn-primary:hover {
        background: #0284c7;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.45);
    }

    .btn-secondary {
        background: #fff;
        color: #0f172a;
        border-color: #dbe3ef;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .btn-sm {
        min-height: 36px;
        padding: 7px 14px;
        font-size: 13px;
        border-radius: 10px;
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

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
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

    @media (max-width: 1024px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .metrics-grid,
        .filter-box {
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
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">
                <i class="ri-file-list-3-line"></i>
            </div>
            <div>
                <span>Phiếu lương đã nhận</span>
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
                <span>Tổng thực nhận</span>
                <strong>{{ number_format((float) ($summary['net_amount'] ?? 0), 0, ',', '.') }} đ</strong>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background: #dcfce7; color: #16a34a;">
                <i class="ri-checkbox-circle-line"></i>
            </div>
            <div>
                <span>Đã thanh toán</span>
                <strong>{{ number_format((float) ($summary['paid_amount'] ?? 0), 0, ',', '.') }} đ</strong>
            </div>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <div class="panel-title">
                <i class="ri-file-list-2-line"></i>
                Danh sách phiếu lương
            </div>
        </div>

        <div class="panel-body">
            <form method="GET" action="{{ route('doctor.payroll.index') }}" class="filter-box">
                <div>
                    <input type="number"
                           name="month"
                           min="1"
                           max="12"
                           class="input"
                           placeholder="Tháng"
                           value="{{ request('month', $month) }}">
                </div>

                <div>
                    <input type="number"
                           name="year"
                           min="2000"
                           max="2100"
                           class="input"
                           placeholder="Năm"
                           value="{{ request('year', $year) }}">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="max-width: 150px;">
                        <i class="ri-filter-3-line"></i>
                        Lọc
                    </button>
                </div>
            </form>

            <div class="payroll-list">
                @forelse($payrolls as $payroll)
                    @php
                        $status = $payroll->status ?? 'approved';
                        $statusClass = $status === 'paid' ? 'success' : 'info';
                    @endphp

                    <article class="payroll-card">
                        <div class="payroll-main">
                            <div class="payroll-code">
                                {{ $payroll->payroll_code }}
                            </div>

                            <div class="doctor-name">
                                {{ $payroll->doctor?->name }}
                            </div>

                            <div class="meta">
                                <span>
                                    <i class="ri-calendar-line"></i>
                                    Tháng {{ $payroll->salary_month }}/{{ $payroll->salary_year }}
                                </span>

                                <span>
                                    <i class="ri-time-line"></i>
                                    {{ number_format((float) ($payroll->total_work_hours ?? 0), 2) }} giờ làm
                                </span>

                                <span>
                                    <i class="ri-loop-right-line"></i>
                                    {{ number_format((float) ($payroll->total_converted_hours ?? 0), 2) }} giờ quy đổi
                                </span>

                                <span>
                                    <i class="ri-percent-line"></i>
                                    Hệ số chuyên môn {{ number_format((float) ($payroll->doctor_coefficient ?? 0), 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="amount-box">
                            <div class="amount">
                                {{ number_format((float) ($payroll->net_amount ?? 0), 0, ',', '.') }} đ
                            </div>

                            <div class="badge {{ $statusClass }}">
                                <i class="ri-circle-fill" style="font-size:8px;"></i>
                                {{ $payroll->status_label }}
                            </div>

                            @if($payroll->doctor_confirmed_at)
                                <div class="badge success" style="font-size: 11px; padding: 5px 10px; min-height: 28px;">
                                    <i class="ri-checkbox-circle-fill" style="font-size: 12px;"></i>
                                    Đã xác nhận nhận lương
                                </div>
                            @elseif($payroll->status === 'paid')
                                <div class="badge" style="background: #fef3c7; color: #92400e; font-size: 11px; padding: 5px 10px; min-height: 28px;">
                                    <i class="ri-time-line" style="font-size: 12px;"></i>
                                    Chưa xác nhận
                                </div>
                            @endif

                            <a href="{{ route('doctor.payroll.show', $payroll->id) }}" class="btn btn-secondary btn-sm" style="max-width: 100px;">
                                <i class="ri-eye-line"></i>
                                Chi tiết
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="empty">
                        Không tìm thấy phiếu lương nào đã được xác nhận trong khoảng thời gian được chọn.
                    </div>
                @endforelse
            </div>

            @if(method_exists($payrolls, 'links'))
                <div style="margin-top:18px;">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
