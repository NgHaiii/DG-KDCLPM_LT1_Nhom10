@extends('layouts.admin-layout')

@section('title', 'Hệ số xử lý dịch vụ')

@section('page-title', 'Hệ số xử lý dịch vụ')
@section('page-subtitle', 'Thiết lập hệ số tính lương theo loại dịch vụ và từng dịch vụ cụ thể.')

@section('header-actions')
    <a href="{{ route('admin.payroll.index') }}" class="header-btn secondary">
        <i class="ri-arrow-left-line"></i>
        Quay lại tính lương
    </a>
    <a href="{{ route('admin.payroll.settings') }}" class="header-btn">
        <i class="ri-settings-3-line"></i>
        Cấu hình lương
    </a>
@endsection

@section('styles')
<style>
    .payroll-page {
        display: grid;
        gap: 18px;
    }

    .payroll-card {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07);
        overflow: hidden;
    }

    .card-head {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
    }

    .card-title i {
        color: #0ea5e9;
        font-size: 22px;
    }

    .card-note {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        margin-top: 3px;
    }

    .card-body {
        padding: 18px 20px;
    }

    .alert-box {
        border-radius: 14px;
        padding: 13px 16px;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .alert-success {
        color: #047857;
        background: #ecfdf5;
        border-color: #86efac;
    }

    .alert-error {
        color: #b91c1c;
        background: #fef2f2;
        border-color: #fecaca;
    }

    .info-box {
        padding: 13px 15px;
        border-radius: 14px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        color: #075985;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .btn {
        height: 42px;
        border: 0;
        border-radius: 12px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 950;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #38bdf8, #0284c7);
        color: #fff;
        box-shadow: 0 10px 22px rgba(14, 165, 233, 0.22);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(14, 165, 233, 0.3);
    }

    .btn-secondary {
        background: #fff;
        color: #0f172a;
        border: 1px solid #dbe3ef;
    }

    .btn-secondary:hover {
        border-color: #38bdf8;
        color: #0284c7;
        background: #f0f9ff;
    }

    .btn-danger {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .form-group {
        display: grid;
        gap: 6px;
    }

    .form-label {
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .form-control {
        width: 100%;
        height: 42px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        padding: 0 12px;
        outline: none;
        transition: all 0.2s ease;
    }

    textarea.form-control {
        height: auto;
        min-height: 78px;
        padding: 11px 12px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
    }

    .type-config-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .type-config-item {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
        padding: 13px;
        display: grid;
        gap: 10px;
    }

    .type-config-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .type-name {
        color: #0f172a;
        font-size: 15px;
        font-weight: 950;
        line-height: 1.25;
    }

    .type-count {
        padding: 5px 9px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .type-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        align-items: center;
    }

    .toolbar {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) 190px auto;
        gap: 10px;
        align-items: end;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        margin-bottom: 16px;
    }

    .service-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .service-row {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
        padding: 13px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 110px;
        gap: 12px;
        align-items: center;
    }

    .service-row.hidden {
        display: none;
    }

    .service-name {
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
        line-height: 1.3;
    }

    .service-meta {
        margin-top: 7px;
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .type-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 12px;
        font-weight: 950;
    }

    .hint-line {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
        margin-top: 6px;
        line-height: 1.35;
    }

    .coefficient-input {
        text-align: center;
        font-size: 17px;
        font-weight: 950;
    }

    .section-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .two-column {
        display: grid;
        grid-template-columns: 390px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .form-grid .full {
        grid-column: 1 / -1;
    }

    .filter-form {
        display: grid;
        grid-template-columns: 120px 120px minmax(220px, 1fr) auto;
        gap: 10px;
        align-items: end;
        margin-bottom: 16px;
        padding: 14px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .complexity-list {
        display: grid;
        gap: 10px;
    }

    .complexity-item {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        padding: 13px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: start;
    }

    .complexity-title {
        color: #0f172a;
        font-size: 16px;
        font-weight: 950;
        margin-bottom: 7px;
    }

    .complexity-meta {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    .coefficient-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 64px;
        height: 34px;
        padding: 0 10px;
        border-radius: 999px;
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
        font-weight: 950;
    }

    .empty-state {
        border: 1px dashed #bae6fd;
        border-radius: 16px;
        background: #f8fafc;
        color: #64748b;
        font-size: 15px;
        font-weight: 850;
        text-align: center;
        padding: 28px 18px;
    }

    .pagination-wrap {
        margin-top: 16px;
    }

    @media (max-width: 1280px) {
        .type-config-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .service-grid {
            grid-template-columns: 1fr;
        }

        .two-column {
            grid-template-columns: 1fr;
        }

        .toolbar,
        .filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .type-config-grid,
        .toolbar,
        .filter-form,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .service-row {
            grid-template-columns: 1fr;
        }

        .section-actions,
        .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
@php
    $services = $services ?? collect();
    $doctors = $doctors ?? collect();
    $complexities = $complexities ?? collect();
    $appointments = $appointments ?? collect();

    $month = $month ?? now()->month;
    $year = $year ?? now()->year;
    $doctorId = $doctorId ?? request('doctor_id');

    $serviceUpdateRoute = \Illuminate\Support\Facades\Route::has('admin.payroll.service-complexities.update')
        ? route('admin.payroll.service-complexities.update')
        : '#';

    $caseStoreRoute = \Illuminate\Support\Facades\Route::has('admin.payroll.case-complexities.store')
        ? route('admin.payroll.case-complexities.store')
        : '#';

    $caseIndexRoute = \Illuminate\Support\Facades\Route::has('admin.payroll.case-complexities')
        ? route('admin.payroll.case-complexities')
        : '#';

    $serviceTypes = $services
        ->pluck('type')
        ->map(fn ($type) => trim((string) $type))
        ->filter()
        ->unique()
        ->values();

    $typeDefaults = [
        'Khám' => 0.05,
        'Điều trị' => 0.15,
        'Thẩm mỹ' => 0.25,
        'Phẫu thuật' => 0.35,
    ];
@endphp

<div class="payroll-page">
    @if(session('success'))
        <div class="alert-box alert-success">
            <i class="ri-checkbox-circle-line"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-box alert-error">
            <i class="ri-error-warning-line"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-box alert-error">
            <strong>Lỗi dữ liệu:</strong>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ $serviceUpdateRoute }}">
        @csrf
        @method('PUT')

        <div class="payroll-card">
            <div class="card-head">
                <div>
                    <div class="card-title">
                        <i class="ri-price-tag-3-line"></i>
                        Hệ số theo loại dịch vụ
                    </div>
                    <div class="card-note">
                        Nhập hệ số cho từng loại, sau đó áp dụng xuống toàn bộ dịch vụ thuộc loại đó.
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="info-box">
                    Nên dùng hệ số theo loại dịch vụ làm mặc định. Nếu một dịch vụ riêng có độ khó khác biệt, bạn vẫn có thể chỉnh trực tiếp ở danh sách bên dưới.
                </div>

                @if($serviceTypes->count())
                    <div class="type-config-grid">
                        @foreach($serviceTypes as $type)
                            @php
                                $typeServices = $services->filter(fn ($service) => trim((string) $service->type) === $type);
                                $avgCoefficient = $typeServices->avg(fn ($service) => (float) ($service->salary_complexity_coefficient ?? 0));
                                $defaultCoefficient = $typeDefaults[$type] ?? $avgCoefficient ?? 0;
                            @endphp

                            <div class="type-config-item">
                                <div class="type-config-head">
                                    <div class="type-name">{{ $type }}</div>
                                    <div class="type-count">{{ $typeServices->count() }} dịch vụ</div>
                                </div>

                                <div class="type-actions">
                                    <input type="number"
                                           class="form-control type-coefficient-input"
                                           data-type="{{ mb_strtolower($type) }}"
                                           value="{{ number_format((float) $defaultCoefficient, 2, '.', '') }}"
                                           min="0"
                                           max="0.5"
                                           step="0.01">

                                    <button type="button"
                                            class="btn btn-secondary apply-type-btn"
                                            data-type="{{ mb_strtolower($type) }}">
                                        Áp dụng
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        Chưa có loại dịch vụ nào để thiết lập hệ số.
                    </div>
                @endif
            </div>
        </div>

        <div class="payroll-card">
            <div class="card-head">
                <div>
                    <div class="card-title">
                        <i class="ri-stethoscope-line"></i>
                        Dịch vụ và hệ số chi tiết
                    </div>
                    <div class="card-note">
                        Có thể chỉnh riêng từng dịch vụ nếu hệ số khác mức mặc định của loại.
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="toolbar">
                    <div class="form-group">
                        <label class="form-label">Tìm nhanh dịch vụ</label>
                        <input type="text" id="serviceSearch" class="form-control" placeholder="Nhập tên dịch vụ hoặc loại dịch vụ...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Loại dịch vụ</label>
                        <select id="serviceTypeFilter" class="form-control">
                            <option value="">Tất cả</option>
                            @foreach($serviceTypes as $type)
                                <option value="{{ mb_strtolower($type) }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" id="resetFilterBtn" class="btn btn-secondary">
                        <i class="ri-close-line"></i>
                        Xóa lọc
                    </button>
                </div>

                @if($services->count())
                    <div class="service-grid" id="serviceGrid">
                        @foreach($services as $service)
                            @php
                                $coefficient = old(
                                    'service_coefficients.' . $service->id,
                                    $service->salary_complexity_coefficient ?? 0
                                );

                                $serviceType = trim((string) ($service->type ?: 'Chưa phân loại'));
                                $serviceTypeKey = mb_strtolower($serviceType);
                                $searchText = mb_strtolower(($service->name ?? '') . ' ' . $serviceType);
                            @endphp

                            <div class="service-row"
                                 data-search="{{ $searchText }}"
                                 data-type="{{ $serviceTypeKey }}">
                                <div>
                                    <div class="service-name">
                                        {{ $service->name }}
                                    </div>

                                    <div class="service-meta">
                                        <span class="type-pill">
                                            <i class="ri-price-tag-3-line"></i>
                                            {{ $serviceType }}
                                        </span>

                                        <span>
                                            <i class="ri-time-line"></i>
                                            {{ $service->actual_duration ?? $service->duration_minutes ?? 0 }} phút
                                        </span>

                                        <span>
                                            <i class="ri-calendar-check-line"></i>
                                            {{ $service->is_active ? 'Đang dùng' : 'Tạm ngừng' }}
                                        </span>
                                    </div>

                                    <div class="hint-line">
                                        Hệ số hợp lệ từ 0.00 đến 0.50.
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Hệ số</label>
                                    <input type="number"
                                           class="form-control coefficient-input service-coefficient"
                                           name="service_coefficients[{{ $service->id }}]"
                                           value="{{ number_format((float) $coefficient, 2, '.', '') }}"
                                           min="0"
                                           max="0.5"
                                           step="0.01">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="section-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line"></i>
                            Lưu toàn bộ hệ số
                        </button>
                    </div>
                @else
                    <div class="empty-state">
                        Chưa có dịch vụ nào để thiết lập hệ số.
                    </div>
                @endif
            </div>
        </div>
    </form>

    <div class="two-column">
        <div class="payroll-card">
            <div class="card-head">
                <div>
                    <div class="card-title">
                        <i class="ri-add-circle-line"></i>
                        Hệ số cộng thêm ca đặc biệt
                    </div>
                    <div class="card-note">
                        Chỉ dùng khi ca thực tế khó hơn mức mặc định của dịch vụ.
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="info-box">
                    Phần này không bắt buộc. Hệ số cộng thêm từ <strong>0.10</strong> đến <strong>0.50</strong>.
                </div>

                <form method="POST" action="{{ $caseStoreRoute }}">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group full">
                            <label class="form-label">Chọn ca khám đã hoàn thành</label>
                            <select name="appointment_id" id="appointmentSelect" class="form-control">
                                <option value="">-- Không gắn với ca khám cụ thể --</option>
                                @foreach($appointments as $appointment)
                                    @php
                                        $patientName = $appointment->patientProfile?->full_name
                                            ?? $appointment->patient?->name
                                            ?? 'Bệnh nhân #' . $appointment->id;

                                        $doctorName = $appointment->doctor?->name ?? 'Chưa có bác sĩ';
                                        $serviceName = $appointment->service?->name ?? 'Chưa có dịch vụ';
                                        $dateText = optional($appointment->appointment_date)->format('d/m/Y H:i');
                                    @endphp
                                    <option value="{{ $appointment->id }}"
                                            data-doctor-id="{{ $appointment->doctor_id }}"
                                            data-patient-profile-id="{{ $appointment->patient_profile_id }}"
                                            data-case-date="{{ optional($appointment->appointment_date)->format('Y-m-d') }}"
                                            data-month="{{ optional($appointment->appointment_date)->format('n') }}"
                                            data-year="{{ optional($appointment->appointment_date)->format('Y') }}"
                                            data-title="{{ $serviceName }}">
                                        {{ $dateText }} - {{ $patientName }} - {{ $serviceName }} - {{ $doctorName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group full">
                            <label class="form-label">Bác sĩ</label>
                            <select name="doctor_id" id="doctorSelect" class="form-control" required>
                                <option value="">-- Chọn bác sĩ --</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $doctorId) === (string) $doctor->id)>
                                        {{ $doctor->name }}{{ $doctor->degree ? ' - ' . $doctor->degree : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="patient_profile_id" id="patientProfileId" value="{{ old('patient_profile_id') }}">

                        <div class="form-group">
                            <label class="form-label">Tháng</label>
                            <input type="number" name="salary_month" id="salaryMonth" class="form-control"
                                   value="{{ old('salary_month', $month) }}" min="1" max="12" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Năm</label>
                            <input type="number" name="salary_year" id="salaryYear" class="form-control"
                                   value="{{ old('salary_year', $year) }}" min="2000" max="2100" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ngày xử lý</label>
                            <input type="date" name="case_date" id="caseDate" class="form-control"
                                   value="{{ old('case_date', now()->toDateString()) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hệ số cộng thêm</label>
                            <input type="number" name="complexity_coefficient" class="form-control"
                                   value="{{ old('complexity_coefficient', '0.10') }}"
                                   min="0.1" max="0.5" step="0.01" required>
                        </div>

                        <div class="form-group full">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" name="case_title" id="caseTitle" class="form-control"
                                   value="{{ old('case_title') }}"
                                   placeholder="VD: Nhổ răng khó, xử lý biến chứng...">
                        </div>

                        <div class="form-group full">
                            <label class="form-label">Lý do cộng hệ số</label>
                            <textarea name="reason" class="form-control" placeholder="Mô tả lý do ca này cần cộng thêm hệ số...">{{ old('reason') }}</textarea>
                        </div>

                        <div class="form-group full">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" placeholder="Ghi chú nội bộ nếu có...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="section-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-add-line"></i>
                            Ghi nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="payroll-card">
            <div class="card-head">
                <div>
                    <div class="card-title">
                        <i class="ri-list-check-3"></i>
                        Danh sách hệ số cộng thêm
                    </div>
                    <div class="card-note">
                        Các hệ số thủ công đã ghi nhận trong kỳ lương.
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ $caseIndexRoute }}" class="filter-form">
                    <div class="form-group">
                        <label class="form-label">Tháng</label>
                        <input type="number" name="month" class="form-control" value="{{ $month }}" min="1" max="12">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Năm</label>
                        <input type="number" name="year" class="form-control" value="{{ $year }}" min="2000" max="2100">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bác sĩ</label>
                        <select name="doctor_id" class="form-control">
                            <option value="">Tất cả bác sĩ</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected((string) $doctorId === (string) $doctor->id)>
                                    {{ $doctor->name }}{{ $doctor->degree ? ' - ' . $doctor->degree : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-3-line"></i>
                        Lọc
                    </button>
                </form>

                @if($complexities->count())
                    <div class="complexity-list">
                        @foreach($complexities as $item)
                            @php
                                $patientName = $item->patientProfile?->full_name
                                    ?? $item->appointment?->patientProfile?->full_name
                                    ?? $item->appointment?->patient?->name
                                    ?? 'Không rõ bệnh nhân';

                                $serviceName = $item->appointment?->service?->name;
                            @endphp

                            <div class="complexity-item">
                                <div>
                                    <div class="complexity-title">
                                        {{ $item->case_title ?: ($serviceName ?: 'Ca bệnh đặc biệt') }}
                                    </div>

                                    <div class="complexity-meta">
                                        <span>
                                            <i class="ri-user-heart-line"></i>
                                            {{ $item->doctor?->name ?? 'Không rõ bác sĩ' }}
                                        </span>

                                        <span>
                                            <i class="ri-user-line"></i>
                                            {{ $patientName }}
                                        </span>

                                        <span>
                                            <i class="ri-calendar-line"></i>
                                            {{ optional($item->case_date)->format('d/m/Y') }}
                                        </span>

                                        @if($serviceName)
                                            <span>
                                                <i class="ri-stethoscope-line"></i>
                                                {{ $serviceName }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($item->reason)
                                        <div class="hint-line">
                                            <strong>Lý do:</strong> {{ $item->reason }}
                                        </div>
                                    @endif
                                </div>

                                <div style="display: grid; gap: 8px; justify-items: end;">
                                    <span class="coefficient-badge">
                                        +{{ number_format((float) $item->complexity_coefficient, 2) }}
                                    </span>

                                    @if(\Illuminate\Support\Facades\Route::has('admin.payroll.case-complexities.delete'))
                                        <form method="POST"
                                              action="{{ route('admin.payroll.case-complexities.delete', $item) }}"
                                              onsubmit="return confirm('Xóa hệ số cộng thêm này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="height: 34px;">
                                                <i class="ri-delete-bin-line"></i>
                                                Xóa
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($complexities, 'links'))
                        <div class="pagination-wrap">
                            {{ $complexities->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        Chưa có hệ số cộng thêm trong kỳ này.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serviceSearch = document.getElementById('serviceSearch');
    const serviceTypeFilter = document.getElementById('serviceTypeFilter');
    const resetFilterBtn = document.getElementById('resetFilterBtn');
    const serviceRows = Array.from(document.querySelectorAll('.service-row'));
    const applyTypeButtons = Array.from(document.querySelectorAll('.apply-type-btn'));

    function normalizeText(value) {
        return (value || '').toString().toLowerCase().trim();
    }

    function filterServices() {
        const keyword = normalizeText(serviceSearch?.value);
        const selectedType = normalizeText(serviceTypeFilter?.value);

        serviceRows.forEach(function (row) {
            const searchText = normalizeText(row.dataset.search);
            const rowType = normalizeText(row.dataset.type);

            const matchKeyword = !keyword || searchText.includes(keyword);
            const matchType = !selectedType || rowType === selectedType;

            row.classList.toggle('hidden', !(matchKeyword && matchType));
        });
    }

    serviceSearch?.addEventListener('input', filterServices);
    serviceTypeFilter?.addEventListener('change', filterServices);

    resetFilterBtn?.addEventListener('click', function () {
        if (serviceSearch) {
            serviceSearch.value = '';
        }

        if (serviceTypeFilter) {
            serviceTypeFilter.value = '';
        }

        filterServices();
    });

    applyTypeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const type = normalizeText(button.dataset.type);
            const wrapper = button.closest('.type-config-item');
            const typeInput = wrapper?.querySelector('.type-coefficient-input');
            const value = typeInput?.value;

            if (value === '' || value === null || Number(value) < 0 || Number(value) > 0.5) {
                alert('Hệ số loại dịch vụ phải nằm trong khoảng 0.00 đến 0.50.');
                return;
            }

            let changedCount = 0;

            serviceRows.forEach(function (row) {
                if (normalizeText(row.dataset.type) === type) {
                    const input = row.querySelector('.service-coefficient');

                    if (input) {
                        input.value = Number(value).toFixed(2);
                        changedCount++;
                    }
                }
            });

            alert('Đã áp dụng hệ số ' + Number(value).toFixed(2) + ' cho ' + changedCount + ' dịch vụ thuộc loại này. Nhớ bấm "Lưu toàn bộ hệ số" để lưu vào database.');
        });
    });

    const appointmentSelect = document.getElementById('appointmentSelect');
    const doctorSelect = document.getElementById('doctorSelect');
    const patientProfileId = document.getElementById('patientProfileId');
    const salaryMonth = document.getElementById('salaryMonth');
    const salaryYear = document.getElementById('salaryYear');
    const caseDate = document.getElementById('caseDate');
    const caseTitle = document.getElementById('caseTitle');

    appointmentSelect?.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];

        if (!option || !option.value) {
            return;
        }

        if (option.dataset.doctorId && doctorSelect) {
            doctorSelect.value = option.dataset.doctorId;
        }

        if (patientProfileId) {
            patientProfileId.value = option.dataset.patientProfileId || '';
        }

        if (salaryMonth && option.dataset.month) {
            salaryMonth.value = option.dataset.month;
        }

        if (salaryYear && option.dataset.year) {
            salaryYear.value = option.dataset.year;
        }

        if (caseDate && option.dataset.caseDate) {
            caseDate.value = option.dataset.caseDate;
        }

        if (caseTitle && !caseTitle.value && option.dataset.title) {
            caseTitle.value = option.dataset.title;
        }
    });
});
</script>
@endsection