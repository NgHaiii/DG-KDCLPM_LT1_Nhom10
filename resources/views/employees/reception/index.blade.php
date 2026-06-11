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

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
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
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
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

    .form-group {
        margin-bottom: 15px;
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

    .ticket-print-area {
        display: none;
    }

    @media (max-width: 1100px) {
        .action-grid,
        .offline-grid {
            grid-template-columns: 1fr;
        }

        .action-card,
        .appointment-card {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .ticket-print-area,
        .ticket-print-area * {
            visibility: visible;
        }

        .ticket-print-area {
            display: block;
            position: absolute;
            inset: 0;
            width: 80mm;
            padding: 12px;
            background: white;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        .print-ticket {
            text-align: center;
            border: 1px dashed #111827;
            padding: 12px;
        }

        .ticket-brand {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .ticket-type {
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .ticket-number {
            font-size: 44px;
            font-weight: 900;
            line-height: 1;
            margin: 10px 0;
        }

        .ticket-info {
            text-align: left;
            font-size: 12px;
            line-height: 1.5;
            margin-top: 10px;
        }

        .ticket-footer {
            margin-top: 10px;
            font-size: 11px;
        }
    }
</style>
@endsection

@section('header-actions')
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
    $patients = $patients ?? collect();
    $doctors = $doctors ?? collect();

    $confirmedAppointments = $todayAppointments->where('status', 'confirmed');
    $waitingAppointments = $todayAppointments->whereIn('status', ['checked_in', 'waiting', 'in_progress']);
    $printTicket = session('print_ticket');
@endphp

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
            <div class="action-desc">Mở danh sách bệnh nhân đã được bác sĩ xác nhận để tiếp nhận khi đến phòng khám.</div>
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
            <div class="action-desc">Tiếp nhận bệnh nhân đến trực tiếp, chọn dịch vụ và bác sĩ phù hợp trước khi đưa vào hàng chờ.</div>
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
                            <div class="time-main">{{ $appointment->appointment_date->format('H:i') }}</div>
                            <div class="time-sub">{{ $appointment->duration_minutes ?? 30 }} phút</div>
                        </div>

                        <div>
                            <div class="patient-name">
                                {{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}
                            </div>

                            <div class="meta-line">
                                <span><i class="ri-stethoscope-line"></i>{{ $appointment->service?->name ?? 'Dịch vụ' }}</span>
                                <span><i class="ri-user-heart-line"></i>{{ $appointment->doctor?->name ?? 'Bác sĩ' }}</span>
                                <span><i class="ri-door-open-line"></i>{{ $appointment->room?->name ?? 'Chưa có phòng' }}</span>
                                <span><i class="ri-global-line"></i>Lịch online</span>
                            </div>
                        </div>

                        <div>
                            <form method="POST" action="{{ route('employees.reception.check-in', $appointment->id) }}"
                                  onsubmit="return confirm('Tiếp nhận bệnh nhân online và in phiếu số thứ tự?');">
                                @csrf
                                <input type="hidden" name="print_ticket" value="1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ri-user-received-2-line"></i>
                                    Tiếp nhận
                                </button>
                            </form>
                        </div>
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
            <form method="POST" action="{{ route('employees.reception.walk-in') }}" id="offlineReceptionForm">
                @csrf
                <input type="hidden" name="print_ticket" value="1">

                <div class="form-group">
                    <label class="form-label">
                        Bệnh nhân <span class="required-mark">*</span>
                    </label>
                    <select name="patient_id" id="patientSelect" class="form-control" required onchange="handlePatientChange(true)">
                        <option value="">-- Chọn bệnh nhân --</option>
                        @foreach($patients as $patient)
                            @php
                                $patientPhone = $patient->phone
                                    ?? $patient->phone_number
                                    ?? $patient->tel
                                    ?? '';
                            @endphp

                            <option value="{{ $patient->id }}"
                                    data-phone="{{ e($patientPhone) }}"
                                    @selected(old('patient_id') == $patient->id)>
                                {{ $patient->name }}{{ $patient->email ? ' - ' . $patient->email : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="field-hint">
                        Nếu bệnh nhân đã có SĐT trong hồ sơ, hệ thống sẽ tự điền xuống ô bên dưới.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Số điện thoại bệnh nhân <span class="required-mark">*</span>
                    </label>
                    <input type="tel"
                           name="patient_phone"
                           id="patientPhoneInput"
                           class="form-control"
                           value="{{ old('patient_phone') }}"
                           placeholder="VD: 0987456123"
                           inputmode="tel"
                           required>
                    <div class="field-hint">
                        Số điện thoại này sẽ được lưu kèm phiếu tiếp nhận offline và hiển thị trên phiếu STT.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Dịch vụ khám <span class="required-mark">*</span>
                    </label>
                    <select name="service_id" id="serviceSelect" class="form-control" required onchange="handleServiceChange()">
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
                    <label class="form-label">
                        Bác sĩ phù hợp <span class="required-mark">*</span>
                    </label>
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
                    <textarea name="notes"
                              class="form-control"
                              rows="4"
                              placeholder="Nhập lý do khám, triệu chứng ban đầu...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit"
                        class="btn btn-primary"
                        style="width: 100%;"
                        onclick="return confirm('Tiếp nhận bệnh nhân khám trực tiếp và in phiếu số thứ tự?');">
                    <i class="ri-printer-line"></i>
                    Tiếp nhận và in số thứ tự
                </button>
            </form>

            <aside class="hint-box">
                <div class="hint-title">
                    <i class="ri-information-line"></i>
                    Quy tắc tiếp nhận khám trực tiếp
                </div>
                <p>Bệnh nhân offline chỉ được nhận khi bác sĩ đang trong ca làm việc và khoảng trống không có nguy cơ lấn lịch online kế tiếp.</p>
                <p style="margin-top: 10px;">Sau khi tiếp nhận, hệ thống đưa bệnh nhân vào hàng chờ, cấp số thứ tự và in phiếu dành riêng cho khám trực tiếp.</p>
            </aside>
        </div>
    </div>
</section>

@if($printTicket)
    <div class="ticket-print-area" id="ticketPrintArea">
        <div class="print-ticket">
            <div class="ticket-brand">DENTALCARE</div>
            <div class="ticket-type">
                {{ ($printTicket['type'] ?? 'online') === 'offline' ? 'Phiếu khám trực tiếp' : 'Phiếu lịch đặt online' }}
            </div>

            <div>Số thứ tự</div>
            <div class="ticket-number">{{ $printTicket['queue_number'] ?? '-' }}</div>

            <div class="ticket-info">
                <div><strong>Bệnh nhân:</strong> {{ $printTicket['patient_name'] ?? '-' }}</div>
                <div><strong>SĐT:</strong> {{ $printTicket['patient_phone'] ?? '-' }}</div>
                <div><strong>Dịch vụ:</strong> {{ $printTicket['service_name'] ?? '-' }}</div>
                <div><strong>Bác sĩ:</strong> {{ $printTicket['doctor_name'] ?? '-' }}</div>
                <div><strong>Phòng:</strong> {{ $printTicket['room_name'] ?? '-' }}</div>
                <div><strong>Giờ tiếp nhận:</strong> {{ $printTicket['checked_in_at'] ?? now()->format('d/m/Y H:i') }}</div>
            </div>

            <div class="ticket-footer">Vui lòng giữ phiếu và chờ gọi số.</div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    const requestedPanel = new URLSearchParams(window.location.search).get('panel');

    document.addEventListener('DOMContentLoaded', function () {
        if (requestedPanel === 'online') {
            showPanel('onlinePanel');
        }

        handlePatientChange(false);
        handleServiceChange();

        @if($printTicket)
            setTimeout(function () {
                window.print();
            }, 500);
        @endif
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

    function handlePatientChange(forceFill) {
        const patientSelect = document.getElementById('patientSelect');
        const phoneInput = document.getElementById('patientPhoneInput');

        if (!patientSelect || !phoneInput) {
            return;
        }

        const selected = patientSelect.options[patientSelect.selectedIndex];
        const phone = selected ? (selected.dataset.phone || '') : '';

        if (phone && (forceFill || !phoneInput.value)) {
            phoneInput.value = phone;
        }
    }

    function handleServiceChange() {
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
        doctorSelect.value = '';

        Array.from(doctorSelect.options).forEach(function (option) {
            if (!option.value) {
                option.textContent = serviceSelect.value ? '-- Chọn bác sĩ phù hợp --' : '-- Chọn dịch vụ trước --';
                option.hidden = false;
                return;
            }

            const doctorSpecialization = option.dataset.specialization || '';
            option.hidden = specialization !== '' && doctorSpecialization !== specialization;
        });

        if (serviceSelect.value) {
            summary.classList.add('active');
            summary.innerHTML =
                '<strong>Thông tin dịch vụ:</strong><br>' +
                'Loại dịch vụ: ' + (type || 'Chưa phân loại') + '<br>' +
                'Chuyên khoa yêu cầu: ' + (specialization || 'Chưa gán') + '<br>' +
                'Thời lượng dự kiến: ' + (duration || '30') + ' phút<br>' +
                'Phòng gợi ý: ' + (room || 'Chưa gán phòng');
        } else {
            summary.classList.remove('active');
            summary.innerHTML = '';
        }
    }
</script>
@endsection