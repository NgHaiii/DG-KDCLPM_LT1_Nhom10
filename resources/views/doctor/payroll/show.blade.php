@extends('layouts.doctor-layout')

@section('title', 'Chi tiết bảng lương')
@section('page-title', 'Chi tiết bảng lương')
@section('page-subtitle', 'Chi tiết từng ca làm việc, hệ số và số tiền được tính')

@php
    $items = collect($payroll->items ?? []);

    $fmt = fn ($n) => number_format((float) ($n ?? 0), 0, ',', '.') . ' đ';
    $num = fn ($n) => number_format((float) ($n ?? 0), 2, ',', '.');

    $statusLabel = [
        'draft' => 'Nháp',
        'approved' => 'Đã duyệt',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ][$payroll->status] ?? $payroll->status;

@endphp

@section('header-actions')
    <a href="{{ route('doctor.payroll.index') }}" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>
    @if($payroll->status === 'paid')
        @if(!$payroll->doctor_confirmed_at)
            <form action="{{ route('doctor.payroll.acknowledge', $payroll) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn xác nhận đã nhận đầy đủ lương cho kỳ lương này?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success" style="background-color: #10b981; border-color: #10b981; color: white;">
                    <i class="ri-checkbox-circle-line"></i>
                    Xác nhận đã nhận lương
                </button>
            </form>
        @else
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-semibold d-inline-flex align-items-center gap-1" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 6px 12px; border-radius: 9999px; font-weight: 700; font-size: 14px;">
                <i class="ri-checkbox-circle-fill" style="color: #10b981;"></i>
                Đã nhận lương ({{ $payroll->doctor_confirmed_at->format('d/m/Y H:i') }})
            </span>
        @endif
    @endif

@endsection

@section('styles')
<style>
    .sheet {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .sheet-head {
        padding: 18px 22px;
        border-bottom: 1px solid #dbe3ef;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: start;
    }

    .title {
        font-size: 22px;
        font-weight: 950;
        color: #0f172a;
    }

    .meta {
        color: #475569;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
        margin-top: 6px;
    }

    .amount {
        text-align: right;
    }

    .amount strong {
        display: block;
        font-size: 26px;
        font-weight: 950;
        color: #0f172a;
    }

    .badge {
        display: inline-flex;
        padding: 7px 12px;
        border-radius: 999px;
        background: #dcfce7;
        color: #047857;
        font-weight: 900;
        margin-top: 8px;
    }

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        border-bottom: 1px solid #dbe3ef;
    }

    .sum-cell {
        padding: 14px 16px;
        border-right: 1px solid #edf2f7;
    }

    .sum-cell:last-child {
        border-right: 0;
    }

    .sum-cell span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .sum-cell strong {
        color: #0f172a;
        font-size: 17px;
        font-weight: 950;
    }

    .table-scroll {
        overflow: auto;
    }

    .payroll-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: collapse;
    }

    .payroll-table th {
        background: #e0f2fe;
        color: #0f172a;
        font-size: 12px;
        text-transform: uppercase;
        padding: 12px;
        border: 1px solid #bae6fd;
        text-align: left;
        white-space: nowrap;
    }

    .payroll-table td {
        padding: 11px 12px;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .payroll-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }

    .payroll-table tfoot th {
        background: #f1f5f9;
        border: 1px solid #dbe3ef;
        padding: 12px;
        font-weight: 950;
    }

    .right {
        text-align: right;
        white-space: nowrap;
    }

    .center {
        text-align: center;
    }

    .note {
        padding: 16px 22px;
        color: #475569;
        font-weight: 700;
        line-height: 1.6;
    }

    .actions-row {
        padding: 16px 22px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #dbe3ef;
    }

    .btn {
        height: 40px;
        border-radius: 10px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        cursor: pointer;
    }

    .btn-primary {
        background: #0ea5e9;
        color: #fff;
        border-color: #0ea5e9;
    }

    .btn-secondary:hover {
        background: #f0f9ff;
        color: #0284c7;
    }

    @media(max-width: 900px) {
        .sheet-head,
        .summary {
            grid-template-columns: 1fr;
        }

        .amount {
            text-align: left;
        }
    }

    @media print {
        .sidebar,
        .header,
        .header-actions,
        .actions-row,
        .btn {
            display: none !important;
        }

        .sheet {
            box-shadow: none;
            border: 1px solid #000;
        }

        body {
            background: #fff !important;
        }
    }
</style>
@endsection

