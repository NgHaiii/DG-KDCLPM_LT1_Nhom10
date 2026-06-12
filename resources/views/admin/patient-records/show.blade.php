@extends('layouts.admin-layout')

@section('title', 'Chi tiết hồ sơ bệnh án')
@section('page-title', 'Chi tiết hồ sơ bệnh án')
@section('page-subtitle', 'Theo dõi toàn bộ thông tin hành chính, lịch sử khám, bệnh án, hình ảnh lâm sàng và sơ đồ răng')

@section('header-actions')
    <a href="{{ route('admin.patient-records.index') }}" class="admin-back-btn">
        <i class="ri-arrow-left-line"></i>
        Quay lại danh sách
    </a>
@endsection

@section('content')
@php
    $displayName = $patientProfile->full_name ?: 'Bệnh nhân #' . $patientProfile->id;
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'), 'UTF-8');

    $genderLabel = match ($patientProfile->gender) {
        'male', 'Nam' => 'Nam',
        'female', 'Nữ' => 'Nữ',
        'other' => 'Khác',
        default => 'Chưa cập nhật',
    };

    $sourceLabel = match ($patientProfile->source) {
        'online' => 'Online',
        'offline' => 'Trực tiếp',
        'imported' => 'Nhập dữ liệu',
        default => 'Không rõ',
    };

    $sourceClass = match ($patientProfile->source) {
        'online' => 'blue',
        'offline' => 'green',
        default => 'gray',
    };

    $age = $patientProfile->dob ? $patientProfile->dob->age : null;

    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'checked_in' => 'Đã tiếp nhận',
        'waiting' => 'Đang chờ',
        'in_progress' => 'Đang khám',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    $statusClasses = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'checked_in' => 'primary',
        'waiting' => 'warning',
        'in_progress' => 'blue',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    $teeth = [
        'upper_right' => [18,17,16,15,14,13,12,11],
        'upper_left' => [21,22,23,24,25,26,27,28],
        'lower_right' => [48,47,46,45,44,43,42,41],
        'lower_left' => [31,32,33,34,35,36,37,38],
    ];

    $toothStatusLabels = [
        'healthy' => 'Khỏe mạnh',
        'caries' => 'Sâu răng',
        'filled' => 'Đã trám',
        'crown' => 'Bọc sứ',
        'root_canal' => 'Điều trị tủy',
        'missing' => 'Đã mất',
    ];

    $toothStatusClasses = [
        'healthy' => 'healthy',
        'caries' => 'caries',
        'filled' => 'filled',
        'crown' => 'crown',
        'root_canal' => 'root-canal',
        'missing' => 'missing',
    ];

    $latestStatus = $latestAppointment?->status;

    $followUpValue = $latestRecord?->follow_up_date;
    if ($followUpValue instanceof \Carbon\CarbonInterface) {
        $followUpDisplay = $followUpValue->format('d/m/Y');
    } else {
        $followUpDisplay = $followUpValue ?: 'Chưa hẹn tái khám';
    }
@endphp

