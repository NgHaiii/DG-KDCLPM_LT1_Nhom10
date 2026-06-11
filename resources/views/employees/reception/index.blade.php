@extends('layouts.employee-layout')

@section('title', 'Tiếp nhận bệnh nhân')
@section('page-title', 'Tiếp nhận bệnh nhân')
@section('page-subtitle', 'Tiếp nhận lịch online và bệnh nhân khám trực tiếp trong ngày')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card,
    .panel,
    .action-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
    }

    .stat-card {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon,
    .action-icon {
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        font-size: 22px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .stat-value {
        font-family: var(--font-title);
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .action-card {
        padding: 22px;
        display: grid;
        grid-template-columns: 54px 1fr auto;
        gap: 16px;
        align-items: center;
    }

    .action-icon {
        width: 54px;
        height: 54px;
        font-size: 25px;
    }

    .action-title {
        font-family: var(--font-title);
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .action-desc {
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .panel {
        display: none;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .panel.active {
        display: block;
    }

    .panel-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-title);
        font-size: 17px;
        font-weight: 800;
        color: var(--text-main);
    }

    .panel-title i {
        color: var(--primary);
        font-size: 21px;
    }

    .panel-body {
        padding: 22px;
    }

    .date-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .form-control {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: white;
        color: var(--text-main);
        font-family: var(--font-body);
        font-size: 14px;
        outline: none;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-grid .form-group {
        margin-bottom: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 7px;
    }

    .required-mark {
        color: #ef4444;
    }

    .field-hint {
        margin-top: 6px;
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.4;
    }

    .alert-box {
        padding: 15px 18px;
        border-radius: var(--radius-md);
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.5;
    }

    .alert-error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .profile-note {
        padding: 13px 14px;
        border-radius: var(--radius-md);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .profile-note strong {
        color: var(--text-main);
    }

    .appointment-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .appointment-card {
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        background: #f8fafc;
        display: grid;
        grid-template-columns: 110px 1fr auto;
        gap: 16px;
        align-items: center;
    }

    .time-box {
        text-align: center;
        padding: 12px 10px;
        border-radius: var(--radius-md);
        background: white;
        border: 1px solid #e2e8f0;
    }

    .time-main {
        font-family: var(--font-title);
        font-size: 18px;
        font-weight: 800;
        color: var(--primary);
    }

    .time-sub {
        margin-top: 4px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .patient-name {
        font-size: 15px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 7px;
    }

    .meta-line {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .meta-line span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .offline-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(300px, 0.8fr);
        gap: 22px;
        align-items: start;
    }

    .hint-box {
        padding: 16px;
        border-radius: var(--radius-md);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .hint-title {
        color: var(--text-main);
        font-weight: 800;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .service-summary {
        display: none;
        margin-bottom: 16px;
        padding: 14px;
        border-radius: var(--radius-md);
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        font-size: 13px;
        line-height: 1.5;
    }

    .service-summary.active {
        display: block;
    }

    .empty-state {
        padding: 42px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        display: block;
        font-size: 42px;
        color: var(--primary);
        margin-bottom: 12px;
    }

    @media (max-width: 1100px) {
        .action-grid,
        .offline-grid,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .action-card,
        .appointment-card {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('header-actions')
<a href="{{ route('employees.patient-profiles.index') }}" class="btn btn-secondary">
    <i class="ri-folder-user-line"></i>
    Hồ sơ bệnh nhân
</a>

<a href="{{ route('employees.reception.queue') }}" class="btn btn-secondary">
    <i class="ri-list-check-3"></i>
    Danh sách khám
</a>
@endsection

@section('content')
@php
    $todayAppointments = $todayAppointments ?? collect();
    $completedAppointments = $completedAppointments ?? collect();
    $services = $services ?? collect();
    $patientProfiles = $patientProfiles ?? collect();
    $doctors = $doctors ?? collect();

    $confirmedAppointments = $todayAppointments->where('status', 'confirmed');
    $waitingAppointments = $todayAppointments->whereIn('status', ['checked_in', 'waiting', 'in_progress']);
@endphp

@if(session('success'))
    <div class="alert-box alert-success">
        <strong>Thành công:</strong> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-box alert-error">
        <strong>Lỗi:</strong> {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-box alert-error">
        <strong>Lỗi:</strong>
        <ul style="margin: 6px 0 0 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="ri-calendar-check-line"></i></div>
        <div>
            <div class="stat-label">Chờ tiếp nhận</div>
            <div class="stat-value">{{ $confirmedAppointments->count() }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class="ri-user-received-2-line"></i></div>
        <div>
            <div class="stat-label">Đang trong quy trình</div>
            <div class="stat-value">{{ $waitingAppointments->count() }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class="ri-check-double-line"></i></div>
        <div>
            <div class="stat-label">Đã hoàn thành</div>
            <div class="stat-value">{{ $completedAppointments->count() }}</div>
        </div>
    </div>
</div>

<div class="action-grid">
    <div class="action-card">
        <div class="action-icon">
            <i class="ri-calendar-check-line"></i>
        </div>
        <div>
            <div class="action-title">Lịch đặt online</div>
            <div class="action-desc">Tiếp nhận bệnh nhân đã được bác sĩ xác nhận khi đến phòng khám.</div>
        </div>
        <button type="button" class="btn btn-primary" onclick="showPanel('onlinePanel')">
            <i class="ri-folder-open-line"></i>
            Mở lịch
        </button>
    </div>

    <div class="action-card">
        <div class="action-icon">
            <i class="ri-user-add-line"></i>
        </div>
        <div>
            <div class="action-title">Khám trực tiếp</div>
            <div class="action-desc">Nhập nhanh thông tin bệnh nhân, chọn dịch vụ và đưa vào hàng chờ.</div>
        </div>
        <button type="button" class="btn btn-primary" onclick="showPanel('offlinePanel')">
            <i class="ri-add-circle-line"></i>
            Mở form
        </button>
    </div>
</div>

<section class="panel" id="onlinePanel">
    <div class="panel-header">
        <div class="panel-title">
            <i class="ri-calendar-event-line"></i>
            Lịch online chờ tiếp nhận
        </div>

        <form method="GET" action="{{ route('employees.reception') }}" class="date-form">
            <input type="hidden" name="panel" value="online">
            <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" class="form-control" style="width: 170px;">
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="ri-search-line"></i>
                Lọc
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="hidePanels()">
                <i class="ri-close-line"></i>
                Đóng
            </button>
        </form>
    </div>

    <div class="panel-body">
        @if($confirmedAppointments->isEmpty())
            <div class="empty-state">
                <i class="ri-calendar-check-line"></i>
                <h3>Không có lịch online chờ tiếp nhận</h3>
                <p>Các lịch đã được bác sĩ xác nhận trong ngày sẽ hiển thị tại đây.</p>
            </div>
        @else
            <div class="appointment-list">
                @foreach($confirmedAppointments as $appointment)
                    <div class="appointment-card">
                        <div class="time-box">
                            <div class="time-main">{{ $appointment->appointment_date?->format('H:i') ?? '-' }}</div>
                            <div class="time-sub">{{ $appointment->duration_minutes ?? 30 }} phút</div>
                        </div>

                        <div>
                            <div class="patient-name">{{ $appointment->patient_display_name }}</div>

                            <div class="meta-line">
                                <span><i class="ri-phone-line"></i>{{ $appointment->patient_display_phone }}</span>
                                <span><i class="ri-stethoscope-line"></i>{{ $appointment->service?->name ?? 'Dịch vụ' }}</span>
                                <span><i class="ri-user-heart-line"></i>{{ $appointment->doctor?->name ?? 'Bác sĩ' }}</span>
                                <span><i class="ri-door-open-line"></i>{{ $appointment->room?->name ?? 'Chưa có phòng' }}</span>
                                <span><i class="ri-global-line"></i>{{ $appointment->source_label }}</span>
                            </div>
                        </div>

                        <form method="POST"
                              action="{{ route('employees.reception.check-in', $appointment->id) }}"
                              onsubmit="return confirm('Tiếp nhận bệnh nhân online và chuyển sang trang in phiếu số thứ tự?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-user-received-2-line"></i>
                                Tiếp nhận
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="panel" id="offlinePanel">
    <div class="panel-header">
        <div class="panel-title">
            <i class="ri-user-add-line"></i>
            Tiếp nhận khám trực tiếp
        </div>

        <button type="button" class="btn btn-secondary btn-sm" onclick="hidePanels()">
            <i class="ri-close-line"></i>
            Đóng
        </button>
    </div>

    <div class="panel-body">
        <div class="offline-grid">
            <form method="POST"
                  action="{{ route('employees.reception.walk-in') }}"
                  id="offlineReceptionForm"
                  onsubmit="return validateWalkInForm();">
                @csrf

                <div class="profile-note">
                    <strong>Thông tin tiếp nhận:</strong>
                    Nếu bệnh nhân đã từng khám, chọn hồ sơ cũ. Nếu chưa có, nhập thông tin tối thiểu để tạo hồ sơ và in phiếu số thứ tự.
                </div>

                <div class="form-group">
                    <label class="form-label">Hồ sơ bệnh nhân cũ</label>
                    <select name="patient_profile_id" id="patientProfileSelect" class="form-control" onchange="handlePatientProfileChange(true)">
                        <option value="">-- Không chọn / tạo hồ sơ mới --</option>
                        @foreach($patientProfiles as $profile)
                            <option value="{{ $profile->id }}"
                                    data-name="{{ e($profile->full_name) }}"
                                    data-phone="{{ e($profile->phone) }}"
                                    data-dob="{{ $profile->dob ? $profile->dob->format('Y-m-d') : '' }}"
                                    data-gender="{{ e($profile->gender ?? '') }}"
                                    data-address="{{ e($profile->address ?? '') }}"
                                    @selected(old('patient_profile_id') == $profile->id)>
                                #{{ $profile->id }} - {{ $profile->full_name }} - {{ $profile->phone ?: 'Chưa có SĐT' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="field-hint">Không bắt buộc. Dùng khi bệnh nhân đã từng khám tại phòng khám.</div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Họ tên <span class="required-mark">*</span></label>
                        <input type="text" name="patient_name" id="patientNameInput" class="form-control"
                               value="{{ old('patient_name') }}" placeholder="Nhập họ tên bệnh nhân" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại <span class="required-mark">*</span></label>
                        <input type="tel" name="patient_phone" id="patientPhoneInput" class="form-control"
                               value="{{ old('patient_phone') }}" placeholder="VD: 0987456123" inputmode="tel" maxlength="30">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="patient_dob" id="patientDobInput" class="form-control"
                               value="{{ old('patient_dob') }}" max="{{ now()->toDateString() }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giới tính</label>
                        <select name="patient_gender" id="patientGenderInput" class="form-control">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" @selected(old('patient_gender') === 'Nam')>Nam</option>
                            <option value="Nữ" @selected(old('patient_gender') === 'Nữ')>Nữ</option>
                            <option value="Khác" @selected(old('patient_gender') === 'Khác')>Khác</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="patient_address" id="patientAddressInput" class="form-control"
                               value="{{ old('patient_address') }}" placeholder="Nhập địa chỉ liên hệ" maxlength="1000">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Dịch vụ khám <span class="required-mark">*</span></label>
                    <select name="service_id" id="serviceSelect" class="form-control" required onchange="handleServiceChange(false)">
                        <option value="">-- Chọn dịch vụ --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}"
                                    data-specialization="{{ e($service->required_specialization ?? '') }}"
                                    data-duration="{{ (int) ($service->actual_duration ?: $service->duration_minutes ?: 30) }}"
                                    data-room="{{ e($service->room?->name ?? 'Chưa gán phòng') }}"
                                    data-type="{{ e($service->type ?? '') }}"
                                    @selected(old('service_id') == $service->id)>
                                {{ $service->name }} - {{ $service->type ?? 'Không phân loại' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="service-summary" id="serviceSummary"></div>

                <div class="form-group">
                    <label class="form-label">Bác sĩ phù hợp <span class="required-mark">*</span></label>
                    <select name="doctor_id" id="doctorSelect" class="form-control" required disabled>
                        <option value="">-- Chọn dịch vụ trước --</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}"
                                    data-specialization="{{ e($doctor->specialization ?? '') }}"
                                    @selected(old('doctor_id') == $doctor->id)>
                                {{ $doctor->name }} - {{ $doctor->specialization ?? 'Chưa có chuyên khoa' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Triệu chứng / ghi chú ban đầu</label>
                    <textarea name="notes" class="form-control" rows="4" maxlength="500"
                              placeholder="Nhập lý do khám, triệu chứng ban đầu...">{{ old('notes') }}</textarea>
                    <div class="field-hint">Thông tin này được lưu theo lượt tiếp nhận, không thay thế hồ sơ bệnh án.</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="ri-printer-line"></i>
                    Tiếp nhận và in số thứ tự
                </button>
            </form>

            <aside class="hint-box">
                <div class="hint-title">
                    <i class="ri-information-line"></i>
                    Quy tắc tiếp nhận
                </div>
                <p>Thông tin chính của bệnh nhân sẽ lưu vào hồ sơ bệnh nhân để tra cứu lại về sau.</p>
                <p style="margin-top: 10px;">Phiếu số thứ tự không hiển thị trong màn hình này. Sau khi tiếp nhận thành công, hệ thống sẽ chuyển sang file phiếu riêng để in.</p>
                <p style="margin-top: 10px;">Bệnh nhân offline chỉ nên được nhận khi bác sĩ còn thời gian xử lý và không gây lấn lịch online kế tiếp.</p>
            </aside>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    const requestedPanel = new URLSearchParams(window.location.search).get('panel');
    const hasValidationErrors = @json($errors->any());
    const oldDoctorId = @json(old('doctor_id'));

    document.addEventListener('DOMContentLoaded', function () {
        if (hasValidationErrors) {
            showPanel('offlinePanel');
        } else if (requestedPanel === 'online') {
            showPanel('onlinePanel');
        }

        handlePatientProfileChange(false);
        handleServiceChange(true);
    });

    function showPanel(panelId) {
        hidePanels();

        const panel = document.getElementById(panelId);

        if (panel) {
            panel.classList.add('active');
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function hidePanels() {
        document.querySelectorAll('.panel').forEach(function (panel) {
            panel.classList.remove('active');
        });
    }

    function setValueIfAllowed(inputId, value, forceFill) {
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        if (value && (forceFill || !input.value)) {
            input.value = value;
        }
    }

    function handlePatientProfileChange(forceFill) {
        const profileSelect = document.getElementById('patientProfileSelect');

        if (!profileSelect) {
            return;
        }

        const selected = profileSelect.options[profileSelect.selectedIndex];

        if (!selected || !selected.value) {
            return;
        }

        setValueIfAllowed('patientNameInput', selected.dataset.name || '', forceFill);
        setValueIfAllowed('patientPhoneInput', selected.dataset.phone || '', forceFill);
        setValueIfAllowed('patientDobInput', selected.dataset.dob || '', forceFill);
        setValueIfAllowed('patientGenderInput', normalizeGenderForSelect(selected.dataset.gender || ''), forceFill);
        setValueIfAllowed('patientAddressInput', selected.dataset.address || '', forceFill);
    }

    function normalizeGenderForSelect(value) {
        const normalized = String(value || '').toLowerCase();

        if (normalized === 'male' || normalized === 'nam') {
            return 'Nam';
        }

        if (normalized === 'female' || normalized === 'nu' || normalized === 'nữ') {
            return 'Nữ';
        }

        if (normalized === 'other' || normalized === 'khac' || normalized === 'khác') {
            return 'Khác';
        }

        return value;
    }

    function handleServiceChange(isInitialLoad) {
        const serviceSelect = document.getElementById('serviceSelect');
        const doctorSelect = document.getElementById('doctorSelect');
        const summary = document.getElementById('serviceSummary');

        if (!serviceSelect || !doctorSelect || !summary) {
            return;
        }

        const selected = serviceSelect.options[serviceSelect.selectedIndex];
        const specialization = selected ? (selected.dataset.specialization || '') : '';
        const duration = selected ? (selected.dataset.duration || '') : '';
        const room = selected ? (selected.dataset.room || '') : '';
        const type = selected ? (selected.dataset.type || '') : '';

        doctorSelect.disabled = !serviceSelect.value;

        Array.from(doctorSelect.options).forEach(function (option) {
            if (!option.value) {
                option.textContent = serviceSelect.value ? '-- Chọn bác sĩ phù hợp --' : '-- Chọn dịch vụ trước --';
                option.hidden = false;
                return;
            }

            const doctorSpecialization = option.dataset.specialization || '';
            option.hidden = specialization !== '' && doctorSpecialization !== specialization;
        });

        if (isInitialLoad && oldDoctorId) {
            const oldOption = doctorSelect.querySelector('option[value="' + oldDoctorId + '"]');

            if (oldOption && !oldOption.hidden) {
                doctorSelect.value = oldDoctorId;
            }
        } else {
            doctorSelect.value = '';
        }

        if (serviceSelect.value) {
            summary.classList.add('active');
            summary.innerHTML =
                '<strong>Thông tin dịch vụ:</strong><br>' +
                'Loại dịch vụ: ' + escapeHtml(type || 'Chưa phân loại') + '<br>' +
                'Chuyên khoa yêu cầu: ' + escapeHtml(specialization || 'Chưa gán') + '<br>' +
                'Thời lượng dự kiến: ' + escapeHtml(duration || '30') + ' phút<br>' +
                'Phòng gợi ý: ' + escapeHtml(room || 'Chưa gán phòng');
        } else {
            summary.classList.remove('active');
            summary.innerHTML = '';
        }
    }

    function validateWalkInForm() {
        const profileId = document.getElementById('patientProfileSelect').value;
        const patientName = document.getElementById('patientNameInput').value.trim();
        const patientPhone = document.getElementById('patientPhoneInput').value.trim();
        const serviceId = document.getElementById('serviceSelect').value;
        const doctorId = document.getElementById('doctorSelect').value;

        if (!profileId && !patientName) {
            alert('Vui lòng nhập họ tên bệnh nhân hoặc chọn hồ sơ bệnh nhân cũ.');
            return false;
        }

        if (!profileId && !patientPhone) {
            alert('Vui lòng nhập số điện thoại bệnh nhân hoặc chọn hồ sơ bệnh nhân cũ.');
            return false;
        }

        if (patientPhone && !/^[0-9+\-\s]{8,30}$/.test(patientPhone)) {
            alert('Số điện thoại bệnh nhân không hợp lệ.');
            return false;
        }

        if (!serviceId) {
            alert('Vui lòng chọn dịch vụ khám.');
            return false;
        }

        if (!doctorId) {
            alert('Vui lòng chọn bác sĩ phù hợp.');
            return false;
        }

        return confirm('Tiếp nhận bệnh nhân khám trực tiếp và chuyển sang trang in phiếu số thứ tự?');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
</script>
@endsection 