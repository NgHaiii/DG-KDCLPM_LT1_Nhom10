@extends('layouts.patient-layout')

@section('title', 'Danh Sách Lịch Hẹn')

@section('page-title', 'Danh Sách Lịch Hẹn')
@section('page-subtitle', 'Theo dõi lịch khám sắp tới, phòng khám và lịch sử đặt lịch của bạn')

@section('header-actions')
<a href="{{ route('patient.appointment.create') }}" class="btn btn-primary">
    <i class="ri-add-circle-line"></i>
    Đặt lịch khám
</a>
@endsection

@section('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .summary-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 22px;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .summary-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .summary-value {
        font-family: var(--font-title);
        font-size: 26px;
        font-weight: 700;
        color: var(--text-main);
    }

    .appointment-section {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .section-header {
        padding: 22px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-title);
        font-size: 17px;
        font-weight: 700;
        color: var(--text-main);
    }

    .section-title i {
        color: var(--primary);
        font-size: 20px;
    }

    .appointment-list {
        display: flex;
        flex-direction: column;
    }

    .appointment-item {
        display: grid;
        grid-template-columns: minmax(150px, 190px) 1fr auto;
        gap: 20px;
        padding: 22px 24px;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
    }

    .appointment-item:last-child {
        border-bottom: none;
    }

    .appointment-date {
        font-family: var(--font-title);
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }

    .appointment-hour {
        color: var(--primary);
        font-weight: 700;
        font-size: 14px;
        margin-top: 6px;
    }

    .appointment-service {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .appointment-meta,
    .room-info {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .appointment-meta span,
    .room-info span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .room-info {
        margin-top: 10px;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
    }

    .room-confirmed {
        color: #047857;
        font-weight: 600;
    }

    .room-pending {
        color: #92400e;
        font-weight: 600;
    }

    .appointment-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }

    .status-confirmed {
        background: var(--success-light);
        color: var(--success-dark);
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .status-checked-in,
    .status-waiting {
        background: #e0f2fe;
        color: #075985;
        border: 1px solid rgba(14, 165, 233, 0.25);
    }

    .status-in-progress {
        background: #ede9fe;
        color: #5b21b6;
        border: 1px solid rgba(124, 58, 237, 0.25);
    }

    .status-completed {
        background: var(--info-light);
        color: var(--info-dark);
        border: 1px solid rgba(59, 130, 246, 0.25);
    }

    .status-cancelled {
        background: var(--error-light);
        color: var(--error-dark);
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .status-missed {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .empty-state {
        padding: 48px 24px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: var(--radius-lg);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
    }

    .cancel-form {
        margin: 0;
    }

    .detail-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .detail-modal.show {
        display: flex;
    }

    .detail-panel {
        width: min(720px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
    }

    .detail-header {
        padding: 22px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .detail-title {
        font-family: var(--font-title);
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
    }

    .detail-subtitle {
        color: var(--text-muted);
        font-size: 13px;
        margin-top: 4px;
    }

    .detail-close {
        border: none;
        background: #f8fafc;
        color: var(--text-muted);
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-size: 20px;
    }

    .detail-close:hover {
        background: #e2e8f0;
        color: var(--text-main);
    }

    .detail-body {
        padding: 24px;
    }

    .detail-notice {
        padding: 14px 16px;
        border-radius: var(--radius-md);
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.5;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--text-main);
    }

    .detail-notice.pending {
        background: #fffbeb;
        color: #92400e;
        border-color: #fde68a;
    }

    .detail-notice.confirmed,
    .detail-notice.checked_in,
    .detail-notice.waiting,
    .detail-notice.in_progress,
    .detail-notice.completed {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .detail-notice.cancelled {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .detail-notice.missed {
        background: #f8fafc;
        color: #475569;
        border-color: #cbd5e1;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-item {
        padding: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
    }

    .detail-item.full {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 5px;
    }

    .detail-value {
        font-size: 14px;
        color: var(--text-main);
        font-weight: 600;
        white-space: pre-line;
    }

    @media (max-width: 900px) {
        .appointment-item {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .appointment-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .appointment-item,
        .summary-card {
            padding: 18px;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $upcomingCount = $appointments->count();
    $pastCount = $pastAppointments->count();
    $pendingCount = $appointments->where('status', 'pending')->count();
    $confirmedCount = $appointments->whereIn('status', ['confirmed', 'checked_in', 'waiting', 'in_progress'])->count();

    $displayStatus = function ($appointment) {
        if ($appointment->appointment_date->isPast()) {
            if ($appointment->status === 'completed' || $appointment->completed_at) {
                return 'completed';
            }

            if ($appointment->status === 'cancelled') {
                return 'cancelled';
            }

            return 'missed';
        }

        return $appointment->status;
    };

    $statusClass = function ($status) {
        return match ($status) {
            'pending' => 'status-pending',
            'confirmed' => 'status-confirmed',
            'checked_in' => 'status-checked-in',
            'waiting' => 'status-waiting',
            'in_progress' => 'status-in-progress',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
            'missed' => 'status-missed',
            default => 'status-pending',
        };
    };

    $statusIcon = function ($status) {
        return match ($status) {
            'pending' => 'ri-time-line',
            'confirmed' => 'ri-checkbox-circle-line',
            'checked_in' => 'ri-login-circle-line',
            'waiting' => 'ri-hourglass-2-line',
            'in_progress' => 'ri-stethoscope-line',
            'completed' => 'ri-check-double-line',
            'cancelled' => 'ri-close-circle-line',
            'missed' => 'ri-calendar-close-line',
            default => 'ri-information-line',
        };
    };

    $statusText = function ($status) {
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã check-in',
            'waiting' => 'Đang chờ khám',
            'in_progress' => 'Đang khám',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            'missed' => 'Bỏ lỡ lịch khám',
            default => 'Không xác định',
        };
    };

    $sourceText = function ($source) {
        return match ($source) {
            'offline' => 'Tại quầy',
            'online' => 'Online',
            default => 'Online',
        };
    };

    $sourceIcon = function ($source) {
        return match ($source) {
            'offline' => 'ri-hospital-line',
            'online' => 'ri-global-line',
            default => 'ri-global-line',
        };
    };

    $canCancel = function ($appointment) {
        return in_array($appointment->status, ['pending', 'confirmed'], true)
            && !$appointment->appointment_date->isPast();
    };
@endphp

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon">
            <i class="ri-calendar-check-line"></i>
        </div>
        <div>
            <div class="summary-label">Lịch sắp tới</div>
            <div class="summary-value">{{ $upcomingCount }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon">
            <i class="ri-time-line"></i>
        </div>
        <div>
            <div class="summary-label">Chờ xác nhận</div>
            <div class="summary-value">{{ $pendingCount }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon">
            <i class="ri-door-open-line"></i>
        </div>
        <div>
            <div class="summary-label">Đã xác nhận/xếp lịch</div>
            <div class="summary-value">{{ $confirmedCount }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon">
            <i class="ri-history-line"></i>
        </div>
        <div>
            <div class="summary-label">Lịch đã qua</div>
            <div class="summary-value">{{ $pastCount }}</div>
        </div>
    </div>
</div>

<div class="appointment-section">
    <div class="section-header">
        <div class="section-title">
            <i class="ri-calendar-event-line"></i>
            Lịch khám sắp tới
        </div>
    </div>

    @if($appointments->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ri-calendar-line"></i>
            </div>
            <h3>Chưa có lịch hẹn sắp tới</h3>
            <p>Bạn có thể đặt lịch khám mới với bác sĩ phù hợp.</p>
            <a href="{{ route('patient.appointment.create') }}" class="btn btn-primary">
                <i class="ri-add-circle-line"></i>
                Đặt lịch khám
            </a>
        </div>
    @else
        <div class="appointment-list">
            @foreach($appointments as $appointment)
                @php
                    $shownStatus = $displayStatus($appointment);
                @endphp

                <div class="appointment-item">
                    <div>
                        <div class="appointment-date">
                            {{ $appointment->appointment_date->format('d/m/Y') }}
                        </div>
                        <div class="appointment-hour">
                            {{ $appointment->appointment_date->format('H:i') }}
                            @if($appointment->duration_minutes)
                                - {{ $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes)->format('H:i') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="appointment-service">
                            {{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}
                        </div>

                        <div class="appointment-meta">
                            <span>
                                <i class="ri-user-heart-line"></i>
                                {{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}
                            </span>

                            <span>
                                <i class="ri-hospital-line"></i>
                                {{ $appointment->doctor?->specialization ?? 'N/A' }}
                            </span>

                            @if($appointment->duration_minutes)
                                <span>
                                    <i class="ri-timer-line"></i>
                                    {{ $appointment->duration_minutes }} phút
                                </span>
                            @endif

                            <span>
                                <i class="{{ $sourceIcon($appointment->source ?? 'online') }}"></i>
                                {{ $sourceText($appointment->source ?? 'online') }}
                            </span>
                        </div>

                        <div class="room-info">
                            @if($appointment->room)
                                <span class="room-confirmed">
                                    <i class="ri-door-open-line"></i>
                                    Phòng khám: {{ $appointment->room->name }}
                                </span>

                                @if($appointment->room->code)
                                    <span>
                                        <i class="ri-hashtag"></i>
                                        Mã phòng: {{ $appointment->room->code }}
                                    </span>
                                @endif

                                @if($appointment->room->floor)
                                    <span>
                                        <i class="ri-building-line"></i>
                                        Tầng: {{ $appointment->room->floor }}
                                    </span>
                                @endif

                                @if($appointment->room->location)
                                    <span>
                                        <i class="ri-map-pin-line"></i>
                                        {{ $appointment->room->location }}
                                    </span>
                                @endif
                            @elseif($appointment->status === 'pending')
                                <span class="room-pending">
                                    <i class="ri-time-line"></i>
                                    Chưa xếp phòng. Phòng khám sẽ hiển thị sau khi bác sĩ xác nhận.
                                </span>
                            @else
                                <span class="room-pending">
                                    <i class="ri-error-warning-line"></i>
                                    Chưa có thông tin phòng khám.
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="appointment-actions">
                        <span class="status-badge {{ $statusClass($shownStatus) }}">
                            <i class="{{ $statusIcon($shownStatus) }}"></i>
                            {{ $statusText($shownStatus) }}
                        </span>

                        <button type="button"
                                class="btn btn-secondary btn-sm appointment-detail-btn"
                                data-id="{{ $appointment->id }}"
                                data-service="{{ e($appointment->service?->name ?? 'Dịch vụ không xác định') }}"
                                data-doctor="{{ e($appointment->doctor?->name ?? 'Chưa có bác sĩ') }}"
                                data-specialization="{{ e($appointment->doctor?->specialization ?? 'N/A') }}"
                                data-date="{{ $appointment->appointment_date->format('d/m/Y') }}"
                                data-time="{{ $appointment->appointment_date->format('H:i') }}"
                                data-end-time="{{ $appointment->duration_minutes ? $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes)->format('H:i') : '' }}"
                                data-duration="{{ $appointment->duration_minutes }}"
                                data-status="{{ $shownStatus }}"
                                data-status-text="{{ e($statusText($shownStatus)) }}"
                                data-source="{{ e($sourceText($appointment->source ?? 'online')) }}"
                                data-room-name="{{ e($appointment->room?->name ?? '') }}"
                                data-room-code="{{ e($appointment->room?->code ?? '') }}"
                                data-room-floor="{{ e($appointment->room?->floor ?? '') }}"
                                data-room-location="{{ e($appointment->room?->location ?? '') }}"
                                data-confirmed-at="{{ $appointment->confirmed_at?->format('d/m/Y H:i') }}"
                                data-checked-in-at="{{ $appointment->checked_in_at?->format('d/m/Y H:i') }}"
                                data-started-at="{{ $appointment->started_at?->format('d/m/Y H:i') }}"
                                data-completed-at="{{ $appointment->completed_at?->format('d/m/Y H:i') }}"
                                data-notes="{{ e($appointment->notes ?? '') }}"
                                onclick="openAppointmentDetailFromButton(this)">
                            <i class="ri-eye-line"></i>
                            Chi tiết
                        </button>

                        @if($canCancel($appointment))
                            <form class="cancel-form"
                                  method="POST"
                                  action="{{ route('patient.appointment.cancel', $appointment->id) }}"
                                  onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này không?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="ri-close-line"></i>
                                    Hủy
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="appointment-section">
    <div class="section-header">
        <div class="section-title">
            <i class="ri-history-line"></i>
            Lịch hẹn đã qua
        </div>
    </div>

    @if($pastAppointments->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="ri-file-list-3-line"></i>
            </div>
            <h3>Chưa có lịch sử lịch hẹn</h3>
            <p>Các lịch hẹn đã qua sẽ được hiển thị tại đây.</p>
        </div>
    @else
        <div class="appointment-list">
            @foreach($pastAppointments as $appointment)
                @php
                    $shownStatus = $displayStatus($appointment);
                @endphp

                <div class="appointment-item">
                    <div>
                        <div class="appointment-date">
                            {{ $appointment->appointment_date->format('d/m/Y') }}
                        </div>
                        <div class="appointment-hour">
                            {{ $appointment->appointment_date->format('H:i') }}
                            @if($appointment->duration_minutes)
                                - {{ $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes)->format('H:i') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="appointment-service">
                            {{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}
                        </div>

                        <div class="appointment-meta">
                            <span>
                                <i class="ri-user-heart-line"></i>
                                {{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}
                            </span>

                            <span>
                                <i class="ri-hospital-line"></i>
                                {{ $appointment->doctor?->specialization ?? 'N/A' }}
                            </span>

                            @if($appointment->duration_minutes)
                                <span>
                                    <i class="ri-timer-line"></i>
                                    {{ $appointment->duration_minutes }} phút
                                </span>
                            @endif

                            <span>
                                <i class="{{ $sourceIcon($appointment->source ?? 'online') }}"></i>
                                {{ $sourceText($appointment->source ?? 'online') }}
                            </span>
                        </div>

                        <div class="room-info">
                            @if($appointment->room)
                                <span class="room-confirmed">
                                    <i class="ri-door-open-line"></i>
                                    Phòng khám: {{ $appointment->room->name }}
                                </span>

                                @if($appointment->room->code)
                                    <span>
                                        <i class="ri-hashtag"></i>
                                        Mã phòng: {{ $appointment->room->code }}
                                    </span>
                                @endif
                            @else
                                <span class="room-pending">
                                    <i class="ri-information-line"></i>
                                    Không có thông tin phòng khám.
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="appointment-actions">
                        <span class="status-badge {{ $statusClass($shownStatus) }}">
                            <i class="{{ $statusIcon($shownStatus) }}"></i>
                            {{ $statusText($shownStatus) }}
                        </span>

                        <button type="button"
                                class="btn btn-secondary btn-sm appointment-detail-btn"
                                data-id="{{ $appointment->id }}"
                                data-service="{{ e($appointment->service?->name ?? 'Dịch vụ không xác định') }}"
                                data-doctor="{{ e($appointment->doctor?->name ?? 'Chưa có bác sĩ') }}"
                                data-specialization="{{ e($appointment->doctor?->specialization ?? 'N/A') }}"
                                data-date="{{ $appointment->appointment_date->format('d/m/Y') }}"
                                data-time="{{ $appointment->appointment_date->format('H:i') }}"
                                data-end-time="{{ $appointment->duration_minutes ? $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes)->format('H:i') : '' }}"
                                data-duration="{{ $appointment->duration_minutes }}"
                                data-status="{{ $shownStatus }}"
                                data-status-text="{{ e($statusText($shownStatus)) }}"
                                data-source="{{ e($sourceText($appointment->source ?? 'online')) }}"
                                data-room-name="{{ e($appointment->room?->name ?? '') }}"
                                data-room-code="{{ e($appointment->room?->code ?? '') }}"
                                data-room-floor="{{ e($appointment->room?->floor ?? '') }}"
                                data-room-location="{{ e($appointment->room?->location ?? '') }}"
                                data-confirmed-at="{{ $appointment->confirmed_at?->format('d/m/Y H:i') }}"
                                data-checked-in-at="{{ $appointment->checked_in_at?->format('d/m/Y H:i') }}"
                                data-started-at="{{ $appointment->started_at?->format('d/m/Y H:i') }}"
                                data-completed-at="{{ $appointment->completed_at?->format('d/m/Y H:i') }}"
                                data-notes="{{ e($appointment->notes ?? '') }}"
                                onclick="openAppointmentDetailFromButton(this)">
                            <i class="ri-eye-line"></i>
                            Chi tiết
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="detail-modal" id="appointmentDetailModal" onclick="closeAppointmentDetail(event)">
    <div class="detail-panel">
        <div class="detail-header">
            <div>
                <h3 class="detail-title" id="detailTitle">Chi tiết lịch hẹn</h3>
                <div class="detail-subtitle" id="detailSubtitle"></div>
            </div>
            <button type="button" class="detail-close" onclick="closeAppointmentDetail()">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <div class="detail-body">
            <div class="detail-notice" id="detailNotice"></div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Dịch vụ</div>
                    <div class="detail-value" id="detailService"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Thời lượng</div>
                    <div class="detail-value" id="detailDuration"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Bác sĩ</div>
                    <div class="detail-value" id="detailDoctor"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Chuyên khoa</div>
                    <div class="detail-value" id="detailSpecialization"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Ngày khám</div>
                    <div class="detail-value" id="detailDate"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Giờ khám</div>
                    <div class="detail-value" id="detailTime"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Trạng thái</div>
                    <div class="detail-value" id="detailStatus"></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Nguồn lịch</div>
                    <div class="detail-value" id="detailSource"></div>
                </div>

                <div class="detail-item full">
                    <div class="detail-label">Phòng khám</div>
                    <div class="detail-value" id="detailRoom"></div>
                </div>

                <div class="detail-item full">
                    <div class="detail-label">Mốc xử lý</div>
                    <div class="detail-value" id="detailTimeline"></div>
                </div>

                <div class="detail-item full">
                    <div class="detail-label">Ghi chú</div>
                    <div class="detail-value" id="detailNotes"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openAppointmentDetailFromButton(button) {
        const data = {
            id: button.dataset.id || '',
            service: button.dataset.service || '',
            doctor: button.dataset.doctor || '',
            specialization: button.dataset.specialization || '',
            date: button.dataset.date || '',
            time: button.dataset.time || '',
            end_time: button.dataset.endTime || '',
            duration: button.dataset.duration || '',
            status: button.dataset.status || '',
            status_text: button.dataset.statusText || '',
            source: button.dataset.source || '',
            room_name: button.dataset.roomName || '',
            room_code: button.dataset.roomCode || '',
            room_floor: button.dataset.roomFloor || '',
            room_location: button.dataset.roomLocation || '',
            confirmed_at: button.dataset.confirmedAt || '',
            checked_in_at: button.dataset.checkedInAt || '',
            started_at: button.dataset.startedAt || '',
            completed_at: button.dataset.completedAt || '',
            notes: button.dataset.notes || '',
        };

        openAppointmentDetail(data);
    }

    function openAppointmentDetail(data) {
        document.getElementById('detailTitle').textContent = 'Chi tiết lịch hẹn #' + data.id;
        document.getElementById('detailSubtitle').textContent = data.date + ' • ' + data.time;

        document.getElementById('detailService').textContent = data.service || '-';
        document.getElementById('detailDuration').textContent = data.duration ? data.duration + ' phút' : '-';
        document.getElementById('detailDoctor').textContent = data.doctor || '-';
        document.getElementById('detailSpecialization').textContent = data.specialization || '-';
        document.getElementById('detailDate').textContent = data.date || '-';
        document.getElementById('detailTime').textContent = data.end_time ? data.time + ' - ' + data.end_time : data.time;
        document.getElementById('detailStatus').textContent = data.status_text || '-';
        document.getElementById('detailSource').textContent = data.source || '-';
        document.getElementById('detailNotes').textContent = data.notes || 'Không có ghi chú';

        const roomLines = [];

        if (data.room_name) {
            roomLines.push('Phòng: ' + data.room_name);
            if (data.room_code) roomLines.push('Mã phòng: ' + data.room_code);
            if (data.room_floor) roomLines.push('Tầng: ' + data.room_floor);
            if (data.room_location) roomLines.push('Vị trí: ' + data.room_location);
        } else if (data.status === 'pending') {
            roomLines.push('Chưa xếp phòng. Phòng khám sẽ hiển thị sau khi bác sĩ xác nhận.');
        } else {
            roomLines.push('Chưa có thông tin phòng khám.');
        }

        document.getElementById('detailRoom').textContent = roomLines.join('\n');

        const timeline = [];

        if (data.confirmed_at) timeline.push('Xác nhận: ' + data.confirmed_at);
        if (data.checked_in_at) timeline.push('Check-in: ' + data.checked_in_at);
        if (data.started_at) timeline.push('Bắt đầu khám: ' + data.started_at);
        if (data.completed_at) timeline.push('Hoàn thành: ' + data.completed_at);

        document.getElementById('detailTimeline').textContent = timeline.length ? timeline.join('\n') : 'Chưa có mốc xử lý.';
        document.getElementById('detailNotice').className = 'detail-notice ' + data.status;

        const notice = document.getElementById('detailNotice');

        if (data.status === 'pending') {
            notice.textContent = 'Lịch hẹn đang chờ bác sĩ xác nhận. Phòng khám sẽ được hiển thị sau khi lịch được xác nhận.';
        } else if (data.status === 'confirmed') {
            notice.textContent = data.room_name
                ? 'Lịch hẹn đã được xác nhận. Vui lòng đến đúng giờ và theo dõi phòng khám được hiển thị.'
                : 'Lịch hẹn đã được xác nhận nhưng chưa có thông tin phòng khám.';
        } else if (data.status === 'checked_in') {
            notice.textContent = 'Bạn đã được tiếp nhận tại quầy. Vui lòng chờ hướng dẫn tiếp theo.';
        } else if (data.status === 'waiting') {
            notice.textContent = 'Bạn đang trong hàng chờ khám. Lễ tân sẽ thông báo khi đến lượt.';
        } else if (data.status === 'in_progress') {
            notice.textContent = 'Lịch khám đang được xử lý.';
        } else if (data.status === 'completed') {
            notice.textContent = 'Lịch khám đã hoàn thành.';
        } else if (data.status === 'cancelled') {
            notice.textContent = 'Lịch hẹn này đã được hủy.';
        } else if (data.status === 'missed') {
            notice.textContent = 'Lịch hẹn đã qua nhưng hệ thống không ghi nhận hoàn thành khám. Nếu bạn đã đến khám, vui lòng liên hệ phòng khám để kiểm tra lại.';
        } else {
            notice.textContent = 'Theo dõi trạng thái lịch hẹn tại đây.';
        }

        document.getElementById('appointmentDetailModal').classList.add('show');
    }

    function closeAppointmentDetail(event) {
        if (event && event.target.id !== 'appointmentDetailModal') {
            return;
        }

        document.getElementById('appointmentDetailModal').classList.remove('show');
    }
</script>
@endsection