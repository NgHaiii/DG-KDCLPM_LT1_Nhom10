@extends('layouts.doctor-layout')

@section('title', 'Khám bệnh')
@section('page-title', 'Khám bệnh')
@section('page-subtitle', 'Theo dõi hàng chờ, bắt đầu khám và cập nhật hồ sơ bệnh án')

@section('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .summary-card,
    .panel {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
    }

    .summary-card {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .summary-icon {
        width: 46px;
        height: 46px;
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .summary-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .summary-value {
        font-family: var(--font-title);
        font-size: 26px;
        font-weight: 800;
    }

    .panel {
        margin-bottom: 22px;
        overflow: hidden;
    }

    .panel-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-title);
        font-size: 17px;
        font-weight: 800;
    }

    .panel-title i {
        color: var(--primary);
        font-size: 21px;
    }

    .appointment-list {
        display: flex;
        flex-direction: column;
    }

    .appointment-row {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-color);
        display: grid;
        grid-template-columns: 92px 1fr auto;
        gap: 18px;
        align-items: center;
    }

    .appointment-row:last-child {
        border-bottom: none;
    }

    .queue-no {
        width: 54px;
        height: 54px;
        border-radius: var(--radius-md);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-title);
        font-size: 20px;
        font-weight: 900;
    }

    .patient-name {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .patient-sub {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 14px;
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 8px;
    }

    .patient-sub span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 16px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-progress {
        background: #ede9fe;
        color: #5b21b6;
        border: 1px solid #ddd6fe;
    }

    .status-waiting {
        background: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
    }

    .status-done {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .empty {
        padding: 42px 22px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty i {
        display: block;
        font-size: 42px;
        color: var(--primary);
        margin-bottom: 12px;
    }

    @media (max-width: 900px) {
        .appointment-row {
            grid-template-columns: 1fr;
        }

        .actions {
            justify-content: flex-start;
        }
    }
</style>
@endsection

@section('content')
@php
    $inProgress = $inProgress ?? collect();
    $waitingAppointments = $waitingAppointments ?? collect();
    $completedToday = $completedToday ?? collect();

    $getSnapshot = function ($appointment) {
        $snapshot = $appointment->patient_snapshot ?? [];

        if (is_string($snapshot)) {
            $decoded = json_decode($snapshot, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($snapshot) ? $snapshot : [];
    };

    $getPatientInfo = function ($appointment) use ($getSnapshot) {
        $snapshot = $getSnapshot($appointment);

        $name = $appointment->patientProfile?->full_name
            ?? data_get($snapshot, 'full_name')
            ?? $appointment->patient?->name
            ?? 'Chưa có tên';

        $phone = $appointment->patientProfile?->phone
            ?? data_get($snapshot, 'phone')
            ?? $appointment->patient?->phone
            ?? $appointment->patient?->phone_number
            ?? $appointment->patient?->tel
            ?? null;

        if (!$phone && $appointment->notes) {
            preg_match('/SĐT:\s*([0-9+\-\s]+)/u', $appointment->notes, $matches);
            $phone = isset($matches[1]) ? trim($matches[1]) : null;
        }

        $dob = $appointment->patientProfile?->dob
            ? $appointment->patientProfile->dob->format('d/m/Y')
            : data_get($snapshot, 'dob');

        if ($dob && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $dob = \Carbon\Carbon::parse($dob)->format('d/m/Y');
        }

        $gender = $appointment->patientProfile?->gender_label
            ?? data_get($snapshot, 'gender_label')
            ?? data_get($snapshot, 'gender')
            ?? null;

        if ($gender === 'male') {
            $gender = 'Nam';
        } elseif ($gender === 'female') {
            $gender = 'Nữ';
        } elseif ($gender === 'other') {
            $gender = 'Khác';
        }

        return [
            'name' => $name,
            'phone' => $phone ?: 'Chưa có SĐT',
            'dob' => $dob,
            'gender' => $gender,
        ];
    };
@endphp

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon"><i class="ri-pulse-line"></i></div>
        <div>
            <div class="summary-label">Đang khám</div>
            <div class="summary-value">{{ $inProgress->count() }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon"><i class="ri-hourglass-2-line"></i></div>
        <div>
            <div class="summary-label">Đang chờ</div>
            <div class="summary-value">{{ $waitingAppointments->count() }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon"><i class="ri-check-double-line"></i></div>
        <div>
            <div class="summary-label">Đã khám hôm nay</div>
            <div class="summary-value">{{ $completedToday->count() }}</div>
        </div>
    </div>
</div>

<section class="panel">
    <div class="panel-header">
        <div class="panel-title">
            <i class="ri-stethoscope-line"></i>
            Ca đang khám
        </div>
    </div>

    @if($inProgress->isEmpty())
        <div class="empty">
            <i class="ri-heart-pulse-line"></i>
            <h3>Chưa có ca đang khám</h3>
            <p>Bắt đầu một ca từ danh sách chờ bên dưới.</p>
        </div>
    @else
        <div class="appointment-list">
            @foreach($inProgress as $appointment)
                @php
                    $patientInfo = $getPatientInfo($appointment);
                @endphp

                <div class="appointment-row">
                    <div class="queue-no">{{ $appointment->queue_number ?? '-' }}</div>

                    <div>
                        <div class="patient-name">{{ $patientInfo['name'] }}</div>

                        <div class="patient-sub">
                            <span><i class="ri-phone-line"></i>{{ $patientInfo['phone'] }}</span>

                            @if($patientInfo['gender'])
                                <span><i class="ri-user-line"></i>{{ $patientInfo['gender'] }}</span>
                            @endif

                            @if($patientInfo['dob'])
                                <span><i class="ri-calendar-line"></i>{{ $patientInfo['dob'] }}</span>
                            @endif
                        </div>

                        <div class="meta">
                            <span><i class="ri-stethoscope-line"></i>{{ $appointment->service?->name ?? 'Dịch vụ' }}</span>
                            <span><i class="ri-door-open-line"></i>{{ $appointment->room?->name ?? 'Chưa có phòng' }}</span>
                            <span><i class="ri-play-circle-line"></i>Bắt đầu: {{ $appointment->started_at?->format('H:i') ?? '-' }}</span>
                            <span><i class="ri-timer-line"></i>{{ $appointment->duration_minutes ?? 30 }} phút dự kiến</span>
                        </div>
                    </div>

                    <div class="actions">
                        <span class="status status-progress"><i class="ri-pulse-line"></i>Đang khám</span>
                        <a href="{{ route('doctor.examinations.show', $appointment->id) }}" class="btn btn-primary btn-sm">
                            <i class="ri-edit-line"></i>
                            Tiếp tục
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="panel">
    <div class="panel-header">
        <div class="panel-title">
            <i class="ri-hourglass-2-line"></i>
            Hàng chờ khám
        </div>
    </div>

    @if($waitingAppointments->isEmpty())
        <div class="empty">
            <i class="ri-user-heart-line"></i>
            <h3>Không có bệnh nhân đang chờ</h3>
            <p>Bệnh nhân sau khi lễ tân tiếp nhận sẽ hiển thị tại đây.</p>
        </div>
    @else
        <div class="appointment-list">
            @foreach($waitingAppointments as $appointment)
                @php
                    $patientInfo = $getPatientInfo($appointment);
                @endphp

                <div class="appointment-row">
                    <div class="queue-no">{{ $appointment->queue_number ?? '-' }}</div>

                    <div>
                        <div class="patient-name">{{ $patientInfo['name'] }}</div>

                        <div class="patient-sub">
                            <span><i class="ri-phone-line"></i>{{ $patientInfo['phone'] }}</span>

                            @if($patientInfo['gender'])
                                <span><i class="ri-user-line"></i>{{ $patientInfo['gender'] }}</span>
                            @endif

                            @if($patientInfo['dob'])
                                <span><i class="ri-calendar-line"></i>{{ $patientInfo['dob'] }}</span>
                            @endif
                        </div>

                        <div class="meta">
                            <span><i class="ri-stethoscope-line"></i>{{ $appointment->service?->name ?? 'Dịch vụ' }}</span>
                            <span><i class="ri-door-open-line"></i>{{ $appointment->room?->name ?? 'Chưa có phòng' }}</span>
                            <span><i class="ri-login-circle-line"></i>Tiếp nhận: {{ $appointment->checked_in_at?->format('H:i') ?? '-' }}</span>
                            <span><i class="{{ ($appointment->source ?? 'online') === 'offline' ? 'ri-hospital-line' : 'ri-global-line' }}"></i>{{ ($appointment->source ?? 'online') === 'offline' ? 'Tại quầy' : 'Online' }}</span>
                        </div>
                    </div>

                    <div class="actions">
                        <span class="status status-waiting"><i class="ri-time-line"></i>Đang chờ</span>
                        <form method="POST" action="{{ route('doctor.examinations.start', $appointment->id) }}"
                              onsubmit="return confirm('Bắt đầu khám cho bệnh nhân này?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-play-circle-line"></i>
                                Bắt đầu khám
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="panel">
    <div class="panel-header">
        <div class="panel-title">
            <i class="ri-check-double-line"></i>
            Đã hoàn thành hôm nay
        </div>
    </div>

    @if($completedToday->isEmpty())
        <div class="empty">
            <i class="ri-file-list-3-line"></i>
            <h3>Chưa có ca hoàn thành</h3>
        </div>
    @else
        <div class="appointment-list">
            @foreach($completedToday as $appointment)
                @php
                    $patientInfo = $getPatientInfo($appointment);
                @endphp

                <div class="appointment-row">
                    <div class="queue-no">{{ $appointment->queue_number ?? '-' }}</div>

                    <div>
                        <div class="patient-name">{{ $patientInfo['name'] }}</div>

                        <div class="patient-sub">
                            <span><i class="ri-phone-line"></i>{{ $patientInfo['phone'] }}</span>

                            @if($patientInfo['gender'])
                                <span><i class="ri-user-line"></i>{{ $patientInfo['gender'] }}</span>
                            @endif

                            @if($patientInfo['dob'])
                                <span><i class="ri-calendar-line"></i>{{ $patientInfo['dob'] }}</span>
                            @endif
                        </div>

                        <div class="meta">
                            <span><i class="ri-stethoscope-line"></i>{{ $appointment->service?->name ?? 'Dịch vụ' }}</span>
                            <span><i class="ri-check-double-line"></i>Hoàn thành: {{ $appointment->completed_at?->format('H:i') ?? '-' }}</span>
                            <span><i class="ri-timer-line"></i>Thực tế: {{ $appointment->actual_used_minutes ?? '-' }} phút</span>
                        </div>
                    </div>

                    <div class="actions">
                        <span class="status status-done"><i class="ri-check-line"></i>Hoàn thành</span>
                        <a href="{{ route('doctor.examinations.show', $appointment->id) }}" class="btn btn-secondary btn-sm">
                            <i class="ri-eye-line"></i>
                            Xem hồ sơ
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection