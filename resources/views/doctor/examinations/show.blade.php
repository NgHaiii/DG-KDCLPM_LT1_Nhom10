@extends('layouts.doctor-layout')

@section('title', 'Chi tiết ca khám')
@section('page-title', 'Chi tiết ca khám')
@section('page-subtitle', 'Cập nhật chẩn đoán, hướng điều trị và hoàn thành hồ sơ khám')

@section('styles')
<style>
    .layout {
        display: grid;
        grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.35fr);
        gap: 22px;
        align-items: start;
    }

    .panel {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
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

    .panel-body {
        padding: 22px;
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .info-item {
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        background: #f8fafc;
    }

    .info-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 800;
        color: var(--text-main);
        white-space: pre-line;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 7px;
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

    textarea.form-control {
        resize: vertical;
        min-height: 96px;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
    }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 800;
    }

    .status-progress {
        background: #ede9fe;
        color: #5b21b6;
        border: 1px solid #ddd6fe;
    }

    .status-completed {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .actions {
        margin-top: 18px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .notice {
        padding: 14px 16px;
        border-radius: var(--radius-md);
        margin-bottom: 18px;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-size: 14px;
        line-height: 1.5;
    }

    @media (max-width: 1100px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('header-actions')
<a href="{{ route('doctor.examinations.index') }}" class="btn btn-secondary">
    <i class="ri-arrow-left-line"></i>
    Quay lại
</a>
@endsection

@section('content')
@php
    $record = $appointment->medicalRecord;
    $isCompleted = $appointment->status === 'completed';
    $isInProgress = $appointment->status === 'in_progress';
@endphp

<div class="layout">
    <aside class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="ri-user-heart-line"></i>
                Thông tin ca khám
            </div>

            @if($isCompleted)
                <span class="status status-completed">
                    <i class="ri-check-line"></i>
                    Hoàn thành
                </span>
            @else
                <span class="status status-progress">
                    <i class="ri-pulse-line"></i>
                    Đang khám
                </span>
            @endif
        </div>

        <div class="panel-body">
            <div class="info-list">
                <div class="info-item">
                    <div class="info-label">Bệnh nhân</div>
                    <div class="info-value">{{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Dịch vụ</div>
                    <div class="info-value">{{ $appointment->service?->name ?? 'Dịch vụ không xác định' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Phòng khám</div>
                    <div class="info-value">{{ $appointment->room?->name ?? 'Chưa có phòng' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Số thứ tự</div>
                    <div class="info-value">{{ $appointment->queue_number ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Thời gian</div>
                    <div class="info-value">
                        Lịch hẹn: {{ $appointment->appointment_date?->format('d/m/Y H:i') ?? '-' }}
                        @if($appointment->checked_in_at)
                            Check-in: {{ $appointment->checked_in_at->format('d/m/Y H:i') }}
                        @endif
                        @if($appointment->started_at)
                            Bắt đầu: {{ $appointment->started_at->format('d/m/Y H:i') }}
                        @endif
                        @if($appointment->estimated_end_at)
                            Dự kiến xong: {{ $appointment->estimated_end_at->format('H:i') }}
                        @endif
                        @if($appointment->completed_at)
                            Hoàn thành: {{ $appointment->completed_at->format('d/m/Y H:i') }}
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Ghi chú ban đầu</div>
                    <div class="info-value">{{ $appointment->notes ?: 'Không có ghi chú' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <section class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <i class="ri-file-medical-line"></i>
                Hồ sơ khám bệnh
            </div>
        </div>

        <div class="panel-body">
            @if($isCompleted)
                <div class="notice">
                    Ca khám đã hoàn thành. Thông tin dưới đây là hồ sơ bác sĩ đã cập nhật.
                </div>
            @endif

            <form method="POST" action="{{ route('doctor.examinations.complete', $appointment->id) }}">
                @csrf

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Triệu chứng / lý do khám</label>
                        <textarea name="chief_complaint" class="form-control" rows="3" @disabled($isCompleted)>{{ old('chief_complaint', $record?->chief_complaint) }}</textarea>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Chẩn đoán <span style="color:#ef4444">*</span></label>
                        <textarea name="diagnosis" class="form-control" rows="4" required @disabled($isCompleted)>{{ old('diagnosis', $record?->diagnosis) }}</textarea>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Hướng điều trị</label>
                        <textarea name="treatment_plan" class="form-control" rows="4" @disabled($isCompleted)>{{ old('treatment_plan', $record?->treatment_plan) }}</textarea>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Đơn thuốc / dặn dò</label>
                        <textarea name="prescription" class="form-control" rows="4" @disabled($isCompleted)>{{ old('prescription', $record?->prescription) }}</textarea>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Ghi chú bác sĩ</label>
                        <textarea name="doctor_notes" class="form-control" rows="3" @disabled($isCompleted)>{{ old('doctor_notes', $record?->doctor_notes) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ngày tái khám</label>
                        <input type="date"
                               name="follow_up_date"
                               value="{{ old('follow_up_date', $record?->follow_up_date?->format('Y-m-d')) }}"
                               class="form-control"
                               @disabled($isCompleted)>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('doctor.examinations.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line"></i>
                        Quay lại
                    </a>

                    @if($isInProgress)
                        <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Hoàn thành ca khám và lưu hồ sơ bệnh án?');">
                            <i class="ri-check-double-line"></i>
                            Hoàn thành khám
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </section>
</div>
@endsection