<style>
    .admin-back-btn {
        height: 44px;
        padding: 0 16px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: #0f172a;
        border: 1px solid #dbe3ef;
        text-decoration: none;
        font-weight: 900;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }

    .record-show-page {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 22px;
        align-items: start;
    }

    .main-column,
    .side-column {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.07);
        overflow: hidden;
        min-width: 0;
    }

    .panel-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
    }

    .panel-title i {
        color: #0ea5e9;
        font-size: 22px;
    }

    .panel-body {
        padding: 22px;
    }

    .patient-hero {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
    }

    .patient-avatar {
        width: 78px;
        height: 78px;
        border-radius: 24px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
        color: #fff;
        font-size: 34px;
        font-weight: 900;
        box-shadow: 0 14px 28px rgba(14, 165, 233, 0.26);
    }

    .patient-name {
        color: #0f172a;
        font-size: 30px;
        font-weight: 950;
        letter-spacing: -0.8px;
        margin-bottom: 8px;
    }

    .patient-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .tag.blue { background: #dbeafe; color: #1d4ed8; }
    .tag.green { background: #dcfce7; color: #15803d; }
    .tag.gray { background: #f1f5f9; color: #475569; }
    .tag.warning { background: #fef3c7; color: #b45309; }
    .tag.success { background: #dcfce7; color: #15803d; }
    .tag.danger { background: #fee2e2; color: #b91c1c; }
    .tag.info { background: #e0f2fe; color: #0369a1; }
    .tag.primary { background: #e0f2fe; color: #0284c7; }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 20px;
    }

    .summary-item {
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        min-width: 0;
    }

    .summary-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .summary-value {
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.35;
        word-break: break-word;
    }

    .tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 14px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .tab-btn {
        border: 1px solid transparent;
        background: transparent;
        color: #475569;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .tab-btn.active {
        background: #fff;
        color: #0284c7;
        border-color: #bae6fd;
        box-shadow: 0 8px 18px rgba(14, 165, 233, 0.08);
    }

    .tab-panel {
        display: none;
        padding: 22px;
        min-width: 0;
    }

    .tab-panel.active {
        display: block;
    }

    .section-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .info-card {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 16px;
        padding: 16px;
    }

    .info-card-title {
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-card-title i {
        color: #0ea5e9;
    }

    .info-text {
        color: #334155;
        font-size: 14px;
        line-height: 1.6;
        white-space: pre-line;
    }

    .alert-card {
        border-color: #fecaca;
        background: #fff1f2;
    }

    .alert-card .info-card-title,
    .alert-card .info-card-title i {
        color: #b91c1c;
    }

    .timeline {
        display: grid;
        gap: 14px;
    }

    .visit-item {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #fff;
        overflow: hidden;
    }

    .visit-head {
        padding: 16px;
        background: #f8fafc;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
    }

    .visit-name {
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .visit-meta {
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
    }

    .visit-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .visit-summary-line {
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
        margin-top: 8px;
        line-height: 1.45;
    }

    .visit-head-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .visit-toggle {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 22px;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .visit-toggle:hover {
        border-color: #38bdf8;
        color: #0284c7;
        background: #f0f9ff;
    }

    .visit-toggle i {
        transition: transform 0.2s ease;
    }

    .visit-item.open .visit-toggle i {
        transform: rotate(180deg);
    }

    .visit-body {
        padding: 16px;
        display: none;
        gap: 12px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }

    .visit-item.open .visit-body {
        display: grid;
    }

    .record-field {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 13px;
        background: #f8fafc;
    }

    .field-title {
        color: #2563eb;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .field-value {
        color: #0f172a;
        font-size: 14px;
        line-height: 1.55;
        white-space: pre-line;
    }

    .tooth-board {
        display: grid;
        gap: 18px;
        width: 100%;
        overflow: hidden;
        min-width: 0;
    }

    .jaw-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 18px;
        width: 100%;
        min-width: 0;
    }

    .jaw-grid > div {
        min-width: 0;
    }

    .jaw-title {
        color: #334155;
        font-size: 15px;
        font-weight: 900;
        margin-bottom: 10px;
    }

    .teeth-row {
        display: grid;
        grid-template-columns: repeat(8, minmax(34px, 1fr));
        gap: 7px;
        width: 100%;
        min-width: 0;
    }

    .tooth {
        min-width: 0;
        height: 64px;
        border: 2px solid #dbe3ef;
        background: #f8fafc;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: #0f172a;
        font-size: 13px;
        font-weight: 950;
        position: relative;
    }

    .tooth::before {
        content: "";
        width: clamp(18px, 45%, 24px);
        aspect-ratio: 1;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #fff;
        position: absolute;
        top: 8px;
    }

    .tooth span {
        position: relative;
        top: 13px;
        font-size: clamp(11px, 1.1vw, 13px);
    }

    .tooth.healthy { border-color: #dbe3ef; }
    .tooth.caries { border-color: #ef4444; background: #fff1f2; }
    .tooth.filled { border-color: #0891b2; background: #ecfeff; }
    .tooth.crown { border-color: #eab308; background: #fefce8; }
    .tooth.root-canal { border-color: #db2777; background: #fdf2f8; }
    .tooth.missing { border-color: #64748b; background: #e2e8f0; }

    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #475569;
        font-size: 13px;
        font-weight: 800;
    }

    .legend-dot {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        border: 2px solid #dbe3ef;
        background: #fff;
    }

    .legend-dot.caries { border-color: #ef4444; }
    .legend-dot.filled { border-color: #0891b2; }
    .legend-dot.crown { border-color: #eab308; }
    .legend-dot.root-canal { border-color: #db2777; }
    .legend-dot.missing { border-color: #64748b; background: #e2e8f0; }

    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
    }

    .image-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .image-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        display: block;
        background: #f1f5f9;
    }

    .image-info {
        padding: 13px;
    }

    .image-title {
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .image-meta {
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }

    .side-list {
        display: grid;
        gap: 10px;
    }

    .side-item {
        border: 1px solid #e2e8f0;
        border-radius: 15px;
        background: #f8fafc;
        padding: 13px;
    }

    .side-item-label {
        color: #2563eb;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .side-item-value {
        color: #0f172a;
        font-size: 15px;
        font-weight: 850;
        line-height: 1.45;
        white-space: pre-line;
    }

    .empty-box {
        padding: 28px;
        text-align: center;
        color: #64748b;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
    }

    .empty-box i {
        display: block;
        color: #0ea5e9;
        font-size: 34px;
        margin-bottom: 8px;
    }

    @media (max-width: 1200px) {
        .record-show-page {
            grid-template-columns: 1fr;
        }

        .summary-grid {
            grid-template-columns: repeat(2, minmax(150px, 1fr));
        }
    }

    @media (max-width: 900px) {
        .jaw-grid {
            grid-template-columns: 1fr;
        }

        .teeth-row {
            grid-template-columns: repeat(8, minmax(34px, 1fr));
        }

        .tooth {
            height: 58px;
        }
    }

    @media (max-width: 760px) {
        .patient-hero {
            grid-template-columns: 1fr;
        }

        .summary-grid,
        .section-grid {
            grid-template-columns: 1fr;
        }

        .teeth-row {
            grid-template-columns: repeat(4, minmax(42px, 1fr));
        }

        .visit-head {
            flex-direction: column;
        }

        .visit-head-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<div class="record-show-page">
    <div class="main-column">
        <div class="panel">
            <div class="panel-body">
                <div class="patient-hero">
                    <div class="patient-avatar">{{ $initial }}</div>

                    <div>
                        <div class="patient-name">{{ $displayName }}</div>

                        <div class="patient-meta">
                            <span class="tag {{ $sourceClass }}">
                                <i class="ri-user-location-line"></i>
                                {{ $sourceLabel }}
                            </span>

                            <span class="tag gray">
                                <i class="ri-user-line"></i>
                                {{ $genderLabel }}{{ $age ? ' · ' . $age . ' tuổi' : '' }}
                            </span>

                            <span class="tag blue">
                                <i class="ri-drop-line"></i>
                                Nhóm máu: {{ $patientProfile->blood_type ?: 'Chưa cập nhật' }}
                            </span>

                            @if($latestAppointment)
                                <span class="tag {{ $statusClasses[$latestStatus] ?? 'gray' }}">
                                    <i class="ri-calendar-check-line"></i>
                                    {{ $statusLabels[$latestStatus] ?? 'Không rõ' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="tag gray">
                            <i class="ri-file-list-3-line"></i>
                            {{ $appointments->count() }} lượt khám
                        </span>
                    </div>
                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Số điện thoại</div>
                        <div class="summary-value">{{ $patientProfile->phone ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Email</div>
                        <div class="summary-value">{{ $patientProfile->email ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Ngày sinh</div>
                        <div class="summary-value">{{ $patientProfile->dob?->format('d/m/Y') ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">CCCD</div>
                        <div class="summary-value">{{ $patientProfile->identity_number ?: 'Chưa cập nhật' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="tabs">
                <button type="button" class="tab-btn active" data-tab="overview">
                    <i class="ri-dashboard-line"></i>
                    Tổng quan
                </button>

                <button type="button" class="tab-btn" data-tab="visits">
                    <i class="ri-stethoscope-line"></i>
                    Lịch sử khám
                </button>

                <button type="button" class="tab-btn" data-tab="chart">
                    <i class="ri-tooth-line"></i>
                    Sơ đồ răng
                </button>

                <button type="button" class="tab-btn" data-tab="images">
                    <i class="ri-image-line"></i>
                    Cận lâm sàng
                </button>
            </div>

            <div class="tab-panel active" id="tab-overview">
                <div class="section-grid">
                    <div class="info-card">
                        <div class="info-card-title">
                            <i class="ri-map-pin-line"></i>
                            Địa chỉ
                        </div>
                        <div class="info-text">{{ $patientProfile->address ?: 'Chưa cập nhật.' }}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-card-title">
                            <i class="ri-briefcase-line"></i>
                            Nghề nghiệp
                        </div>
                        <div class="info-text">{{ $patientProfile->occupation ?: 'Chưa cập nhật.' }}</div>
                    </div>

                    <div class="info-card alert-card">
                        <div class="info-card-title">
                            <i class="ri-alarm-warning-line"></i>
                            Dị ứng / phản ứng thuốc
                        </div>
                        <div class="info-text">{{ $patientProfile->allergies ?: 'Chưa ghi nhận dị ứng.' }}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-card-title">
                            <i class="ri-heart-pulse-line"></i>
                            Tiền sử bệnh lý toàn thân
                        </div>
                        <div class="info-text">{{ $patientProfile->medical_history ?: 'Chưa cập nhật.' }}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-card-title">
                            <i class="ri-capsule-line"></i>
                            Thuốc đang sử dụng
                        </div>
                        <div class="info-text">{{ $patientProfile->current_medications ?: 'Chưa cập nhật.' }}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-card-title">
                            <i class="ri-tooth-line"></i>
                            Tiền sử nha khoa
                        </div>
                        <div class="info-text">{{ $patientProfile->dental_history ?: 'Chưa cập nhật.' }}</div>
                    </div>
                </div>
            </div>

            <div class="tab-panel" id="tab-visits">
                @if($appointments->isEmpty())
                    <div class="empty-box">
                        <i class="ri-calendar-close-line"></i>
                        Chưa có lịch sử khám.
                    </div>
                @else
                    <div class="timeline">
                        @foreach($appointments as $appointment)
                            @php
                                $record = $appointment->medicalRecord;
                                $appointmentStatus = $appointment->status;
                            @endphp

                            <div class="visit-item">
                                <div class="visit-head">
                                    <div>
                                        <div class="visit-name">
                                            {{ $appointment->service?->name ?? 'Dịch vụ chưa xác định' }}
                                        </div>

                                        <div class="visit-meta">
                                            <span>
                                                <i class="ri-calendar-line"></i>
                                                {{ $appointment->appointment_date?->format('d/m/Y H:i') ?: 'Chưa có thời gian' }}
                                            </span>

                                            <span>
                                                <i class="ri-user-star-line"></i>
                                                {{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}
                                            </span>

                                            <span>
                                                <i class="ri-building-line"></i>
                                                {{ $appointment->room?->name ?? 'Chưa phân phòng' }}
                                            </span>

                                            <span>
                                                <i class="ri-time-line"></i>
                                                {{ $appointment->duration_minutes ?? 0 }} phút
                                            </span>
                                        </div>

                                        <div class="visit-summary-line">
                                            Chẩn đoán:
                                            {{ $record?->diagnosis ?: 'Chưa cập nhật' }}
                                        </div>
                                    </div>

                                    <div class="visit-head-actions">
                                        <span class="tag {{ $statusClasses[$appointmentStatus] ?? 'gray' }}">
                                            {{ $statusLabels[$appointmentStatus] ?? 'Không rõ' }}
                                        </span>

                                        <button type="button" class="visit-toggle" title="Xem chi tiết lượt khám">
                                            <i class="ri-arrow-down-s-line"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="visit-body">
                                    @if($record)
                                        <div class="record-field">
                                            <div class="field-title">Lý do khám / Triệu chứng chính</div>
                                            <div class="field-value">{{ $record->chief_complaint ?: 'Chưa cập nhật.' }}</div>
                                        </div>

                                        <div class="record-field">
                                            <div class="field-title">Khám lâm sàng / Tình trạng trong miệng</div>
                                            <div class="field-value">{{ $record->clinical_findings ?: 'Chưa cập nhật.' }}</div>
                                        </div>

                                        <div class="record-field">
                                            <div class="field-title">Chẩn đoán</div>
                                            <div class="field-value">{{ $record->diagnosis ?: 'Chưa cập nhật.' }}</div>
                                        </div>

                                        <div class="record-field">
                                            <div class="field-title">Kế hoạch điều trị</div>
                                            <div class="field-value">{{ $record->treatment_plan ?: 'Chưa cập nhật.' }}</div>
                                        </div>

                                        <div class="record-field">
                                            <div class="field-title">Đơn thuốc / Chỉ định</div>
                                            <div class="field-value">{{ $record->prescription ?: 'Chưa cập nhật.' }}</div>
                                        </div>

                                        <div class="record-field">
                                            <div class="field-title">Ghi chú bác sĩ</div>
                                            <div class="field-value">{{ $record->doctor_notes ?: 'Chưa cập nhật.' }}</div>
                                        </div>
                                    @else
                                        <div class="empty-box">
                                            <i class="ri-file-warning-line"></i>
                                            Lượt khám này chưa có bệnh án chi tiết.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-panel" id="tab-chart">
                <div class="tooth-board">
                    <div class="jaw-grid">
                        <div>
                            <div class="jaw-title">Hàm trên - Phải</div>
                            <div class="teeth-row">
                                @foreach($teeth['upper_right'] as $tooth)
                                    @php
                                        $chart = $dentalCharts->get((string) $tooth) ?: $dentalCharts->get($tooth);
                                        $toothStatus = $chart?->status ?: 'healthy';
                                        $toothClass = $toothStatusClasses[$toothStatus] ?? 'healthy';
                                    @endphp

                                    <div class="tooth {{ $toothClass }}" title="{{ $tooth }} - {{ $toothStatusLabels[$toothStatus] ?? 'Không rõ' }}">
                                        <span>{{ $tooth }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="jaw-title">Hàm trên - Trái</div>
                            <div class="teeth-row">
                                @foreach($teeth['upper_left'] as $tooth)
                                    @php
                                        $chart = $dentalCharts->get((string) $tooth) ?: $dentalCharts->get($tooth);
                                        $toothStatus = $chart?->status ?: 'healthy';
                                        $toothClass = $toothStatusClasses[$toothStatus] ?? 'healthy';
                                    @endphp

                                    <div class="tooth {{ $toothClass }}" title="{{ $tooth }} - {{ $toothStatusLabels[$toothStatus] ?? 'Không rõ' }}">
                                        <span>{{ $tooth }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="jaw-title">Hàm dưới - Phải</div>
                            <div class="teeth-row">
                                @foreach($teeth['lower_right'] as $tooth)
                                    @php
                                        $chart = $dentalCharts->get((string) $tooth) ?: $dentalCharts->get($tooth);
                                        $toothStatus = $chart?->status ?: 'healthy';
                                        $toothClass = $toothStatusClasses[$toothStatus] ?? 'healthy';
                                    @endphp

                                    <div class="tooth {{ $toothClass }}" title="{{ $tooth }} - {{ $toothStatusLabels[$toothStatus] ?? 'Không rõ' }}">
                                        <span>{{ $tooth }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="jaw-title">Hàm dưới - Trái</div>
                            <div class="teeth-row">
                                @foreach($teeth['lower_left'] as $tooth)
                                    @php
                                        $chart = $dentalCharts->get((string) $tooth) ?: $dentalCharts->get($tooth);
                                        $toothStatus = $chart?->status ?: 'healthy';
                                        $toothClass = $toothStatusClasses[$toothStatus] ?? 'healthy';
                                    @endphp

                                    <div class="tooth {{ $toothClass }}" title="{{ $tooth }} - {{ $toothStatusLabels[$toothStatus] ?? 'Không rõ' }}">
                                        <span>{{ $tooth }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="legend">
                        <span class="legend-item"><span class="legend-dot"></span>Khỏe mạnh</span>
                        <span class="legend-item"><span class="legend-dot caries"></span>Sâu răng</span>
                        <span class="legend-item"><span class="legend-dot filled"></span>Đã trám</span>
                        <span class="legend-item"><span class="legend-dot crown"></span>Bọc sứ</span>
                        <span class="legend-item"><span class="legend-dot root-canal"></span>Điều trị tủy</span>
                        <span class="legend-item"><span class="legend-dot missing"></span>Đã mất</span>
                    </div>

                    @if($dentalChartHistories->isNotEmpty())
                        <div class="timeline">
                            @foreach($dentalChartHistories as $history)
                                <div class="record-field">
                                    <div class="field-title">
                                        Răng {{ $history->tooth_number ?? 'N/A' }} - {{ $history->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="field-value">
                                        {{ $toothStatusLabels[$history->status ?? ''] ?? ($history->status ?? 'Cập nhật') }}
                                        @if(!empty($history->note))
                                            - {{ $history->note }}
                                        @endif
                                        @if($history->doctor)
                                            <br>Bác sĩ: {{ $history->doctor->name }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="tab-panel" id="tab-images">
                @if($clinicalImages->isEmpty())
                    <div class="empty-box">
                        <i class="ri-image-line"></i>
                        Chưa có hình ảnh X-quang hoặc cận lâm sàng.
                    </div>
                @else
                    <div class="image-grid">
                        @foreach($clinicalImages as $image)
                            @php
                                $path = $image->file_path ?? $image->image_path ?? $image->path ?? null;
                                $imageUrl = $path ? asset('storage/' . ltrim($path, '/')) : null;
                                $imageTitle = $image->title ?? $image->name ?? 'Hình ảnh lâm sàng';
                                $imageType = $image->image_type ?? $image->type ?? 'Không rõ loại';
                                $takenDate = $image->taken_date;

                                if ($takenDate instanceof \Carbon\CarbonInterface) {
                                    $takenDateDisplay = $takenDate->format('d/m/Y');
                                } else {
                                    $takenDateDisplay = $takenDate ?: 'Chưa cập nhật';
                                }
                            @endphp

                            <div class="image-card">
                                @if($imageUrl)
                                    <a href="{{ $imageUrl }}" target="_blank">
                                        <img src="{{ $imageUrl }}" alt="{{ $imageTitle }}">
                                    </a>
                                @else
                                    <div class="empty-box" style="border:0;border-radius:0;">
                                        <i class="ri-image-close-line"></i>
                                        Không tìm thấy file ảnh.
                                    </div>
                                @endif

                                <div class="image-info">
                                    <div class="image-title">{{ $imageTitle }}</div>
                                    <div class="image-meta">
                                        Loại: {{ $imageType }}<br>
                                        Ngày chụp: {{ $takenDateDisplay }}<br>
                                        Bác sĩ: {{ $image->doctor?->name ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="side-column">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="ri-user-heart-line"></i>
                    Thông tin liên hệ
                </div>
            </div>

            <div class="panel-body">
                <div class="side-list">
                    <div class="side-item">
                        <div class="side-item-label">Người liên hệ khẩn cấp</div>
                        <div class="side-item-value">{{ $patientProfile->emergency_contact_name ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="side-item">
                        <div class="side-item-label">SĐT khẩn cấp</div>
                        <div class="side-item-value">{{ $patientProfile->emergency_contact_phone ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="side-item">
                        <div class="side-item-label">Lần cập nhật gần nhất</div>
                        <div class="side-item-value">{{ $patientProfile->updated_at?->format('d/m/Y H:i') ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="side-item">
                        <div class="side-item-label">Lần khám gần nhất</div>
                        <div class="side-item-value">
                            {{ $latestAppointment?->appointment_date?->format('d/m/Y H:i') ?: 'Chưa có lượt khám' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="ri-clipboard-line"></i>
                    Tóm tắt chuyên môn
                </div>
            </div>

            <div class="panel-body">
                <div class="side-list">
                    <div class="side-item">
                        <div class="side-item-label">Chẩn đoán gần nhất</div>
                        <div class="side-item-value">{{ $latestRecord?->diagnosis ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="side-item">
                        <div class="side-item-label">Kế hoạch gần nhất</div>
                        <div class="side-item-value">{{ $latestRecord?->treatment_plan ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="side-item">
                        <div class="side-item-label">Tái khám</div>
                        <div class="side-item-value">{{ $followUpDisplay }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="ri-shield-check-line"></i>
                    Quyền quản trị
                </div>
            </div>

            <div class="panel-body">
                <div class="info-text">
                    Admin được quyền xem và kiểm tra toàn bộ hồ sơ để quản lý hệ thống.
                    Nội dung chuyên môn như chẩn đoán, điều trị, đơn thuốc và sơ đồ răng chỉ nên được cập nhật bởi bác sĩ phụ trách.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const tab = button.dataset.tab;

            tabButtons.forEach(function (item) {
                item.classList.remove('active');
            });

            tabPanels.forEach(function (panel) {
                panel.classList.remove('active');
            });

            button.classList.add('active');

            const activePanel = document.getElementById('tab-' + tab);

            if (activePanel) {
                activePanel.classList.add('active');
            }
        });
    });

    document.querySelectorAll('.visit-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const visitItem = button.closest('.visit-item');

            if (!visitItem) {
                return;
            }

            const isOpen = visitItem.classList.contains('open');

            document.querySelectorAll('.visit-item').forEach(function (item) {
                item.classList.remove('open');
            });

            if (!isOpen) {
                visitItem.classList.add('open');
            }
        });
    });
});
</script>
@endsection