@section('content')
<div class="sheet">
    <div class="sheet-head">
        <div>
            <div class="title">BẢNG LƯƠNG BÁC SĨ CHI TIẾT</div>
            <div class="meta">
                Mã phiếu: <strong>{{ $payroll->payroll_code }}</strong><br>
                Bác sĩ: <strong>{{ $payroll->doctor?->name ?? 'Không rõ' }}</strong>
                @if($payroll->doctor?->degree)
                    - {{ $payroll->doctor->degree }}
                @endif
                <br>
                Kỳ lương: <strong>Tháng {{ $payroll->salary_month }}/{{ $payroll->salary_year }}</strong><br>
                Ngày lập:
                {{ optional($payroll->generated_at)->format('d/m/Y H:i') ?? $payroll->created_at?->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="amount">
            <strong>{{ $fmt($payroll->net_amount) }}</strong>
            <span class="badge {{ $payroll->status === 'paid' ? 'success' : 'info' }}">{{ $statusLabel }}</span>
        </div>
    </div>

    <div class="summary">
        <div class="sum-cell">
            <span>Tiền/giờ cơ bản</span>
            <strong>{{ $fmt($payroll->base_hourly_rate) }}</strong>
        </div>
        <div class="sum-cell">
            <span>HS học vị</span>
            <strong>{{ $num($payroll->doctor_coefficient) }}</strong>
        </div>
        <div class="sum-cell">
            <span>Giờ làm việc</span>
            <strong>{{ $num($payroll->total_work_hours) }}</strong>
        </div>
        <div class="sum-cell">
            <span>Giờ quy đổi</span>
            <strong>{{ $num($payroll->total_converted_hours) }}</strong>
        </div>
        <div class="sum-cell">
            <span>Hệ số cộng thêm</span>
            <strong>{{ $num($payroll->total_patient_complexity_coefficient) }}</strong>
        </div>
    </div>

    <div class="table-scroll">
        <table id="payrollTable" class="payroll-table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Thứ</th>
                    <th>Giờ làm</th>
                    <th>Loại ca</th>
                    <th class="center">Số giờ</th>
                    <th class="center">HS ca</th>
                    <th class="center">Số ca khám</th>
                    <th class="center">HS dịch vụ</th>
                    <th class="center">HS cộng thêm</th>
                    <th class="center">Giờ quy đổi</th>
                    <th class="right">Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['work_date'])->format('d/m/Y') }}</td>
                        <td>{{ $item['weekday'] ?? '' }}</td>
                        <td>{{ $item['start_time'] ?? '' }} - {{ $item['end_time'] ?? '' }}</td>
                        <td>{{ $item['shift_type_label'] ?? $item['shift_type'] ?? '' }}</td>
                        <td class="center">{{ $num($item['work_hours'] ?? 0) }}</td>
                        <td class="center">{{ $num($item['shift_coefficient'] ?? 0) }}</td>
                        <td class="center">{{ $item['completed_case_count'] ?? 0 }}</td>
                        <td class="center">{{ $num($item['service_complexity_total'] ?? 0) }}</td>
                        <td class="center">{{ $num($item['manual_complexity_total'] ?? 0) }}</td>
                        <td class="center">{{ $num($item['converted_hours'] ?? 0) }}</td>
                        <td class="right">{{ $fmt($item['amount'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="center">Chưa có dữ liệu chi tiết ca làm việc.</td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="4">Tổng cộng</th>
                    <th class="center">{{ $num($payroll->total_work_hours) }}</th>
                    <th></th>
                    <th></th>
                    <th colspan="2" class="center">{{ $num($payroll->total_patient_complexity_coefficient) }}</th>
                    <th class="center">{{ $num($payroll->total_converted_hours) }}</th>
                    <th class="right">{{ $fmt($payroll->gross_amount) }}</th>
                </tr>
                <tr>
                    <th colspan="10">Thưởng</th>
                    <th class="right">{{ $fmt($payroll->bonus_amount) }}</th>
                </tr>
                <tr>
                    <th colspan="10">Khấu trừ</th>
                    <th class="right">{{ $fmt($payroll->deduction_amount) }}</th>
                </tr>
                <tr>
                    <th colspan="10">Thực nhận</th>
                    <th class="right">{{ $fmt($payroll->net_amount) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($payroll->notes)
        <div class="note">
            <strong>Ghi chú:</strong> {{ $payroll->notes }}
        </div>
    @endif

    <div class="actions-row" style="justify-content: center; border-top: 1px solid #dbe3ef; padding: 16px 22px; flex-direction: column; align-items: center; gap: 8px;">
        @if($payroll->doctor_confirmed_at)
            <div style="color: #15803d; font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                <i class="ri-checkbox-circle-fill" style="color: #10b981;"></i>
                Bạn đã xác nhận đã nhận đủ lương vào lúc {{ $payroll->doctor_confirmed_at->format('d/m/Y H:i') }}.
            </div>
        @endif
        <span style="color: #64748b; font-size: 13px; font-weight: 700; text-align: center;">
            <i class="ri-information-line"></i>
            Bảng lương đã được xác nhận bởi Ban Quản Trị. Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ bộ phận Hành chính - Nhân sự.
        </span>
    </div>
</div>
@endsection
