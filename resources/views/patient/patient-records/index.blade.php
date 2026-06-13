@extends('layouts.patient-layout')

@section('title', 'Hồ sơ bệnh án cá nhân')
@section('page-title', 'Hồ sơ bệnh án cá nhân')
@section('page-subtitle', 'Theo dõi thông tin hồ sơ, lịch sử khám, chẩn đoán, điều trị và ảnh cận lâm sàng của bạn')

@section('header-actions')
@if(\Illuminate\Support\Facades\Route::has('patient.appointment.create'))
    <a href="{{ route('patient.appointment.create') }}" class="btn btn-primary">
        <i class="ri-calendar-check-line"></i>
        Đặt lịch khám
    </a>
@endif
@endsection

@php
    use App\Models\Appointment;
    use App\Models\PatientProfile;

    $user = auth()->user();
    $userId = auth()->id();

    $profiles = PatientProfile::where('user_id', $userId)
        ->latest('updated_at')
        ->get();

    $profileIds = $profiles->pluck('id')->filter()->values();

    $appointments = Appointment::with([
            'doctor',
            'service',
            'room',
            'medicalRecord',
            'clinicalImages',
            'patientProfile',
        ])
        ->where(function ($query) use ($userId, $profileIds) {
            $query->where('patient_id', $userId);

            if ($profileIds->isNotEmpty()) {
                $query->orWhereIn('patient_profile_id', $profileIds);
            }
        })
        ->latest('appointment_date')
        ->get();

    $primaryProfile = $profiles->first()
        ?: $appointments->pluck('patientProfile')->filter()->first();

    $displayName = $primaryProfile?->full_name
        ?: $user?->name
        ?: 'Bệnh nhân';

    $displayPhone = $primaryProfile?->phone
        ?: $user?->phone
        ?: $user?->phone_number
        ?: 'Chưa cập nhật';

    $displayEmail = $primaryProfile?->email
        ?: $user?->email
        ?: 'Chưa cập nhật';

    $displayDob = $primaryProfile?->dob
        ? $primaryProfile->dob->format('d/m/Y')
        : 'Chưa cập nhật';

    $displayGender = $primaryProfile?->gender_label
        ?: $primaryProfile?->gender
        ?: 'Chưa cập nhật';

    $displayAddress = $primaryProfile?->address ?: 'Chưa cập nhật';
    $displayIdentity = $primaryProfile?->identity_number ?: 'Chưa cập nhật';
    $displayBloodType = $primaryProfile?->blood_type ?: 'Chưa cập nhật';
    $displayOccupation = $primaryProfile?->occupation ?: 'Chưa cập nhật';

    $avatarText = mb_strtoupper(mb_substr($displayName, 0, 1));

    $completedAppointments = $appointments->where('status', 'completed');
    $medicalAppointments = $appointments->filter(fn ($appointment) => $appointment->medicalRecord);
    $upcomingAppointments = $appointments
        ->filter(function ($appointment) {
            return $appointment->appointment_date
                && $appointment->appointment_date->gte(now())
                && in_array($appointment->status, ['pending', 'confirmed', 'checked_in', 'waiting', 'in_progress'], true);
        })
        ->sortBy(fn ($appointment) => optional($appointment->appointment_date)->timestamp ?? 0)
        ->values();

    $latestAppointment = $appointments->first();
    $latestMedicalAppointment = $medicalAppointments->first();
    $latestRecord = $latestMedicalAppointment?->medicalRecord;

    $clinicalImages = $appointments
        ->flatMap(fn ($appointment) => $appointment->clinicalImages ?? collect())
        ->sortByDesc(fn ($image) => optional($image->taken_date)->timestamp ?? optional($image->created_at)->timestamp ?? 0)
        ->values();

    $dentalCharts = collect();

    if ($primaryProfile && class_exists(\App\Models\DentalChart::class)) {
        $dentalCharts = \App\Models\DentalChart::where('patient_profile_id', $primaryProfile->id)
            ->get()
            ->keyBy('tooth_number');
    }

    $toothRows = [
        ['label' => 'Hàm trên — Phải', 'teeth' => [18, 17, 16, 15, 14, 13, 12, 11]],
        ['label' => 'Hàm trên — Trái', 'teeth' => [21, 22, 23, 24, 25, 26, 27, 28]],
        ['label' => 'Hàm dưới — Phải', 'teeth' => [48, 47, 46, 45, 44, 43, 42, 41]],
        ['label' => 'Hàm dưới — Trái', 'teeth' => [31, 32, 33, 34, 35, 36, 37, 38]],
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
        'healthy' => 'tooth-healthy',
        'caries' => 'tooth-caries',
        'filled' => 'tooth-filled',
        'crown' => 'tooth-crown',
        'root_canal' => 'tooth-root',
        'missing' => 'tooth-missing',
    ];
@endphp

@section('styles')
<style>
    .record-shell {
        display: grid;
        gap: 22px;
    }

    .record-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .profile-head {
        padding: 24px;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }

    .profile-avatar {
        width: 82px;
        height: 82px;
        border-radius: 22px;
        background: var(--primary-gradient);
        color: #fff;
        display: grid;
        place-items: center;
        font-family: var(--font-title);
        font-size: 36px;
        font-weight: 800;
        box-shadow: 0 14px 30px rgba(14, 165, 233, 0.25);
    }

    .profile-name {
        font-family: var(--font-title);
        font-size: 30px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .profile-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: var(--radius-full);
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 800;
    }

    .pill-blue { background: #e0f2fe; color: #0284c7; }
    .pill-green { background: #dcfce7; color: #15803d; }
    .pill-red { background: #fee2e2; color: #b91c1c; }
    .pill-gray { background: #f1f5f9; color: #475569; }

    .fact-grid {
        padding: 0 24px 24px;
        display: grid;
        grid-template-columns: repeat(4, minmax(140px, 1fr));
        gap: 12px;
    }

    .fact-box {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        padding: 14px;
        min-width: 0;
    }

    .fact-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 800;
        margin-bottom: 6px;
    }

    .fact-value {
        color: var(--text-main);
        font-weight: 800;
        word-break: break-word;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 16px;
    }

    .summary-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .summary-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: var(--primary-light);
        color: var(--primary);
        display: grid;
        place-items: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .summary-label {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 4px;
    }

    .summary-value {
        font-family: var(--font-title);
        font-size: 25px;
        font-weight: 800;
        color: var(--text-main);
    }

    .main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 20px;
        align-items: start;
    }

    .tabs {
        display: inline-flex;
        gap: 5px;
        padding: 5px;
        background: #eef6fa;
        border: 1px solid #dbeaf2;
        border-radius: 16px;
        margin-bottom: 16px;
        max-width: 100%;
        overflow-x: auto;
    }

    .tab-btn {
        border: 0;
        background: transparent;
        padding: 10px 15px;
        border-radius: 12px;
        color: #475569;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }

    .tab-btn.active {
        background: #fff;
        color: var(--text-main);
        box-shadow: var(--shadow-sm);
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    .section-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .section-title {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        font-family: var(--font-title);
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
    }

    .section-title i {
        color: var(--primary);
    }

    .section-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .info-box {
        padding: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
    }

    .info-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .info-title i {
        color: var(--primary);
    }

    .info-text {
        color: #475569;
        line-height: 1.55;
        white-space: pre-line;
    }

    .warning-box {
        border-color: #fecaca;
        background: #fef2f2;
    }

    .warning-box .info-title,
    .warning-box .info-title i {
        color: #b91c1c;
    }

    .visit-search {
        margin-bottom: 14px;
        position: relative;
    }

    .visit-search input {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        padding: 13px 14px 13px 42px;
        outline: none;
        font-size: 14px;
    }

    .visit-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        font-size: 18px;
    }

    .visit-list {
        display: grid;
        gap: 12px;
    }

    .visit-item {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .visit-head {
        padding: 15px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        background: #f8fafc;
    }

    .visit-date {
        color: #2563eb;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .visit-name {
        color: var(--text-main);
        font-family: var(--font-title);
        font-size: 17px;
        font-weight: 800;
    }

    .visit-meta {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .visit-toggle {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: var(--text-main);
        display: grid;
        place-items: center;
        font-size: 22px;
        cursor: pointer;
    }

    .visit-toggle i {
        transition: transform 0.2s ease;
    }

    .visit-item.open .visit-toggle i {
        transform: rotate(180deg);
    }

    .visit-body {
        display: none;
        padding: 16px;
        border-top: 1px solid #e2e8f0;
    }

    .visit-item.open .visit-body {
        display: grid;
        gap: 12px;
    }

    .record-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .record-detail {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        background: #f8fafc;
    }

    .record-detail.full {
        grid-column: 1 / -1;
    }

    .record-label {
        font-size: 12px;
        font-weight: 900;
        color: #2563eb;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .record-value {
        color: var(--text-main);
        line-height: 1.55;
        white-space: pre-line;
    }

    .dental-chart-wrap {
    display: grid;
    gap: 18px;
}

.dental-arch-card {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    padding: 18px;
    overflow: hidden;
}

.dental-arch-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 16px;
}

.dental-arch-title strong {
    font-family: var(--font-title);
    color: var(--text-main);
    font-size: 16px;
}

.dental-arch-title span {
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 700;
}

.dental-board {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 6px;
}

.dental-board-inner {
    min-width: 760px;
    display: grid;
    gap: 22px;
}

.dental-row-labels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    color: #475569;
    font-weight: 900;
    font-size: 13px;
    margin-bottom: 8px;
}

.dental-row-labels span:last-child {
    text-align: right;
}

.dental-row {
    display: grid;
    grid-template-columns: repeat(16, minmax(38px, 1fr));
    gap: 6px;
    align-items: end;
}

.dental-row.lower {
    align-items: start;
}

.tooth {
    display: grid;
    justify-items: center;
    gap: 6px;
    min-width: 0;
}

.tooth-box {
    width: 40px;
    height: 48px;
    border-radius: 15px 15px 11px 11px;
    border: 2px solid #d6e1e8;
    background: #f8fcff;
    display: grid;
    place-items: center;
    position: relative;
    box-shadow: inset 0 -4px 0 rgba(15, 23, 42, 0.03);
}

.dental-row.lower .tooth-box {
    border-radius: 11px 11px 15px 15px;
    box-shadow: inset 0 4px 0 rgba(15, 23, 42, 0.03);
}

.tooth-box::before {
    content: "";
    width: 22px;
    height: 25px;
    border-radius: 50% 50% 42% 42%;
    background: #fff;
    border: 1px solid #dbe3ef;
    display: block;
}

.dental-row.lower .tooth-box::before {
    border-radius: 42% 42% 50% 50%;
}

.tooth-number {
    font-size: 12px;
    color: var(--text-main);
    font-weight: 900;
}

.tooth-status-text {
    font-size: 10px;
    color: #64748b;
    font-weight: 700;
    text-align: center;
    min-height: 13px;
    max-width: 54px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.tooth-caries {
    border-color: #ef4444;
    background: #fff1f2;
}

.tooth-filled {
    border-color: #0891b2;
    background: #cffafe;
}

.tooth-crown {
    border-color: #eab308;
    background: #fef3c7;
}

.tooth-root {
    border-color: #db2777;
    background: #fce7f3;
}

.tooth-missing {
    border-color: #64748b;
    background: #e2e8f0;
}

.tooth-missing::before {
    background: #cbd5e1;
    border-color: #64748b;
}

.tooth-note-dot {
    position: absolute;
    right: -3px;
    top: -3px;
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #2563eb;
    border: 2px solid #fff;
}

.dental-note-box {
    margin-top: 16px;
    border: 1px solid #dbeafe;
    background: #eff6ff;
    border-radius: 14px;
    padding: 14px;
    color: #1e3a8a;
    line-height: 1.55;
}

.dental-note-box strong {
    display: block;
    margin-bottom: 4px;
    color: #1d4ed8;
}

.legend {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
}

.legend span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.legend-mark {
    width: 14px;
    height: 14px;
    border-radius: 5px;
    border: 2px solid;
    background: #fff;
}

    .legend {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .legend span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .legend-mark {
        width: 14px;
        height: 14px;
        border-radius: 5px;
        border: 2px solid;
        background: #fff;
    }

    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 14px;
    }

    .image-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .image-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        display: block;
        background: #020617;
    }

    .image-info {
        padding: 13px;
    }

    .image-title {
        color: var(--text-main);
        font-weight: 800;
        margin-bottom: 4px;
    }

    .image-meta {
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .side-stack {
        display: grid;
        gap: 16px;
    }

    .side-box {
        padding: 16px;
    }

    .side-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font-title);
        font-size: 17px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 14px;
    }

    .side-title i {
        color: var(--primary);
    }

    .side-item {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
        padding: 13px;
        margin-bottom: 10px;
    }

    .side-item:last-child {
        margin-bottom: 0;
    }

    .side-label {
        color: #2563eb;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .side-value {
        color: var(--text-main);
        line-height: 1.5;
        white-space: pre-line;
    }

    .empty-state {
        padding: 36px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        display: block;
        color: var(--primary);
        font-size: 38px;
        margin-bottom: 10px;
    }

    @media (max-width: 1200px) {
        .main-grid {
            grid-template-columns: 1fr;
        }

        .side-stack {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .fact-grid,
        .summary-grid,
        .info-grid,
        .record-detail-grid,
        .side-stack {
            grid-template-columns: 1fr;
        }

        .profile-head {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .profile-name {
            font-size: 24px;
        }

        .visit-head {
            grid-template-columns: 1fr;
        }

        .visit-toggle {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="record-shell">
    <div class="record-card">
        <div class="profile-head">
            <div class="profile-avatar">{{ $avatarText }}</div>

            <div>
                <div class="profile-name">{{ $displayName }}</div>

                <div class="profile-pills">
                    <span class="pill pill-blue">
                        <i class="ri-user-heart-line"></i>
                        Hồ sơ cá nhân
                    </span>

                    <span class="pill pill-gray">
                        <i class="ri-drop-line"></i>
                        Nhóm máu: {{ $displayBloodType }}
                    </span>

                    @if($primaryProfile?->allergies)
                        <span class="pill pill-red">
                            <i class="ri-alarm-warning-line"></i>
                            Có dị ứng / phản ứng thuốc
                        </span>
                    @else
                        <span class="pill pill-green">
                            <i class="ri-shield-check-line"></i>
                            Chưa ghi nhận dị ứng
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="fact-grid">
            <div class="fact-box">
                <div class="fact-label">Số điện thoại</div>
                <div class="fact-value">{{ $displayPhone }}</div>
            </div>

            <div class="fact-box">
                <div class="fact-label">Email</div>
                <div class="fact-value">{{ $displayEmail }}</div>
            </div>

            <div class="fact-box">
                <div class="fact-label">Ngày sinh</div>
                <div class="fact-value">{{ $displayDob }}</div>
            </div>

            <div class="fact-box">
                <div class="fact-label">Giới tính</div>
                <div class="fact-value">{{ $displayGender }}</div>
            </div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon"><i class="ri-calendar-check-line"></i></div>
            <div>
                <div class="summary-label">Tổng lịch hẹn</div>
                <div class="summary-value">{{ $appointments->count() }}</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon"><i class="ri-check-double-line"></i></div>
            <div>
                <div class="summary-label">Lượt khám hoàn thành</div>
                <div class="summary-value">{{ $completedAppointments->count() }}</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon"><i class="ri-file-list-3-line"></i></div>
            <div>
                <div class="summary-label">Bệnh án đã lưu</div>
                <div class="summary-value">{{ $medicalAppointments->count() }}</div>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon"><i class="ri-image-line"></i></div>
            <div>
                <div class="summary-label">Ảnh cận lâm sàng</div>
                <div class="summary-value">{{ $clinicalImages->count() }}</div>
            </div>
        </div>
    </div>

    <div class="main-grid">
        <main>
            <div class="tabs">
                <button type="button" class="tab-btn active" data-tab="overview" onclick="switchPatientRecordTab('overview')">
                    <i class="ri-dashboard-line"></i>
                    Tổng quan
                </button>

                <button type="button" class="tab-btn" data-tab="visits" onclick="switchPatientRecordTab('visits')">
                    <i class="ri-stethoscope-line"></i>
                    Lịch sử khám
                </button>

                <button type="button" class="tab-btn" data-tab="teeth" onclick="switchPatientRecordTab('teeth')">
                    <i class="ri-hospital-line"></i>
                    Sơ đồ răng
                </button>

                <button type="button" class="tab-btn" data-tab="images" onclick="switchPatientRecordTab('images')">
                    <i class="ri-image-2-line"></i>
                    Cận lâm sàng
                </button>
            </div>

            <div class="tab-pane active" id="tab-overview">
                <div class="record-card">
                    <div class="section-head">
                        <div class="section-title">
                            <i class="ri-profile-line"></i>
                            Thông tin hồ sơ
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="info-grid">
                            <div class="info-box">
                                <div class="info-title"><i class="ri-map-pin-line"></i> Địa chỉ</div>
                                <div class="info-text">{{ $displayAddress }}</div>
                            </div>

                            <div class="info-box">
                                <div class="info-title"><i class="ri-id-card-line"></i> CCCD</div>
                                <div class="info-text">{{ $displayIdentity }}</div>
                            </div>

                            <div class="info-box">
                                <div class="info-title"><i class="ri-briefcase-line"></i> Nghề nghiệp</div>
                                <div class="info-text">{{ $displayOccupation }}</div>
                            </div>

                            <div class="info-box">
                                <div class="info-title"><i class="ri-contacts-line"></i> Liên hệ khẩn cấp</div>
                                <div class="info-text">
                                    {{ $primaryProfile?->emergency_contact_label ?? 'Chưa cập nhật' }}
                                </div>
                            </div>

                            <div class="info-box warning-box">
                                <div class="info-title"><i class="ri-alarm-warning-line"></i> Dị ứng / phản ứng thuốc</div>
                                <div class="info-text">{{ $primaryProfile?->allergies ?: 'Chưa ghi nhận dị ứng.' }}</div>
                            </div>

                            <div class="info-box">
                                <div class="info-title"><i class="ri-heart-pulse-line"></i> Tiền sử bệnh lý toàn thân</div>
                                <div class="info-text">{{ $primaryProfile?->medical_history ?: 'Chưa cập nhật.' }}</div>
                            </div>

                            <div class="info-box">
                                <div class="info-title"><i class="ri-capsule-line"></i> Thuốc đang sử dụng</div>
                                <div class="info-text">{{ $primaryProfile?->current_medications ?: 'Chưa cập nhật.' }}</div>
                            </div>

                            <div class="info-box">
                                <div class="info-title"><i class="ri-hospital-line"></i> Tiền sử nha khoa</div>
                                <div class="info-text">{{ $primaryProfile?->dental_history ?: 'Chưa cập nhật.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab-visits">
                <div class="record-card">
                    <div class="section-head">
                        <div class="section-title">
                            <i class="ri-stethoscope-line"></i>
                            Lịch sử khám & bệnh án
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="visit-search">
                            <i class="ri-search-line"></i>
                            <input type="text"
                                   id="visitSearchInput"
                                   autocomplete="off"
                                   placeholder="Tìm theo dịch vụ, bác sĩ, ngày khám, chẩn đoán...">
                        </div>

                        @if($appointments->isEmpty())
                            <div class="empty-state">
                                <i class="ri-file-list-3-line"></i>
                                Chưa có lịch hẹn hoặc bệnh án nào được ghi nhận.
                            </div>
                        @else
                            <div class="visit-list" id="visitList">
                                @foreach($appointments as $appointment)
                                    @php
                                        $record = $appointment->medicalRecord;
                                        $searchText = mb_strtolower(implode(' ', [
                                            $appointment->service?->name,
                                            $appointment->doctor?->name,
                                            $appointment->room?->name,
                                            $appointment->status_label,
                                            optional($appointment->appointment_date)->format('d/m/Y H:i'),
                                            $record?->diagnosis,
                                            $record?->chief_complaint,
                                            $record?->treatment_plan,
                                        ]));
                                    @endphp

                                    <div class="visit-item" data-search="{{ $searchText }}">
                                        <div class="visit-head">
                                            <div>
                                                <div class="visit-date">
                                                    {{ $appointment->appointment_date ? $appointment->appointment_date->format('d/m/Y H:i') : 'Chưa có ngày' }}
                                                </div>

                                                <div class="visit-name">
                                                    {{ $appointment->service?->name ?? 'Dịch vụ khám' }}
                                                </div>

                                                <div class="visit-meta">
                                                    <span><i class="ri-user-star-line"></i> {{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}</span>
                                                    <span><i class="ri-door-open-line"></i> {{ $appointment->room?->name ?? 'Chưa gán phòng' }}</span>
                                                    <span><i class="ri-check-double-line"></i> {{ $appointment->status_label ?? $appointment->status }}</span>
                                                    <span><i class="ri-time-line"></i> {{ $appointment->duration_minutes ?? 0 }} phút</span>
                                                </div>
                                            </div>

                                            <button type="button" class="visit-toggle" onclick="toggleVisit(this)">
                                                <i class="ri-arrow-down-s-line"></i>
                                            </button>
                                        </div>

                                        <div class="visit-body">
                                            @if($record)
                                                <div class="record-detail-grid">
                                                    <div class="record-detail full">
                                                        <div class="record-label">Lý do khám / Triệu chứng chính</div>
                                                        <div class="record-value">{{ $record->chief_complaint ?: 'Chưa cập nhật' }}</div>
                                                    </div>

                                                    <div class="record-detail full">
                                                        <div class="record-label">Khám lâm sàng / Tình trạng trong miệng</div>
                                                        <div class="record-value">{{ $record->clinical_findings ?: 'Chưa cập nhật' }}</div>
                                                    </div>

                                                    <div class="record-detail">
                                                        <div class="record-label">Chẩn đoán</div>
                                                        <div class="record-value">{{ $record->diagnosis ?: 'Chưa cập nhật' }}</div>
                                                    </div>

                                                    <div class="record-detail">
                                                        <div class="record-label">Ngày tái khám</div>
                                                        <div class="record-value">
                                                            {{ $record->follow_up_date ? $record->follow_up_date->format('d/m/Y') : 'Chưa hẹn tái khám' }}
                                                        </div>
                                                    </div>

                                                    <div class="record-detail full">
                                                        <div class="record-label">Kế hoạch điều trị</div>
                                                        <div class="record-value">{{ $record->treatment_plan ?: 'Chưa cập nhật' }}</div>
                                                    </div>

                                                    <div class="record-detail full">
                                                        <div class="record-label">Đơn thuốc / Chỉ định</div>
                                                        <div class="record-value">{{ $record->prescription ?: 'Không có' }}</div>
                                                    </div>

                                                    <div class="record-detail full">
                                                        <div class="record-label">Ghi chú bác sĩ</div>
                                                        <div class="record-value">{{ $record->doctor_notes ?: 'Không có' }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="empty-state">
                                                    <i class="ri-information-line"></i>
                                                    Lịch này chưa có bệnh án chi tiết. Bệnh án chỉ được tạo sau khi bác sĩ hoàn thành ca khám.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab-teeth">
    <div class="record-card">
        <div class="section-head">
            <div class="section-title">
                <i class="ri-hospital-line"></i>
                Sơ đồ răng FDI
            </div>
        </div>

        <div class="section-body">
            @if(!$primaryProfile)
                <div class="empty-state">
                    <i class="ri-hospital-line"></i>
                    Chưa có hồ sơ bệnh nhân để hiển thị sơ đồ răng.
                </div>
            @else
                @php
                    $upperTeeth = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
                    $lowerTeeth = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];

                    $changedTeethCount = $dentalCharts
                        ->filter(fn ($chart) => ($chart->status ?? 'healthy') !== 'healthy' || filled($chart->note ?? null))
                        ->count();

                    $latestToothNote = $dentalCharts
                        ->filter(fn ($chart) => filled($chart->note ?? null))
                        ->sortByDesc(fn ($chart) => optional($chart->updated_at)->timestamp ?? 0)
                        ->first();
                @endphp

                <div class="dental-chart-wrap">
                    <div class="dental-arch-card">
                        <div class="dental-arch-title">
                            <strong>Tổng quan tình trạng răng</strong>
                            <span>{{ $changedTeethCount }} răng có ghi nhận</span>
                        </div>

                        <div class="dental-board">
                            <div class="dental-board-inner">
                                <div>
                                    <div class="dental-row-labels">
                                        <span>Hàm trên - Phải</span>
                                        <span>Hàm trên - Trái</span>
                                    </div>

                                    <div class="dental-row upper">
                                        @foreach($upperTeeth as $toothNumber)
                                            @php
                                                $chart = $dentalCharts->get((string) $toothNumber) ?: $dentalCharts->get($toothNumber);
                                                $status = $chart?->status ?: 'healthy';
                                                $statusClass = $toothStatusClasses[$status] ?? 'tooth-healthy';
                                                $statusLabel = $toothStatusLabels[$status] ?? 'Khỏe mạnh';
                                                $hasNote = filled($chart?->note);
                                            @endphp

                                            <div class="tooth" title="Răng {{ $toothNumber }} - {{ $statusLabel }}{{ $hasNote ? ': ' . $chart->note : '' }}">
                                                <div class="tooth-box {{ $statusClass }}">
                                                    @if($hasNote)
                                                        <span class="tooth-note-dot"></span>
                                                    @endif
                                                </div>

                                                <div class="tooth-number">{{ $toothNumber }}</div>

                                                @if($status !== 'healthy')
                                                    <div class="tooth-status-text">{{ $statusLabel }}</div>
                                                @else
                                                    <div class="tooth-status-text"></div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <div class="dental-row-labels">
                                        <span>Hàm dưới - Phải</span>
                                        <span>Hàm dưới - Trái</span>
                                    </div>

                                    <div class="dental-row lower">
                                        @foreach($lowerTeeth as $toothNumber)
                                            @php
                                                $chart = $dentalCharts->get((string) $toothNumber) ?: $dentalCharts->get($toothNumber);
                                                $status = $chart?->status ?: 'healthy';
                                                $statusClass = $toothStatusClasses[$status] ?? 'tooth-healthy';
                                                $statusLabel = $toothStatusLabels[$status] ?? 'Khỏe mạnh';
                                                $hasNote = filled($chart?->note);
                                            @endphp

                                            <div class="tooth" title="Răng {{ $toothNumber }} - {{ $statusLabel }}{{ $hasNote ? ': ' . $chart->note : '' }}">
                                                <div class="tooth-number">{{ $toothNumber }}</div>

                                                <div class="tooth-box {{ $statusClass }}">
                                                    @if($hasNote)
                                                        <span class="tooth-note-dot"></span>
                                                    @endif
                                                </div>

                                                @if($status !== 'healthy')
                                                    <div class="tooth-status-text">{{ $statusLabel }}</div>
                                                @else
                                                    <div class="tooth-status-text"></div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($latestToothNote)
                            <div class="dental-note-box">
                                <strong>Ghi chú răng gần nhất</strong>
                                Răng {{ $latestToothNote->tooth_number }}:
                                {{ $latestToothNote->note }}
                            </div>
                        @endif

                        <div class="legend">
                            <span><i class="legend-mark" style="border-color:#d6e1e8;"></i> Khỏe mạnh</span>
                            <span><i class="legend-mark" style="border-color:#ef4444;"></i> Sâu răng</span>
                            <span><i class="legend-mark" style="border-color:#0891b2;"></i> Đã trám</span>
                            <span><i class="legend-mark" style="border-color:#eab308;"></i> Bọc sứ</span>
                            <span><i class="legend-mark" style="border-color:#db2777;"></i> Điều trị tủy</span>
                            <span><i class="legend-mark" style="border-color:#64748b;"></i> Đã mất</span>
                            <span><i class="legend-mark" style="border-color:#2563eb;background:#2563eb;"></i> Có ghi chú</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

            <div class="tab-pane" id="tab-images">
                <div class="record-card">
                    <div class="section-head">
                        <div class="section-title">
                            <i class="ri-image-2-line"></i>
                            X-quang & cận lâm sàng
                        </div>
                    </div>

                    <div class="section-body">
                        @if($clinicalImages->isEmpty())
                            <div class="empty-state">
                                <i class="ri-image-add-line"></i>
                                Chưa có ảnh X-quang hoặc cận lâm sàng trong hồ sơ.
                            </div>
                        @else
                            <div class="image-grid">
                                @foreach($clinicalImages as $image)
                                    <div class="image-card">
                                        <a href="{{ asset('storage/' . $image->file_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $image->file_path) }}" alt="{{ $image->title ?: 'Ảnh cận lâm sàng' }}">
                                        </a>

                                        <div class="image-info">
                                            <div class="image-title">{{ $image->title ?: ($image->image_type_label ?? 'Ảnh cận lâm sàng') }}</div>
                                            <div class="image-meta">
                                                {{ $image->image_type_label ?? $image->image_type }}
                                                @if($image->taken_date)
                                                    · {{ $image->taken_date->format('d/m/Y') }}
                                                @endif
                                                @if($image->doctor?->name)
                                                    <br>Bác sĩ: {{ $image->doctor->name }}
                                                @endif
                                                @if($image->notes)
                                                    <br>{{ $image->notes }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>

        <aside class="side-stack">
            <div class="record-card side-box">
                <div class="side-title">
                    <i class="ri-alarm-warning-line"></i>
                    Cảnh báo y tế
                </div>

                <div class="side-item">
                    <div class="side-label">Dị ứng / phản ứng thuốc</div>
                    <div class="side-value">{{ $primaryProfile?->allergies ?: 'Chưa ghi nhận dị ứng.' }}</div>
                </div>

                <div class="side-item">
                    <div class="side-label">Tiền sử bệnh lý</div>
                    <div class="side-value">{{ $primaryProfile?->medical_history ?: 'Chưa cập nhật.' }}</div>
                </div>

                <div class="side-item">
                    <div class="side-label">Thuốc đang sử dụng</div>
                    <div class="side-value">{{ $primaryProfile?->current_medications ?: 'Chưa cập nhật.' }}</div>
                </div>
            </div>

            <div class="record-card side-box">
                <div class="side-title">
                    <i class="ri-line-chart-line"></i>
                    Tóm tắt điều trị
                </div>

                <div class="side-item">
                    <div class="side-label">Chẩn đoán gần nhất</div>
                    <div class="side-value">{{ $latestRecord?->diagnosis ?: 'Chưa cập nhật.' }}</div>
                </div>

                <div class="side-item">
                    <div class="side-label">Kế hoạch gần nhất</div>
                    <div class="side-value">{{ $latestRecord?->treatment_plan ?: 'Chưa cập nhật.' }}</div>
                </div>

                <div class="side-item">
                    <div class="side-label">Tái khám</div>
                    <div class="side-value">
                        {{ $latestRecord?->follow_up_date ? $latestRecord->follow_up_date->format('d/m/Y') : 'Chưa có lịch tái khám.' }}
                    </div>
                </div>
            </div>

            <div class="record-card side-box">
                <div class="side-title">
                    <i class="ri-calendar-event-line"></i>
                    Lịch hẹn sắp tới
                </div>

                @if($upcomingAppointments->isEmpty())
                    <div class="side-item">
                        <div class="side-value">Bạn chưa có lịch hẹn sắp tới.</div>
                    </div>
                @else
                    @foreach($upcomingAppointments->take(3) as $appointment)
                        <div class="side-item">
                            <div class="side-label">
                                {{ $appointment->appointment_date?->format('d/m/Y H:i') }}
                            </div>
                            <div class="side-value">
                                {{ $appointment->service?->name ?? 'Dịch vụ khám' }}<br>
                                {{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}<br>
                                {{ $appointment->room?->name ?? 'Chưa gán phòng' }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </aside>
    </div>
</div>

<script>
    function switchPatientRecordTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(function (button) {
            button.classList.toggle('active', button.dataset.tab === tabName);
        });

        document.querySelectorAll('.tab-pane').forEach(function (pane) {
            pane.classList.toggle('active', pane.id === 'tab-' + tabName);
        });
    }

    function toggleVisit(button) {
        const item = button.closest('.visit-item');

        if (!item) {
            return;
        }

        item.classList.toggle('open');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('visitSearchInput');
        const visitList = document.getElementById('visitList');

        if (!searchInput || !visitList) {
            return;
        }

        searchInput.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();

            visitList.querySelectorAll('.visit-item').forEach(function (item) {
                const searchText = item.dataset.search || '';
                item.style.display = searchText.includes(keyword) ? '' : 'none';
            });
        });
    });
</script>
@endsection