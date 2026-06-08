@extends('layouts.doctor-layout')

@section('title', 'Lịch Đặt Online')

@section('page-title', 'Lịch Đặt Online')
@section('page-subtitle', 'Kiểm tra thông tin lịch hẹn trước khi xác nhận hoặc hủy')

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
        flex-shrink: 0;
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
        justify-content: space-between;
        gap: 12px;
        font-family: var(--font-title);
        font-weight: 700;
        color: var(--text-main);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--primary);
        font-size: 20px;
    }

    .section-count {
        font-size: 12px;
        font-weight: 700;
        padding: 6px 10px;
        border-radius: var(--radius-full);
        background: var(--primary-light);
        color: var(--primary);
    }

    .appointment-list {
        display: flex;
        flex-direction: column;
    }

    .appointment-card {
        border-bottom: 1px solid var(--border-color);
    }

    .appointment-card:last-child {
        border-bottom: none;
    }

    .appointment-summary {
        display: grid;
        grid-template-columns: 180px 1fr auto;
        gap: 20px;
        padding: 22px 24px;
        align-items: center;
    }

    .date-main {
        font-family: var(--font-title);
        font-weight: 800;
        font-size: 18px;
        color: var(--text-main);
        margin-bottom: 6px;
    }

    .time-main {
        color: var(--primary);
        font-weight: 800;
        font-size: 15px;
    }

    .service-name {
        font-family: var(--font-title);
        font-weight: 800;
        color: var(--text-main);
        font-size: 17px;
        margin-bottom: 9px;
    }

    .meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.5;
    }

    .meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .quick-note {
        margin-top: 10px;
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.55;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 800;
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

    .detail-toggle {
        background: white;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .detail-toggle:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .appointment-detail {
        display: none;
        padding: 0 24px 24px;
    }

    .appointment-detail.show {
        display: block;
    }

    .detail-inner {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .detail-box {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 15px;
    }

    .detail-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .detail-value {
        color: var(--text-main);
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
        white-space: pre-line;
    }

    .detail-note {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 15px;
    }

    .detail-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid var(--border-color);
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

    .btn-outline {
        background: white;
        color: var(--text-main);
        border-color: var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    .btn-outline:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    @media (max-width: 980px) {
        .appointment-summary {
            grid-template-columns: 1fr;
            align-items: stretch;
        }

        .actions {
            justify-content: flex-start;
        }

        .detail-grid {
            grid-template-columns: 1fr;
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
        <div class="section-title">
            <i class="ri-notification-3-line"></i>
            Lịch chờ xác nhận
        </div>
        <span class="section-count">{{ $pendingAppointments->count() }} lịch</span>
    </div>

    @if($pendingAppointments->isEmpty())
        <div class="empty-state">
            <i class="ri-calendar-check-line"></i>
            Không có lịch đặt online nào đang chờ xác nhận.
        </div>
    @else
        <div class="appointment-list">
            @foreach($pendingAppointments as $appointment)
                @php
                    $duration = $appointment->duration_minutes ?? 30;
                    $endTime = $appointment->appointment_date->copy()->addMinutes($duration);
                    $detailId = 'appointment-detail-' . $appointment->id;
                @endphp

                <div class="appointment-card">
                    <div class="appointment-summary">
                        <div>
                            <div class="date-main">{{ $appointment->appointment_date->format('d/m/Y') }}</div>
                            <div class="time-main">
                                {{ $appointment->appointment_date->format('H:i') }} - {{ $endTime->format('H:i') }}
                            </div>
                        </div>

                        <div>
                            <div class="service-name">{{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}</div>
                            <div class="meta">
                                <span><i class="ri-user-line"></i>{{ $appointment->patient?->name ?? 'Bệnh nhân' }}</span>
                                <span><i class="ri-timer-line"></i>{{ $duration }} phút</span>
                                <span><i class="ri-calendar-line"></i>Đặt online</span>
                            </div>

                            @if($appointment->notes)
                                <div class="quick-note">
                                    <i class="ri-chat-1-line"></i>
                                    {{ $appointment->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="actions">
                            <span class="status-badge status-pending">
                                <i class="ri-time-line"></i>
                                Chờ xác nhận
                            </span>

                            <button type="button"
                                    class="btn btn-sm btn-outline detail-toggle"
                                    onclick="toggleAppointmentDetail('{{ $detailId }}', this)">
                                <i class="ri-file-list-3-line"></i>
                                Chi tiết
                            </button>
                        </div>
                    </div>

                    <div class="appointment-detail" id="{{ $detailId }}">
                        <div class="detail-inner">
                            <div class="detail-grid">
                                <div class="detail-box">
                                    <div class="detail-label">
                                        <i class="ri-user-heart-line"></i>
                                        Bệnh nhân
                                    </div>
                                    <div class="detail-value">
                                        {{ $appointment->patient?->name ?? 'Bệnh nhân' }}
                                    </div>
                                </div>

                                <div class="detail-box">
                                    <div class="detail-label">
                                        <i class="ri-hospital-line"></i>
                                        Dịch vụ
                                    </div>
                                    <div class="detail-value">
                                        {{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}
                                    </div>
                                </div>

                                <div class="detail-box">
                                    <div class="detail-label">
                                        <i class="ri-calendar-event-line"></i>
                                        Thời gian khám
                                    </div>
                                    <div class="detail-value">
                                        {{ $appointment->appointment_date->format('d/m/Y') }}
                                        <br>
                                        {{ $appointment->appointment_date->format('H:i') }} - {{ $endTime->format('H:i') }}
                                    </div>
                                </div>

                                <div class="detail-box">
                                    <div class="detail-label">
                                        <i class="ri-time-line"></i>
                                        Thời lượng
                                    </div>
                                    <div class="detail-value">
                                        {{ $duration }} phút
                                    </div>
                                </div>
                            </div>

                            <div class="detail-note">
                                <div class="detail-label">
                                    <i class="ri-chat-3-line"></i>
                                    Thông tin đặt lịch / triệu chứng
                                </div>
                                <div class="detail-value">
                                    {{ $appointment->notes ?: 'Không có ghi chú thêm.' }}
                                </div>
                            </div>

                            <div class="detail-actions">
                                <form method="POST" action="{{ route('doctor.appointments.online.confirm', $appointment->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-check-line"></i>
                                        Xác nhận lịch hẹn
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('doctor.appointments.online.cancel', $appointment->id) }}"
                                      onsubmit="return confirm('Bạn có chắc muốn hủy lịch hẹn này không?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ri-close-line"></i>
                                        Hủy lịch hẹn
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="appointment-section">
    <div class="section-header">
        <div class="section-title">
            <i class="ri-calendar-check-line"></i>
            Lịch đã xác nhận sắp tới
        </div>
        <span class="section-count">{{ $confirmedAppointments->count() }} lịch</span>
    </div>

    @if($confirmedAppointments->isEmpty())
        <div class="empty-state">
            <i class="ri-calendar-line"></i>
            Chưa có lịch hẹn nào đã xác nhận.
        </div>
    @else
        <div class="appointment-list">
            @foreach($confirmedAppointments as $appointment)
                @php
                    $duration = $appointment->duration_minutes ?? 30;
                    $endTime = $appointment->appointment_date->copy()->addMinutes($duration);
                @endphp

                <div class="appointment-card">
                    <div class="appointment-summary">
                        <div>
                            <div class="date-main">{{ $appointment->appointment_date->format('d/m/Y') }}</div>
                            <div class="time-main">
                                {{ $appointment->appointment_date->format('H:i') }} - {{ $endTime->format('H:i') }}
                            </div>
                        </div>

                        <div>
                            <div class="service-name">{{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}</div>
                            <div class="meta">
                                <span><i class="ri-user-line"></i>{{ $appointment->patient?->name ?? 'Bệnh nhân' }}</span>
                                <span><i class="ri-timer-line"></i>{{ $duration }} phút</span>
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
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="appointment-section">
    <div class="section-header">
        <div class="section-title">
            <i class="ri-history-line"></i>
            Lịch gần đây
        </div>
        <span class="section-count">{{ $recentAppointments->count() }} lịch</span>
    </div>

    @if($recentAppointments->isEmpty())
        <div class="empty-state">
            <i class="ri-history-line"></i>
            Chưa có lịch hẹn gần đây.
        </div>
    @else
        <div class="appointment-list">
            @foreach($recentAppointments as $appointment)
                <div class="appointment-card">
                    <div class="appointment-summary">
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
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function toggleAppointmentDetail(detailId, button) {
        const detail = document.getElementById(detailId);

        if (!detail) {
            return;
        }

        const isOpen = detail.classList.toggle('show');

        if (button) {
            button.innerHTML = isOpen
                ? '<i class="ri-arrow-up-s-line"></i> Thu gọn'
                : '<i class="ri-file-list-3-line"></i> Chi tiết';
        }
    }
</script>
@endsection