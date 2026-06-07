@extends('layouts.doctor-layout')

@section('title', 'Lịch Đặt Online')

@section('page-title', 'Lịch Đặt Online')
@section('page-subtitle', 'Xác nhận hoặc hủy các lịch hẹn bệnh nhân đã đặt trực tuyến')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 22px;
        box-shadow: var(--shadow-md);
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
        font-size: 23px;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 4px;
    }

    .stat-value {
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
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-title);
        font-weight: 700;
        color: var(--text-main);
    }

    .section-header i {
        color: var(--primary);
        font-size: 20px;
    }

    .appointment-list {
        display: flex;
        flex-direction: column;
    }

    .appointment-item {
        display: grid;
        grid-template-columns: 180px 1fr auto;
        gap: 18px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
    }

    .appointment-item:last-child {
        border-bottom: none;
    }

    .date-main {
        font-family: var(--font-title);
        font-weight: 700;
        font-size: 17px;
        margin-bottom: 6px;
    }

    .time-main {
        color: var(--primary);
        font-weight: 700;
        font-size: 14px;
    }

    .service-name {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
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

    .status-cancelled {
        background: var(--error-light);
        color: var(--error-dark);
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .empty-state {
        padding: 44px 24px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 34px;
        color: var(--primary);
        display: block;
        margin-bottom: 12px;
    }

    form {
        margin: 0;
    }

    @media (max-width: 900px) {
        .appointment-item {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .actions {
            justify-content: flex-start;
        }
    }
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="ri-time-line"></i>
        </div>
        <div>
            <div class="stat-label">Chờ xác nhận</div>
            <div class="stat-value">{{ $pendingAppointments->count() }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="ri-checkbox-circle-line"></i>
        </div>
        <div>
            <div class="stat-label">Đã xác nhận</div>
            <div class="stat-value">{{ $confirmedAppointments->count() }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="ri-history-line"></i>
        </div>
        <div>
            <div class="stat-label">Gần đây</div>
            <div class="stat-value">{{ $recentAppointments->count() }}</div>
        </div>
    </div>
</div>

<div class="appointment-section">
    <div class="section-header">
        <i class="ri-notification-3-line"></i>
        Lịch chờ xác nhận
    </div>

    @if($pendingAppointments->isEmpty())
        <div class="empty-state">
            <i class="ri-calendar-check-line"></i>
            Không có lịch đặt online nào đang chờ xác nhận.
        </div>
    @else
        <div class="appointment-list">
            @foreach($pendingAppointments as $appointment)
                <div class="appointment-item">
                    <div>
                        <div class="date-main">{{ $appointment->appointment_date->format('d/m/Y') }}</div>
                        <div class="time-main">
                            {{ $appointment->appointment_date->format('H:i') }}
                            @if($appointment->duration_minutes)
                                - {{ $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes)->format('H:i') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="service-name">{{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}</div>
                        <div class="meta">
                            <span><i class="ri-user-line"></i>{{ $appointment->patient?->name ?? 'Bệnh nhân' }}</span>
                            <span><i class="ri-timer-line"></i>{{ $appointment->duration_minutes ?? 30 }} phút</span>
                            @if($appointment->notes)
                                <span><i class="ri-chat-1-line"></i>{{ $appointment->notes }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="actions">
                        <span class="status-badge status-pending">
                            <i class="ri-time-line"></i>
                            Chờ xác nhận
                        </span>

                        <form method="POST" action="{{ route('doctor.appointments.online.confirm', $appointment->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-check-line"></i>
                                Xác nhận
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('doctor.appointments.online.cancel', $appointment->id) }}"
                              onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này không?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="ri-close-line"></i>
                                Hủy
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="appointment-section">
    <div class="section-header">
        <i class="ri-calendar-check-line"></i>
        Lịch đã xác nhận sắp tới
    </div>

    @if($confirmedAppointments->isEmpty())
        <div class="empty-state">
            <i class="ri-calendar-line"></i>
            Chưa có lịch hẹn nào đã xác nhận.
        </div>
    @else
        <div class="appointment-list">
            @foreach($confirmedAppointments as $appointment)
                <div class="appointment-item">
                    <div>
                        <div class="date-main">{{ $appointment->appointment_date->format('d/m/Y') }}</div>
                        <div class="time-main">
                            {{ $appointment->appointment_date->format('H:i') }}
                            @if($appointment->duration_minutes)
                                - {{ $appointment->appointment_date->copy()->addMinutes($appointment->duration_minutes)->format('H:i') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="service-name">{{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}</div>
                        <div class="meta">
                            <span><i class="ri-user-line"></i>{{ $appointment->patient?->name ?? 'Bệnh nhân' }}</span>
                            <span><i class="ri-timer-line"></i>{{ $appointment->duration_minutes ?? 30 }} phút</span>
                        </div>
                    </div>

                    <div class="actions">
                        <span class="status-badge status-confirmed">
                            <i class="ri-checkbox-circle-line"></i>
                            Đã xác nhận
                        </span>

                        <form method="POST"
                              action="{{ route('doctor.appointments.online.cancel', $appointment->id) }}"
                              onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này không?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="ri-close-line"></i>
                                Hủy
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="appointment-section">
    <div class="section-header">
        <i class="ri-history-line"></i>
        Lịch gần đây
    </div>

    @if($recentAppointments->isEmpty())
        <div class="empty-state">
            <i class="ri-history-line"></i>
            Chưa có lịch hẹn gần đây.
        </div>
    @else
        <div class="appointment-list">
            @foreach($recentAppointments as $appointment)
                <div class="appointment-item">
                    <div>
                        <div class="date-main">{{ $appointment->appointment_date->format('d/m/Y') }}</div>
                        <div class="time-main">{{ $appointment->appointment_date->format('H:i') }}</div>
                    </div>

                    <div>
                        <div class="service-name">{{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}</div>
                        <div class="meta">
                            <span><i class="ri-user-line"></i>{{ $appointment->patient?->name ?? 'Bệnh nhân' }}</span>
                        </div>
                    </div>

                    <div class="actions">
                        <span class="status-badge {{ $appointment->status === 'cancelled' ? 'status-cancelled' : 'status-confirmed' }}">
                            {{ $appointment->status === 'cancelled' ? 'Đã hủy' : 'Đã xử lý' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection