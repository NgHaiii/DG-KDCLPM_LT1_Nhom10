@extends('layouts.patient-layout')

@section('title', 'Danh Sách Lịch Hẹn')

@section('page-title', 'Danh Sách Lịch Hẹn')
@section('page-subtitle', 'Theo dõi lịch khám sắp tới và lịch sử đặt lịch của bạn')

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

    .appointment-time {
        display: flex;
        flex-direction: column;
        gap: 6px;
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
    }

    .appointment-main {
        min-width: 0;
    }

    .appointment-service {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .appointment-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .appointment-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
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

    .empty-state h3 {
        font-family: var(--font-title);
        color: var(--text-main);
        font-size: 18px;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin-bottom: 18px;
    }

    .cancel-form {
        margin: 0;
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

        .appointment-item {
            padding: 18px;
        }

        .summary-card {
            padding: 18px;
        }
    }
</style>
@endsection

@section('content')
@php
    $upcomingCount = $appointments->count();
    $pastCount = $pastAppointments->count();
    $pendingCount = $appointments->where('status', 'pending')->count();

    function appointmentStatusClass($status) {
        return match ($status) {
            'pending' => 'status-pending',
            'confirmed' => 'status-confirmed',
            'completed' => 'status-completed',
            'cancelled' => 'status-cancelled',
            default => 'status-pending',
        };
    }

    function appointmentStatusIcon($status) {
        return match ($status) {
            'pending' => 'ri-time-line',
            'confirmed' => 'ri-checkbox-circle-line',
            'completed' => 'ri-check-double-line',
            'cancelled' => 'ri-close-circle-line',
            default => 'ri-information-line',
        };
    }

    function appointmentStatusText($status) {
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định',
        };
    }
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
                <div class="appointment-item">
                    <div class="appointment-time">
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

                    <div class="appointment-main">
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
                        </div>
                    </div>

                    <div class="appointment-actions">
                        <span class="status-badge {{ appointmentStatusClass($appointment->status) }}">
                            <i class="{{ appointmentStatusIcon($appointment->status) }}"></i>
                            {{ appointmentStatusText($appointment->status) }}
                        </span>

                        @if(!in_array($appointment->status, ['cancelled', 'completed']))
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
                <div class="appointment-item">
                    <div class="appointment-time">
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

                    <div class="appointment-main">
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
                        </div>
                    </div>

                    <div class="appointment-actions">
                        <span class="status-badge {{ appointmentStatusClass($appointment->status) }}">
                            <i class="{{ appointmentStatusIcon($appointment->status) }}"></i>
                            {{ appointmentStatusText($appointment->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection