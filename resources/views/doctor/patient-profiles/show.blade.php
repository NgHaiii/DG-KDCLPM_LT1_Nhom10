@extends('layouts.doctor-layout')

@section('title', 'Hồ sơ bệnh án')
@section('page-title', 'Hồ sơ bệnh án điện tử')
@section('page-subtitle', 'Quản lý hồ sơ bệnh nhân, nhật ký điều trị, sơ đồ răng và cận lâm sàng')

@section('styles')
<style>
    .ehr-shell { display: grid; gap: 18px; }
    .ehr-card {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .ehr-card-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 900;
        color: #0f172a;
    }
    .ehr-card-title-main { display: inline-flex; align-items: center; gap: 8px; }
    .ehr-card-body { padding: 18px; }

    .patient-top {
        display: grid;
        grid-template-columns: auto minmax(220px, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: 18px;
    }
    .patient-avatar {
        width: 74px;
        height: 74px;
        border-radius: 18px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 900;
    }
    .patient-name {
        font-size: 26px;
        line-height: 1.1;
        font-weight: 900;
        color: #0f172a;
    }
    .patient-sub {
        margin-top: 6px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        font-size: 14px;
    }
    .patient-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
    .patient-facts {
        display: grid;
        grid-template-columns: repeat(4, minmax(120px, 1fr));
        gap: 10px;
        padding: 0 18px 18px;
    }
    .fact-box {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 14px;
        padding: 12px;
        min-width: 0;
    }
    .fact-label {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 900;
        color: #64748b;
        margin-bottom: 4px;
    }
    .fact-value {
        color: #0f172a;
        font-weight: 900;
        font-size: 15px;
        word-break: break-word;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }
    .pill-blue { background: #e0f2fe; color: #0284c7; }
    .pill-green { background: #dcfce7; color: #15803d; }
    .pill-red { background: #fee2e2; color: #b91c1c; }
    .pill-orange { background: #ffedd5; color: #c2410c; }
    .pill-gray { background: #f1f5f9; color: #475569; }

    .top-actions { display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    .ehr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        border-radius: 12px;
        padding: 10px 13px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        transition: 0.15s ease;
        white-space: nowrap;
    }
    .ehr-btn:hover { background: #f8fafc; color: #0369a1; }
    .ehr-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .ehr-btn-primary { background: #0ea5e9; border-color: #0ea5e9; color: #fff; }
    .ehr-btn-primary:hover { background: #0284c7; color: #fff; }
    .ehr-btn-danger { background: #ef4444; border-color: #ef4444; color: #fff; }
    .ehr-btn-danger:hover { background: #dc2626; color: #fff; }
    .ehr-btn-sm { padding: 8px 10px; font-size: 12px; border-radius: 10px; }

    .ehr-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 18px;
        align-items: start;
    }
    .tabs {
        display: inline-flex;
        gap: 4px;
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
        color: #475569;
        font-weight: 900;
        cursor: pointer;
        border-radius: 12px;
        white-space: nowrap;
    }
    .tab-btn.active {
        color: #0f172a;
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    .odontogram-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 16px;
        align-items: start;
    }
    .tooth-chart { padding: 18px; }
    .tooth-board {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .tooth-board-inner { min-width: 640px; }
    .jaw-label-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        color: #475569;
        font-weight: 900;
        margin: 0 0 10px;
        font-size: 15px;
    }
    .jaw-label-row span:last-child { text-align: right; }
    .jaw-row {
        display: grid;
        grid-template-columns: repeat(16, 1fr);
        gap: 5px;
        margin-bottom: 18px;
    }
    .tooth { display: grid; gap: 4px; justify-items: center; }
    .tooth-box {
        width: 36px;
        height: 42px;
        border: 2px solid #d6e1e8;
        border-radius: 10px;
        background: #f8fcff;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: 0.15s ease;
    }
    .tooth-box::before {
        content: "";
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #dbe3ef;
        display: block;
    }
    .tooth-box:hover,
    .tooth-box.selected {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.14);
    }
    .tooth-number { font-size: 11px; color: #0f172a; font-weight: 900; }
    .tooth-box.healthy { background: #f8fcff; }
    .tooth-box.caries { border-color: #ef4444; background: #fff1f2; }
    .tooth-box.filled { border-color: #0891b2; background: #cffafe; }
    .tooth-box.crown { border-color: #eab308; background: #fef3c7; }
    .tooth-box.root_canal { border-color: #db2777; background: #fce7f3; }
    .tooth-box.missing { border-color: #64748b; background: #e2e8f0; }
    .tooth-box.missing::before { background: #cbd5e1; border-color: #64748b; }

    .tooth-editor { padding: 18px; }
    .selected-tooth-title {
        font-size: 21px;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .status-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
        margin-top: 10px;
    }
    .status-btn {
        border: 1px solid #dbe3ef;
        background: #f8fafc;
        color: #0f172a;
        border-radius: 11px;
        padding: 10px 8px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        font-size: 13px;
    }
    .status-btn.active {
        background: #0891b2;
        color: #fff;
        border-color: #0891b2;
    }
    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding-top: 14px;
        border-top: 1px solid #e2e8f0;
        color: #475569;
        font-size: 13px;
        font-weight: 800;
    }
    .legend span { display: inline-flex; align-items: center; gap: 7px; }
    .legend-mark {
        width: 14px;
        height: 14px;
        border: 2px solid;
        border-radius: 5px;
        display: inline-block;
        background: #fff;
    }

    .quick-panel {
        display: none;
        margin-top: 14px;
        border: 1px dashed #bae6fd;
        background: #f0f9ff;
        border-radius: 14px;
        padding: 14px;
    }
    .quick-panel.active { display: block; }

    .clinical-grid { display: grid; gap: 12px; }
    .clinical-field {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        background: #fff;
    }
    .clinical-label {
        font-size: 13px;
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 7px;
    }
    .clinical-value {
        color: #475569;
        line-height: 1.55;
        white-space: pre-line;
    }

    .side-stack { display: grid; gap: 14px; }
    .side-card { padding: 14px; }
    .side-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 12px;
    }
    .compact-info { display: grid; gap: 10px; }
    .compact-row {
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .compact-row:last-child { border-bottom: 0; padding-bottom: 0; }
    .compact-label {
        font-size: 11px;
        color: #2563eb;
        text-transform: uppercase;
        font-weight: 900;
        margin-bottom: 4px;
    }
    .compact-value {
        color: #0f172a;
        line-height: 1.45;
        white-space: pre-line;
        word-break: break-word;
    }
    .alert-soft {
        border: 1px solid;
        border-radius: 14px;
        padding: 12px;
        line-height: 1.5;
    }
    .alert-soft strong {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .alert-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .alert-warning { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }

    .plan-box,
    .appointment-box {
        border-radius: 14px;
        padding: 13px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
    }
    .plan-title {
        color: #2563eb;
        font-weight: 900;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .plan-text {
        color: #0f172a;
        font-size: 14px;
        line-height: 1.5;
        white-space: pre-line;
    }

    .timeline { position: relative; padding: 18px; }
    .timeline::before {
        content: "";
        position: absolute;
        left: 28px;
        top: 22px;
        bottom: 22px;
        width: 2px;
        background: #e0f2fe;
    }
    .visit-item {
        position: relative;
        padding-left: 36px;
        margin-bottom: 16px;
    }
    .visit-item:last-child { margin-bottom: 0; }
    .visit-dot {
        position: absolute;
        left: 4px;
        top: 8px;
        width: 14px;
        height: 14px;
        border-radius: 999px;
        background: #0ea5e9;
        border: 3px solid #dbeafe;
        z-index: 1;
    }
    .visit-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
    }
    .visit-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 8px;
    }
    .visit-date {
        font-size: 12px;
        color: #2563eb;
        font-weight: 900;
        text-transform: uppercase;
    }
    .visit-name {
        color: #0f172a;
        font-weight: 900;
        font-size: 15px;
        margin-top: 2px;
    }
    .visit-desc {
        color: #475569;
        font-size: 13px;
        line-height: 1.55;
        white-space: pre-line;
    }
    .visit-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
        color: #64748b;
        font-size: 12px;
    }

    .record-edit {
        display: none;
        margin-top: 14px;
        padding: 14px;
        border-radius: 14px;
        border: 1px dashed #bfdbfe;
        background: #f8fafc;
    }
    .record-edit.active { display: block; }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .form-group { display: grid; gap: 6px; }
    .form-group.full { grid-column: 1 / -1; }
    .form-label { font-weight: 900; color: #0f172a; font-size: 13px; }
    .form-control {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 11px 12px;
        color: #0f172a;
        outline: none;
        background: #fff;
    }
    textarea.form-control { min-height: 92px; resize: vertical; }
    .tooth-editor textarea.form-control { min-height: 86px; }
    .quick-panel textarea.form-control { min-height: 74px; }
    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.13);
    }

    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
    .image-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    .image-card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        display: block;
        background: #020617;
    }
    .image-info { padding: 12px; }
    .image-title {
        color: #0f172a;
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 4px;
    }
    .image-meta { color: #64748b; font-size: 12px; line-height: 1.45; }

    .empty-box {
        text-align: center;
        padding: 30px 18px;
        color: #64748b;
    }
    .empty-box i {
        color: #0ea5e9;
        font-size: 34px;
        display: block;
        margin-bottom: 8px;
    }
    .toast-note {
        position: fixed;
        right: 24px;
        bottom: 24px;
        background: #0f172a;
        color: #fff;
        padding: 12px 14px;
        border-radius: 12px;
        font-weight: 800;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.22);
        display: none;
        z-index: 9999;
    }
    .toast-note.active { display: block; }

    @media (min-width: 1440px) {
        .tooth-board-inner { min-width: 0; }
    }
    @media (max-width: 1280px) {
        .ehr-main-grid { grid-template-columns: 1fr; }
        .side-stack { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 1100px) {
        .odontogram-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
        .patient-top { grid-template-columns: auto 1fr; }
        .top-actions { grid-column: 1 / -1; justify-content: flex-start; }
        .patient-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .side-stack { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .patient-top { grid-template-columns: 1fr; }
        .patient-avatar { width: 64px; height: 64px; }
        .patient-facts,
        .form-grid { grid-template-columns: 1fr; }
        .status-options { grid-template-columns: 1fr; }
        .tooth-board-inner { min-width: 600px; }
    }
    @media print {
        .doctor-sidebar,
        .main-sidebar,
        .tabs,
        .top-actions,
        .ehr-btn,
        form,
        button,
        .tooth-editor,
        .quick-panel {
            display: none !important;
        }
        .ehr-main-grid,
        .odontogram-layout { display: block; }
        .ehr-card {
            box-shadow: none;
            border: 1px solid #cbd5e1;
            margin-bottom: 14px;
        }
    }
</style>
@endsection

@section('content')
@php
    $appointments = $patientProfile->appointments ?? collect();

    $sortedAppointments = $appointments
        ->sortByDesc(fn ($appointment) => optional($appointment->appointment_date)->timestamp ?? 0)
        ->values();

    $latestAppointment = $sortedAppointments->first();
    $latestRecord = $latestAppointment?->medicalRecord;

    $upcomingAppointment = $appointments
        ->filter(function ($appointment) {
            return $appointment->appointment_date
                && $appointment->appointment_date->gte(now())
                && in_array($appointment->status, ['pending', 'confirmed', 'checked_in', 'waiting', 'in_progress']);
        })
        ->sortBy(fn ($appointment) => optional($appointment->appointment_date)->timestamp ?? 0)
        ->first();

    $clinicalImages = $appointments
        ->flatMap(fn ($appointment) => $appointment->clinicalImages ?? collect())
        ->sortByDesc(fn ($image) => optional($image->taken_date)->timestamp ?? optional($image->created_at)->timestamp ?? 0)
        ->values();

    $displayName = $patientProfile->full_name ?: ('Bệnh nhân #' . $patientProfile->id);
    $profileCode = 'BN-' . str_pad($patientProfile->id, 5, '0', STR_PAD_LEFT);
    $genderLabel = $patientProfile->gender_label ?? $patientProfile->gender ?? 'Chưa cập nhật';
    $ageLabel = $patientProfile->dob ? $patientProfile->dob->age . ' tuổi' : 'Chưa cập nhật tuổi';
    $avatarText = mb_strtoupper(mb_substr($displayName, 0, 1));

    $dentalChartShowUrl = \Illuminate\Support\Facades\Route::has('doctor.patient-profiles.dental-chart.show')
        ? route('doctor.patient-profiles.dental-chart.show', $patientProfile->id)
        : null;

    $dentalChartStoreUrl = \Illuminate\Support\Facades\Route::has('doctor.patient-profiles.dental-chart.store')
        ? route('doctor.patient-profiles.dental-chart.store', $patientProfile->id)
        : null;
@endphp

<div class="ehr-shell">
    <div class="ehr-card">
        <div class="patient-top">
            <div class="patient-avatar">{{ $avatarText }}</div>

            <div>
                <div class="patient-name">{{ $displayName }}</div>

                <div class="patient-sub">
                    <span>ID: {{ $profileCode }}</span>
                    <span>·</span>
                    <span>{{ $ageLabel }}</span>
                    <span>·</span>
                    <span>{{ $genderLabel }}</span>
                </div>

                <div class="patient-badges">
                    <span class="pill pill-blue">
                        <i class="ri-drop-line"></i>
                        {{ $patientProfile->blood_type ?: 'Chưa có nhóm máu' }}
                    </span>

                    <span class="pill {{ $patientProfile->is_temporary ? 'pill-orange' : 'pill-green' }}">
                        <i class="ri-user-heart-line"></i>
                        {{ $patientProfile->source_label ?? ($patientProfile->source ?: 'Hồ sơ') }}
                    </span>

                    @if($patientProfile->allergies)
                        <span class="pill pill-red">
                            <i class="ri-alarm-warning-line"></i>
                            Có dị ứng
                        </span>
                    @endif
                </div>
            </div>

            <div class="top-actions">
                <button type="button" class="ehr-btn ehr-btn-primary" onclick="switchTab('edit')">
                    <i class="ri-edit-line"></i>
                    Chỉnh sửa hồ sơ
                </button>

                <button type="button" class="ehr-btn" onclick="window.print()">
                    <i class="ri-printer-line"></i>
                    In bệnh án
                </button>

                <button type="button" class="ehr-btn" onclick="switchTab('clinical'); openLatestRecordEdit();">
                    <i class="ri-add-line"></i>
                    Ghi chú khám mới
                </button>
            </div>
        </div>

        <div class="patient-facts">
            <div class="fact-box">
                <div class="fact-label">Số điện thoại</div>
                <div class="fact-value">{{ $patientProfile->phone ?: 'Chưa cập nhật' }}</div>
            </div>

            <div class="fact-box">
                <div class="fact-label">Ngày sinh</div>
                <div class="fact-value">{{ $patientProfile->dob ? $patientProfile->dob->format('d/m/Y') : 'Chưa cập nhật' }}</div>
            </div>

            <div class="fact-box">
                <div class="fact-label">Email</div>
                <div class="fact-value">{{ $patientProfile->email ?: 'Chưa cập nhật' }}</div>
            </div>

            <div class="fact-box">
                <div class="fact-label">CCCD</div>
                <div class="fact-value">{{ $patientProfile->identity_number ?: 'Chưa cập nhật' }}</div>
            </div>
        </div>
    </div>

    <div class="ehr-main-grid">
        <main>
            <div class="tabs">
                <button type="button" class="tab-btn active" data-tab="teeth" onclick="switchTab('teeth')">
                    Sơ đồ răng
                </button>

                <button type="button" class="tab-btn" data-tab="visits" onclick="switchTab('visits')">
                    Lịch sử điều trị
                </button>

                <button type="button" class="tab-btn" data-tab="clinical" onclick="switchTab('clinical')">
                    Ghi chú lâm sàng
                </button>

                <button type="button" class="tab-btn" data-tab="images" onclick="switchTab('images')">
                    X-quang & cận lâm sàng
                </button>

                <button type="button" class="tab-btn" data-tab="edit" onclick="switchTab('edit')">
                    Chỉnh sửa hồ sơ
                </button>
            </div>

            <div class="tab-pane active" id="tab-teeth">
                <div class="odontogram-layout">
                    <div class="ehr-card">
                        <div class="ehr-card-title">
                            <span class="ehr-card-title-main">
                                <i class="ri-hospital-line"></i>
                                Sơ đồ răng FDI
                            </span>

                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <button type="button" class="ehr-btn ehr-btn-sm" onclick="togglePanel('quick-note-panel')">
                                    <i class="ri-flashlight-line"></i>
                                    Ghi chú nhanh
                                </button>

                                <button type="button" class="ehr-btn ehr-btn-sm" onclick="togglePanel('tooth-history-panel')">
                                    <i class="ri-history-line"></i>
                                    Lịch sử thay đổi
                                </button>
                            </div>
                        </div>

                        <div class="tooth-chart">
                            <div class="tooth-board">
                                <div class="tooth-board-inner">
                                    <div class="jaw-label-row">
                                        <span>Hàm trên — Phải</span>
                                        <span>Hàm trên — Trái</span>
                                    </div>

                                    <div class="jaw-row">
                                        @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $tooth)
                                            <div class="tooth">
                                                <button type="button"
                                                        class="tooth-box healthy"
                                                        data-tooth="{{ $tooth }}"
                                                        onclick="selectTooth({{ $tooth }})"
                                                        title="Răng {{ $tooth }}"></button>
                                                <div class="tooth-number">{{ $tooth }}</div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="jaw-row">
                                        @foreach([48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38] as $tooth)
                                            <div class="tooth">
                                                <button type="button"
                                                        class="tooth-box healthy"
                                                        data-tooth="{{ $tooth }}"
                                                        onclick="selectTooth({{ $tooth }})"
                                                        title="Răng {{ $tooth }}"></button>
                                                <div class="tooth-number">{{ $tooth }}</div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="jaw-label-row" style="margin-top:4px;">
                                        <span>Hàm dưới — Phải</span>
                                        <span>Hàm dưới — Trái</span>
                                    </div>
                                </div>
                            </div>

                            <div class="legend">
                                <span><i class="legend-mark" style="border-color:#d6e1e8;"></i> Khỏe mạnh</span>
                                <span><i class="legend-mark" style="border-color:#ef4444;"></i> Sâu răng</span>
                                <span><i class="legend-mark" style="border-color:#0891b2;"></i> Đã trám</span>
                                <span><i class="legend-mark" style="border-color:#eab308;"></i> Bọc sứ</span>
                                <span><i class="legend-mark" style="border-color:#db2777;"></i> Điều trị tủy</span>
                                <span><i class="legend-mark" style="border-color:#64748b;background:#e2e8f0;"></i> Đã mất</span>
                            </div>

                            <div class="quick-panel" id="quick-note-panel">
                                <div class="form-group">
                                    <label class="form-label">Ghi chú nhanh cho sơ đồ răng</label>
                                    <textarea id="quickToothNote" class="form-control" placeholder="VD: Theo dõi răng 16, nghi sâu mặt nhai..."></textarea>
                                </div>

                                <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                                    <button type="button" class="ehr-btn ehr-btn-primary" id="quickNoteSaveBtn" onclick="saveQuickToothNote()">
                                        <i class="ri-save-line"></i>
                                        Lưu ghi chú nhanh
                                    </button>
                                </div>
                            </div>

                            <div class="quick-panel" id="tooth-history-panel">
                                <div class="clinical-label">Lịch sử cập nhật sơ đồ răng</div>
                                <div id="toothHistoryList" class="clinical-value">Đang tải lịch sử...</div>
                            </div>
                        </div>
                    </div>

                    <div class="ehr-card">
                        <div class="tooth-editor">
                            <div class="selected-tooth-title" id="selectedToothTitle">Răng 32</div>

                            <div class="clinical-label">Tình trạng</div>
                            <span class="pill pill-gray" id="selectedToothStatusLabel">Đang tải...</span>

                            <div style="margin-top:16px;">
                                <div class="clinical-label">Cập nhật chẩn đoán</div>

                                <div class="status-options">
                                    <button type="button" class="status-btn active" data-status="healthy" onclick="setToothStatus('healthy')">Khỏe mạnh</button>
                                    <button type="button" class="status-btn" data-status="caries" onclick="setToothStatus('caries')">Sâu răng</button>
                                    <button type="button" class="status-btn" data-status="filled" onclick="setToothStatus('filled')">Đã trám</button>
                                    <button type="button" class="status-btn" data-status="crown" onclick="setToothStatus('crown')">Bọc sứ</button>
                                    <button type="button" class="status-btn" data-status="root_canal" onclick="setToothStatus('root_canal')">Điều trị tủy</button>
                                    <button type="button" class="status-btn" data-status="missing" onclick="setToothStatus('missing')">Đã mất</button>
                                </div>
                            </div>

                            <div style="margin-top:16px;">
                                <label class="form-label">Ghi chú</label>
                                <textarea id="toothNote" class="form-control" placeholder="Ghi chú điều trị cho răng này..."></textarea>
                            </div>

                            <button type="button" class="ehr-btn ehr-btn-primary" id="toothSaveBtn" style="width:100%; margin-top:16px;" onclick="saveToothChange()">
                                <i class="ri-save-line"></i>
                                Lưu thay đổi
                            </button>

                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab-visits">
                <div class="ehr-card">
                    <div class="ehr-card-title">
                        <span class="ehr-card-title-main">
                            <i class="ri-time-line"></i>
                            Lịch sử điều trị
                        </span>

                        <button type="button" class="ehr-btn ehr-btn-sm ehr-btn-primary" onclick="switchTab('clinical'); openLatestRecordEdit();">
                            <i class="ri-add-line"></i>
                            Ghi nhận khám mới
                        </button>
                    </div>

                    @if($sortedAppointments->isEmpty())
                        <div class="empty-box">
                            <i class="ri-calendar-check-line"></i>
                            Chưa có lượt khám nào trong hồ sơ này.
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($sortedAppointments as $appointment)
                                @php
                                    $record = $appointment->medicalRecord;
                                @endphp

                                <div class="visit-item">
                                    <div class="visit-dot"></div>

                                    <div class="visit-box">
                                        <div class="visit-top">
                                            <div>
                                                <div class="visit-date">
                                                    {{ $appointment->appointment_date ? $appointment->appointment_date->format('d/m/Y H:i') : 'Chưa có ngày' }}
                                                </div>

                                                <div class="visit-name">
                                                    {{ $appointment->service?->name ?? 'Dịch vụ khám' }}
                                                </div>
                                            </div>

                                            <span class="pill {{ $appointment->status === 'completed' ? 'pill-green' : ($appointment->status === 'cancelled' ? 'pill-red' : 'pill-blue') }}">
                                                {{ $appointment->status_label ?? $appointment->status }}
                                            </span>
                                        </div>

                                        <div class="visit-desc">
                                            @if($record?->diagnosis)
                                                Chẩn đoán: {{ $record->diagnosis }}
                                            @elseif($appointment->notes)
                                                {{ $appointment->notes }}
                                            @else
                                                Chưa cập nhật nội dung khám.
                                            @endif
                                        </div>

                                        <div class="visit-meta">
                                            <span><i class="ri-user-star-line"></i> {{ $appointment->doctor?->name ?? 'Bác sĩ phụ trách' }}</span>
                                            <span><i class="ri-door-open-line"></i> {{ $appointment->room?->name ?? 'Chưa gán phòng' }}</span>
                                            <span><i class="ri-timer-line"></i> {{ $appointment->duration_minutes ?? 0 }} phút</span>
                                        </div>

                                        <div style="margin-top:12px;">
                                            <button type="button" class="ehr-btn ehr-btn-sm" onclick="switchTab('clinical'); toggleRecordEdit({{ $appointment->id }})">
                                                <i class="ri-edit-2-line"></i>
                                                Cập nhật bệnh án
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="tab-pane" id="tab-clinical">
                <div class="ehr-card">
                    <div class="ehr-card-title">
                        <span class="ehr-card-title-main">
                            <i class="ri-stethoscope-line"></i>
                            Ghi chú lâm sàng
                        </span>
                    </div>

                    <div class="ehr-card-body">
                        @if($sortedAppointments->isEmpty())
                            <div class="empty-box">
                                <i class="ri-file-list-3-line"></i>
                                Chưa có lượt khám để cập nhật bệnh án.
                            </div>
                        @else
                            <div class="clinical-grid">
                                @foreach($sortedAppointments as $appointment)
                                    @php
                                        $record = $appointment->medicalRecord;
                                    @endphp

                                    <div class="clinical-field">
                                        <div class="visit-top">
                                            <div>
                                                <div class="visit-date">
                                                    {{ $appointment->appointment_date ? $appointment->appointment_date->format('d/m/Y H:i') : 'Chưa có ngày' }}
                                                </div>

                                                <div class="visit-name">
                                                    {{ $appointment->service?->name ?? 'Dịch vụ khám' }}
                                                </div>
                                            </div>

                                            <button type="button" class="ehr-btn ehr-btn-sm" onclick="toggleRecordEdit({{ $appointment->id }})">
                                                <i class="ri-edit-line"></i>
                                                Chỉnh sửa
                                            </button>
                                        </div>

                                        <div class="clinical-grid" style="margin-top:12px;">
                                            <div class="clinical-field">
                                                <div class="clinical-label">Lý do khám / Triệu chứng chính</div>
                                                <div class="clinical-value">{{ $record?->chief_complaint ?: 'Chưa cập nhật' }}</div>
                                            </div>

                                            <div class="clinical-field">
                                                <div class="clinical-label">Khám lâm sàng / Tình trạng trong miệng</div>
                                                <div class="clinical-value">{{ $record?->clinical_findings ?: 'Chưa cập nhật' }}</div>
                                            </div>

                                            <div class="clinical-field">
                                                <div class="clinical-label">Chẩn đoán</div>
                                                <div class="clinical-value">{{ $record?->diagnosis ?: 'Chưa cập nhật' }}</div>
                                            </div>

                                            <div class="clinical-field">
                                                <div class="clinical-label">Kế hoạch điều trị</div>
                                                <div class="clinical-value">{{ $record?->treatment_plan ?: 'Chưa cập nhật' }}</div>
                                            </div>

                                            <div class="clinical-field">
                                                <div class="clinical-label">Đơn thuốc / Chỉ định</div>
                                                <div class="clinical-value">{{ $record?->prescription ?: 'Không có' }}</div>
                                            </div>
                                        </div>

                                        <div class="record-edit" id="record-edit-{{ $appointment->id }}">
                                            @if(\Illuminate\Support\Facades\Route::has('doctor.patient-profiles.medical-records.update'))
                                                <form method="POST" action="{{ route('doctor.patient-profiles.medical-records.update', $appointment->id) }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="form-grid">
                                                        <div class="form-group full">
                                                            <label class="form-label">Lý do khám / Triệu chứng chính</label>
                                                            <textarea name="chief_complaint" class="form-control">{{ old('chief_complaint', $record?->chief_complaint) }}</textarea>
                                                        </div>

                                                        <div class="form-group full">
                                                            <label class="form-label">Khám lâm sàng / Tình trạng trong miệng</label>
                                                            <textarea name="clinical_findings" class="form-control">{{ old('clinical_findings', $record?->clinical_findings) }}</textarea>
                                                        </div>

                                                        <div class="form-group full">
                                                            <label class="form-label">Chẩn đoán</label>
                                                            <textarea name="diagnosis" class="form-control">{{ old('diagnosis', $record?->diagnosis) }}</textarea>
                                                        </div>

                                                        <div class="form-group full">
                                                            <label class="form-label">Kế hoạch điều trị</label>
                                                            <textarea name="treatment_plan" class="form-control">{{ old('treatment_plan', $record?->treatment_plan) }}</textarea>
                                                        </div>

                                                        <div class="form-group full">
                                                            <label class="form-label">Đơn thuốc / Chỉ định</label>
                                                            <textarea name="prescription" class="form-control">{{ old('prescription', $record?->prescription) }}</textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="form-label">Ngày tái khám</label>
                                                            <input type="date"
                                                                   name="follow_up_date"
                                                                   class="form-control"
                                                                   value="{{ old('follow_up_date', $record?->follow_up_date ? $record->follow_up_date->format('Y-m-d') : '') }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="form-label">Ghi chú bác sĩ</label>
                                                            <input type="text"
                                                                   name="doctor_notes"
                                                                   class="form-control"
                                                                   value="{{ old('doctor_notes', $record?->doctor_notes) }}">
                                                        </div>
                                                    </div>

                                                    <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                                                        <button type="submit" class="ehr-btn ehr-btn-primary">
                                                            <i class="ri-save-line"></i>
                                                            Lưu bệnh án
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="alert-soft alert-warning">
                                                    <strong>Thiếu route cập nhật bệnh án</strong>
                                                    Cần tạo route `doctor.patient-profiles.medical-records.update`.
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

            <div class="tab-pane" id="tab-images">
                <div class="ehr-card">
                    <div class="ehr-card-title">
                        <span class="ehr-card-title-main">
                            <i class="ri-image-2-line"></i>
                            X-quang & cận lâm sàng
                        </span>
                    </div>

                    <div class="ehr-card-body">
                        @if($latestAppointment && \Illuminate\Support\Facades\Route::has('doctor.patient-profiles.clinical-images.store'))
                            <form method="POST"
                                  action="{{ route('doctor.patient-profiles.clinical-images.store', $latestAppointment->id) }}"
                                  enctype="multipart/form-data"
                                  style="margin-bottom:18px;">
                                @csrf

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Ngày chụp</label>
                                        <input type="date"
                                               name="taken_date"
                                               class="form-control"
                                               value="{{ now()->format('Y-m-d') }}"
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Loại ảnh</label>
                                        <select name="image_type" class="form-control" required>
                                            <option value="xray">X-quang</option>
                                            <option value="panorama">Panorama</option>
                                            <option value="intraoral">Ảnh trong miệng</option>
                                            <option value="clinical">Ảnh lâm sàng</option>
                                            <option value="other">Khác</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Tiêu đề</label>
                                        <input type="text"
                                               name="title"
                                               class="form-control"
                                               placeholder="VD: X-quang toàn cảnh">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">File ảnh</label>
                                        <input type="file"
                                               name="image"
                                               class="form-control"
                                               accept="image/*"
                                               required>
                                    </div>

                                    <div class="form-group full">
                                        <label class="form-label">Ghi chú ảnh</label>
                                        <textarea name="notes" class="form-control" placeholder="Nhận xét nhanh về hình ảnh..."></textarea>
                                    </div>
                                </div>

                                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                                    <button type="submit" class="ehr-btn ehr-btn-primary">
                                        <i class="ri-upload-cloud-line"></i>
                                        Tải ảnh lên hồ sơ
                                    </button>
                                </div>
                            </form>
                        @elseif(!$latestAppointment)
                            <div class="alert-soft alert-warning">
                                <strong>Chưa có lượt khám</strong>
                                Cần có ít nhất một lượt khám để gắn ảnh cận lâm sàng.
                            </div>
                        @else
                            <div class="alert-soft alert-warning">
                                <strong>Thiếu chức năng upload ảnh</strong>
                                Cần tạo route `doctor.patient-profiles.clinical-images.store`.
                            </div>
                        @endif

                        @if($clinicalImages->isEmpty())
                            <div class="empty-box">
                                <i class="ri-image-add-line"></i>
                                Chưa có ảnh X-quang/cận lâm sàng trong hồ sơ này.
                            </div>
                        @else
                            <div class="image-grid">
                                @foreach($clinicalImages as $image)
                                    <div class="image-card">
                                        <a href="{{ asset('storage/' . $image->file_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $image->file_path) }}" alt="{{ $image->title ?: 'Ảnh cận lâm sàng' }}">
                                        </a>

                                        <div class="image-info">
                                            <div class="image-title">{{ $image->title ?: $image->image_type_label }}</div>

                                            <div class="image-meta">
                                                {{ $image->image_type_label ?? $image->image_type }}
                                                @if($image->taken_date)
                                                    · {{ $image->taken_date->format('d/m/Y') }}
                                                @endif
                                            </div>

                                            @if($image->notes)
                                                <div class="image-meta" style="margin-top:6px;">{{ $image->notes }}</div>
                                            @endif

                                            @if(\Illuminate\Support\Facades\Route::has('doctor.patient-profiles.clinical-images.destroy'))
                                                <form method="POST"
                                                      action="{{ route('doctor.patient-profiles.clinical-images.destroy', $image->id) }}"
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa ảnh này khỏi hồ sơ?')"
                                                      style="margin-top:10px;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="ehr-btn ehr-btn-sm ehr-btn-danger">
                                                        <i class="ri-delete-bin-line"></i>
                                                        Xóa ảnh
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab-edit">
                <div class="ehr-card">
                    <div class="ehr-card-title">
                        <span class="ehr-card-title-main">
                            <i class="ri-edit-box-line"></i>
                            Chỉnh sửa hồ sơ bệnh nhân
                        </span>
                    </div>

                    <div class="ehr-card-body">
                        @if(\Illuminate\Support\Facades\Route::has('doctor.patient-profiles.update'))
                            <form method="POST" action="{{ route('doctor.patient-profiles.update', $patientProfile->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Họ tên</label>
                                        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $patientProfile->full_name) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $patientProfile->phone) }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $patientProfile->email) }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Ngày sinh</label>
                                        <input type="date" name="dob" class="form-control" value="{{ old('dob', $patientProfile->dob ? $patientProfile->dob->format('Y-m-d') : '') }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Giới tính</label>
                                        <select name="gender" class="form-control">
                                            <option value="">-- Chọn giới tính --</option>
                                            <option value="male" @selected(old('gender', $patientProfile->gender) === 'male' || old('gender', $patientProfile->gender) === 'Nam')>Nam</option>
                                            <option value="female" @selected(old('gender', $patientProfile->gender) === 'female' || old('gender', $patientProfile->gender) === 'Nữ')>Nữ</option>
                                            <option value="other" @selected(old('gender', $patientProfile->gender) === 'other')>Khác</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Nhóm máu</label>
                                        <input type="text" name="blood_type" class="form-control" value="{{ old('blood_type', $patientProfile->blood_type) }}" placeholder="VD: O+, A-, B+">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">CCCD</label>
                                        <input type="text" name="identity_number" class="form-control" value="{{ old('identity_number', $patientProfile->identity_number) }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Nghề nghiệp</label>
                                        <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $patientProfile->occupation) }}">
                                    </div>

                                    <div class="form-group full">
                                        <label class="form-label">Địa chỉ</label>
                                        <input type="text" name="address" class="form-control" value="{{ old('address', $patientProfile->address) }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Tên người liên hệ khẩn cấp</label>
                                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $patientProfile->emergency_contact_name) }}">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">SĐT liên hệ khẩn cấp</label>
                                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $patientProfile->emergency_contact_phone) }}">
                                    </div>

                                    <div class="form-group full">
                                        <label class="form-label">Dị ứng / phản ứng thuốc</label>
                                        <textarea name="allergies" class="form-control">{{ old('allergies', $patientProfile->allergies) }}</textarea>
                                    </div>

                                    <div class="form-group full">
                                        <label class="form-label">Tiền sử bệnh lý toàn thân</label>
                                        <textarea name="medical_history" class="form-control">{{ old('medical_history', $patientProfile->medical_history) }}</textarea>
                                    </div>

                                    <div class="form-group full">
                                        <label class="form-label">Thuốc đang sử dụng</label>
                                        <textarea name="current_medications" class="form-control">{{ old('current_medications', $patientProfile->current_medications) }}</textarea>
                                    </div>

                                    <div class="form-group full">
                                        <label class="form-label">Tiền sử nha khoa</label>
                                        <textarea name="dental_history" class="form-control">{{ old('dental_history', $patientProfile->dental_history) }}</textarea>
                                    </div>
                                </div>

                                <div style="margin-top:16px; display:flex; justify-content:flex-end;">
                                    <button type="submit" class="ehr-btn ehr-btn-primary">
                                        <i class="ri-save-line"></i>
                                        Lưu hồ sơ
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="alert-soft alert-warning">
                                <strong>Thiếu route cập nhật hồ sơ</strong>
                                Cần tạo route `doctor.patient-profiles.update`.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>

        <aside class="side-stack">
            <div class="ehr-card side-card">
                <div class="side-title">
                    <i class="ri-profile-line"></i>
                    Thông tin quan trọng
                </div>

                <div class="compact-info">
                    @if($patientProfile->allergies)
                        <div class="alert-soft alert-danger">
                            <strong>Dị ứng / phản ứng thuốc</strong>
                            {{ $patientProfile->allergies }}
                        </div>
                    @endif

                    @if($patientProfile->medical_history)
                        <div class="alert-soft alert-warning">
                            <strong>Tiền sử bệnh lý</strong>
                            {{ $patientProfile->medical_history }}
                        </div>
                    @endif

                    <div class="compact-row">
                        <div class="compact-label">Địa chỉ</div>
                        <div class="compact-value">{{ $patientProfile->address ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="compact-row">
                        <div class="compact-label">Nghề nghiệp</div>
                        <div class="compact-value">{{ $patientProfile->occupation ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="compact-row">
                        <div class="compact-label">Thuốc đang sử dụng</div>
                        <div class="compact-value">{{ $patientProfile->current_medications ?: 'Chưa cập nhật' }}</div>
                    </div>

                    <div class="compact-row">
                        <div class="compact-label">Tiền sử nha khoa</div>
                        <div class="compact-value">{{ $patientProfile->dental_history ?: 'Chưa cập nhật' }}</div>
                    </div>
                </div>
            </div>

            <div class="ehr-card side-card">
                <div class="side-title">
                    <i class="ri-line-chart-line"></i>
                    Tiến trình điều trị
                </div>

                <div class="compact-info">
                    <div class="plan-box">
                        <div class="plan-title">Kế hoạch gần nhất</div>
                        <div class="plan-text">{{ $latestRecord?->treatment_plan ?: 'Chưa có kế hoạch điều trị.' }}</div>
                    </div>

                    <div class="plan-box">
                        <div class="plan-title">Chẩn đoán gần nhất</div>
                        <div class="plan-text">{{ $latestRecord?->diagnosis ?: 'Chưa cập nhật chẩn đoán.' }}</div>
                    </div>

                    @if($latestRecord?->follow_up_date)
                        <div class="plan-box">
                            <div class="plan-title">Tái khám</div>
                            <div class="plan-text">{{ $latestRecord->follow_up_date->format('d/m/Y') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="ehr-card side-card">
                <div class="side-title">
                    <i class="ri-calendar-event-line"></i>
                    Lịch hẹn sắp tới
                </div>

                @if($upcomingAppointment)
                    <div class="appointment-box">
                        <div class="visit-date">
                            {{ $upcomingAppointment->appointment_date?->format('d/m/Y') }}
                            · {{ $upcomingAppointment->appointment_date?->format('H:i') }}
                        </div>

                        <div class="visit-name" style="margin-top:6px;">
                            {{ $upcomingAppointment->service?->name ?? 'Dịch vụ khám' }}
                        </div>

                        <div class="visit-meta">
                            <span><i class="ri-user-star-line"></i> {{ $upcomingAppointment->doctor?->name ?? 'Bác sĩ' }}</span>
                            <span><i class="ri-door-open-line"></i> {{ $upcomingAppointment->room?->name ?? 'Chưa gán phòng' }}</span>
                            <span><i class="ri-time-line"></i> {{ $upcomingAppointment->duration_minutes ?? 0 }} phút</span>
                        </div>
                    </div>
                @else
                    <div class="empty-box">
                        <i class="ri-calendar-line"></i>
                        Chưa có lịch hẹn sắp tới.
                    </div>
                @endif
            </div>

            <div class="ehr-card side-card">
                <div class="side-title">
                    <i class="ri-image-line"></i>
                    Ảnh mới nhất
                </div>

                @if($clinicalImages->isEmpty())
                    <div class="empty-box">
                        <i class="ri-image-add-line"></i>
                        Chưa có ảnh cận lâm sàng.
                    </div>
                @else
                    <div class="image-grid" style="grid-template-columns:1fr;">
                        @foreach($clinicalImages->take(2) as $image)
                            <div class="image-card">
                                <a href="{{ asset('storage/' . $image->file_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $image->file_path) }}" alt="{{ $image->title ?: 'Ảnh cận lâm sàng' }}">
                                </a>

                                <div class="image-info">
                                    <div class="image-title">{{ $image->title ?: $image->image_type_label }}</div>

                                    <div class="image-meta">
                                        {{ $image->image_type_label ?? $image->image_type }}
                                        @if($image->taken_date)
                                            · {{ $image->taken_date->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>

<div class="toast-note" id="ehrToast">Đã lưu thay đổi.</div>

<script>
    const csrfToken = @json(csrf_token());
    const dentalChartShowUrl = @json($dentalChartShowUrl);
    const dentalChartStoreUrl = @json($dentalChartStoreUrl);

    const toothStatusLabels = {
        healthy: 'Khỏe mạnh',
        caries: 'Sâu răng',
        filled: 'Đã trám',
        crown: 'Bọc sứ',
        root_canal: 'Điều trị tủy',
        missing: 'Đã mất'
    };

    let selectedTooth = 32;
    let selectedStatus = 'healthy';
    let chartData = {};
    let chartHistory = [];
    let isSavingDentalChart = false;

    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(function (button) {
            button.classList.toggle('active', button.dataset.tab === tabName);
        });

        document.querySelectorAll('.tab-pane').forEach(function (pane) {
            pane.classList.toggle('active', pane.id === 'tab-' + tabName);
        });
    }

    function togglePanel(panelId) {
        const panel = document.getElementById(panelId);

        if (panel) {
            panel.classList.toggle('active');
        }

        if (panelId === 'tooth-history-panel') {
            renderToothHistory();
        }
    }

    function toggleRecordEdit(appointmentId) {
        const section = document.getElementById('record-edit-' + appointmentId);

        if (section) {
            section.classList.toggle('active');
            section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function openLatestRecordEdit() {
        const firstEdit = document.querySelector('.record-edit');

        if (firstEdit && !firstEdit.classList.contains('active')) {
            firstEdit.classList.add('active');
        }

        if (firstEdit) {
            firstEdit.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    async function loadDentalChart() {
        if (!dentalChartShowUrl) {
            setDentalChartActionsDisabled(true);
            showToast('Thiếu route tải sơ đồ răng. Vui lòng kiểm tra route dental-chart.show.');
            applyToothClasses();
            selectTooth(selectedTooth);
            renderToothHistory();
            return;
        }

        setDentalChartActionsDisabled(true);

        try {
            const response = await fetch(dentalChartShowUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await parseJsonResponse(response);

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Không tải được sơ đồ răng từ database.');
            }

            chartData = data.teeth || data.data?.teeth || {};
            chartHistory = data.history || data.data?.history || [];

            applyToothClasses();
            selectTooth(selectedTooth);
            renderToothHistory();
        } catch (error) {
            chartData = {};
            chartHistory = [];

            applyToothClasses();
            selectTooth(selectedTooth);
            renderToothHistory();

            showToast(error.message || 'Không tải được sơ đồ răng từ database.');
        } finally {
            setDentalChartActionsDisabled(false);
        }
    }

    function selectTooth(toothNumber) {
        selectedTooth = Number(toothNumber);

        const tooth = chartData[String(selectedTooth)] || {
            status: 'healthy',
            note: ''
        };

        selectedStatus = tooth.status || 'healthy';

        document.querySelectorAll('.tooth-box').forEach(function (button) {
            button.classList.toggle('selected', Number(button.dataset.tooth) === selectedTooth);
        });

        document.getElementById('selectedToothTitle').textContent = 'Răng ' + selectedTooth;
        document.getElementById('selectedToothStatusLabel').textContent = toothStatusLabels[selectedStatus] || 'Khỏe mạnh';
        document.getElementById('toothNote').value = tooth.note || '';

        document.querySelectorAll('.status-btn').forEach(function (button) {
            button.classList.toggle('active', button.dataset.status === selectedStatus);
        });
    }

    function setToothStatus(status) {
        selectedStatus = status;

        document.querySelectorAll('.status-btn').forEach(function (button) {
            button.classList.toggle('active', button.dataset.status === status);
        });

        document.getElementById('selectedToothStatusLabel').textContent = toothStatusLabels[status] || 'Khỏe mạnh';
    }

    function applyToothClasses() {
        document.querySelectorAll('.tooth-box').forEach(function (button) {
            const toothNumber = String(button.dataset.tooth);
            const tooth = chartData[toothNumber] || { status: 'healthy' };

            button.classList.remove('healthy', 'caries', 'filled', 'crown', 'root_canal', 'missing');
            button.classList.add(tooth.status || 'healthy');
        });
    }

    async function saveToothChange() {
        if (!dentalChartStoreUrl) {
            showToast('Thiếu route lưu sơ đồ răng. Vui lòng kiểm tra route dental-chart.store.');
            return;
        }

        if (isSavingDentalChart) {
            return;
        }

        const note = document.getElementById('toothNote').value.trim();

        await syncDentalChart({
            type: 'tooth',
            tooth_number: String(selectedTooth),
            status: selectedStatus,
            note: note
        });
    }

    async function saveQuickToothNote() {
        if (!dentalChartStoreUrl) {
            showToast('Thiếu route lưu ghi chú sơ đồ răng. Vui lòng kiểm tra route dental-chart.store.');
            return;
        }

        if (isSavingDentalChart) {
            return;
        }

        const input = document.getElementById('quickToothNote');
        const note = input.value.trim();

        if (!note) {
            showToast('Vui lòng nhập ghi chú nhanh.');
            return;
        }

        const saved = await syncDentalChart({
            type: 'quick_note',
            note: note
        });

        if (saved) {
            input.value = '';
        }
    }

    async function syncDentalChart(payload) {
        isSavingDentalChart = true;
        setDentalChartActionsDisabled(true);

        try {
            const response = await fetch(dentalChartStoreUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await parseJsonResponse(response);

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Không thể lưu sơ đồ răng vào database.');
            }

            chartData = data.teeth || data.data?.teeth || {};
            chartHistory = data.history || data.data?.history || [];

            applyToothClasses();
            selectTooth(selectedTooth);
            renderToothHistory();

            showToast(data.message || 'Đã lưu sơ đồ răng vào database.');
            return true;
        } catch (error) {
            showToast(error.message || 'Không thể lưu sơ đồ răng vào database.');
            return false;
        } finally {
            isSavingDentalChart = false;
            setDentalChartActionsDisabled(false);
        }
    }

    async function parseJsonResponse(response) {
        const text = await response.text();

        if (!text) {
            return {};
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            return {
                success: false,
                message: 'Server không trả về JSON hợp lệ. Vui lòng kiểm tra controller/route.'
            };
        }
    }

    function setDentalChartActionsDisabled(disabled) {
        const saveBtn = document.getElementById('toothSaveBtn');
        const quickBtn = document.getElementById('quickNoteSaveBtn');

        if (saveBtn) {
            saveBtn.disabled = disabled;
            saveBtn.innerHTML = disabled
                ? '<i class="ri-loader-4-line"></i> Đang xử lý...'
                : '<i class="ri-save-line"></i> Lưu thay đổi';
        }

        if (quickBtn) {
            quickBtn.disabled = disabled;
            quickBtn.innerHTML = disabled
                ? '<i class="ri-loader-4-line"></i> Đang xử lý...'
                : '<i class="ri-save-line"></i> Lưu ghi chú nhanh';
        }
    }

    function renderToothHistory() {
        const holder = document.getElementById('toothHistoryList');

        if (!holder) {
            return;
        }

        if (!chartHistory.length) {
            holder.innerHTML = 'Chưa có thay đổi nào.';
            return;
        }

        holder.innerHTML = chartHistory.map(function (item) {
            const label = item.status_label
                || (item.status === 'note' ? 'Ghi chú nhanh' : (toothStatusLabels[item.status] || item.status || item.action_label || 'Cập nhật'));

            return `
                <div style="padding:10px 0;border-bottom:1px dashed #cbd5e1;">
                    <strong>${escapeHtml(item.tooth || 'Ghi chú chung')}</strong> · ${escapeHtml(label)}
                    <div style="color:#64748b;font-size:12px;margin-top:3px;">${escapeHtml(item.time || '')}</div>
                    ${item.doctor_name ? `<div style="color:#64748b;font-size:12px;margin-top:3px;">Bác sĩ: ${escapeHtml(item.doctor_name)}</div>` : ''}
                    ${item.note ? `<div style="margin-top:4px;">${escapeHtml(item.note)}</div>` : ''}
                </div>
            `;
        }).join('');
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function showToast(message) {
        const toast = document.getElementById('ehrToast');

        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add('active');

        setTimeout(function () {
            toast.classList.remove('active');
        }, 2600);
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadDentalChart();
    });
</script>
@endsection