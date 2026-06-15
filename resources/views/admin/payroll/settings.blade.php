@extends('layouts.admin-layout')

@section('title', 'Cấu hình tính lương')
@section('page-title', 'Cấu hình tính lương bác sĩ')
@section('page-subtitle', 'Thiết lập tiền theo giờ, hệ số học vị, hệ số ca và thời gian làm việc')

@php
    $indexUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.index')
        ? route('admin.payroll.index')
        : '#';

    $saveUrl = \Illuminate\Support\Facades\Route::has('admin.payroll.settings.save')
        ? route('admin.payroll.settings.save')
        : '#';

    $safeConfig = $config ?? null;

    $timeValue = function ($value, $default) {
        if (empty($value)) {
            return $default;
        }

        return substr((string) $value, 0, 5);
    };

    $numberValue = function ($value, $default = 0) {
        return $value !== null && $value !== '' ? $value : $default;
    };
@endphp

@section('header-actions')
    <a href="{{ $indexUrl }}" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>
@endsection

@section('styles')
<style>
    .payroll-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 22px;
        align-items: start;
    }

    .panel {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 18px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, .08);
        overflow: hidden;
        min-width: 0;
    }

    .panel-head {
        padding: 22px 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: var(--font-title);
        font-size: 22px;
        font-weight: 850;
        color: #0f172a;
        line-height: 1.25;
    }

    .panel-title i {
        color: #0ea5e9;
    }

    .panel-body {
        padding: 24px;
    }

    .error-list {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 16px;
        padding: 16px 18px;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .error-list ul {
        margin: 8px 0 0 20px;
    }

    .form-section {
        padding: 20px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        margin-bottom: 18px;
    }

    .section-title {
        font-family: var(--font-title);
        font-size: 18px;
        font-weight: 850;
        color: #0f172a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: #0ea5e9;
    }

    .section-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        margin: -8px 0 16px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .form-grid.three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .form-group {
        display: grid;
        gap: 7px;
        min-width: 0;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 13px;
        font-weight: 800;
        color: #334155;
    }

    .form-help {
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        line-height: 1.45;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 650;
        color: #0f172a;
        background: #fff;
        outline: none;
        min-height: 46px;
    }

    .form-input:focus,
    .form-textarea:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }

    .form-textarea {
        min-height: 90px;
        resize: vertical;
    }

    .toggle-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        background: #fff;
        font-weight: 800;
        color: #334155;
        line-height: 1.4;
    }

    .toggle-row input {
        width: 18px;
        height: 18px;
        accent-color: #0ea5e9;
        flex-shrink: 0;
    }

    .shift-box {
        border: 1px solid #dbe3ef;
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        display: grid;
        gap: 14px;
    }

    .shift-box-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }

    .shift-badge {
        padding: 6px 10px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .side-card {
        padding: 22px;
    }

    .current-box {
        display: grid;
        gap: 14px;
    }

    .metric {
        padding: 16px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        min-width: 0;
    }

    .metric span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .metric strong {
        color: #0f172a;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.25;
        word-break: break-word;
    }

    .metric small {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        margin-top: 4px;
        line-height: 1.45;
    }

    .hint {
        padding: 16px;
        border-radius: 16px;
        background: #ecfeff;
        border: 1px solid #bae6fd;
        color: #075985;
        font-weight: 700;
        line-height: 1.6;
        font-size: 13px;
    }

    .formula-box {
        padding: 16px;
        border-radius: 16px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.65;
    }

    .formula-box code {
        background: #ffedd5;
        padding: 2px 6px;
        border-radius: 7px;
        color: #7c2d12;
        font-weight: 850;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 8px;
    }

    @media (max-width: 1100px) {
        .payroll-grid {
            grid-template-columns: 1fr;
        }

        .form-grid,
        .form-grid.three {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .panel-body {
            padding: 18px;
        }

        .form-section {
            padding: 16px;
        }

        .actions {
            justify-content: stretch;
        }

        .actions .btn {
            width: 100%;
        }

        .shift-box-title {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
@if($errors->any())
    <div class="error-list">
        <div><i class="ri-error-warning-line"></i> Dữ liệu chưa hợp lệ:</div>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="payroll-grid">
    <form method="POST" action="{{ $saveUrl }}" class="panel">
        @csrf

        <div class="panel-head">
            <div class="panel-title">
                <i class="ri-settings-4-line"></i>
                Thiết lập cấu hình
            </div>
        </div>

        <div class="panel-body">
            <div class="form-section">
                <div class="section-title">
                    <i class="ri-money-dollar-circle-line"></i>
                    Tiền cơ bản
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tên cấu hình</label>
                        <input type="text"
                               name="name"
                               class="form-input"
                               value="{{ old('name', $safeConfig->name ?? 'Cấu hình lương mặc định') }}"
                               placeholder="VD: Cấu hình lương tháng 06/2026">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số tiền cơ bản cho một giờ</label>
                        <input type="number"
                               name="base_hourly_rate"
                               min="0"
                               step="1000"
                               class="form-input"
                               value="{{ old('base_hourly_rate', $numberValue($safeConfig->base_hourly_rate ?? null, 0)) }}"
                               required>
                        <div class="form-help">Đây là mức tiền/giờ dùng trong công thức tính lương theo ca.</div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-graduation-cap-line"></i>
                    Hệ số bác sĩ theo học vị
                </div>

                <div class="form-grid three">
                    <div class="form-group">
                        <label class="form-label">Đại học / Bác sĩ</label>
                        <input type="number"
                               name="degree_bachelor_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('degree_bachelor_coefficient', $numberValue($safeConfig->degree_bachelor_coefficient ?? null, 1.30)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Thạc sĩ</label>
                        <input type="number"
                               name="degree_master_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('degree_master_coefficient', $numberValue($safeConfig->degree_master_coefficient ?? null, 1.50)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiến sĩ</label>
                        <input type="number"
                               name="degree_doctor_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('degree_doctor_coefficient', $numberValue($safeConfig->degree_doctor_coefficient ?? null, 1.70)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phó giáo sư</label>
                        <input type="number"
                               name="degree_associate_professor_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('degree_associate_professor_coefficient', $numberValue($safeConfig->degree_associate_professor_coefficient ?? null, 2.00)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giáo sư</label>
                        <input type="number"
                               name="degree_professor_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('degree_professor_coefficient', $numberValue($safeConfig->degree_professor_coefficient ?? null, 2.50)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mặc định</label>
                        <input type="number"
                               name="degree_default_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('degree_default_coefficient', $numberValue($safeConfig->degree_default_coefficient ?? null, 1.30)) }}"
                               required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-calendar-schedule-line"></i>
                    Hệ số ca làm việc
                </div>

                <div class="section-subtitle">
                    Ca sáng và ca tối đều là ca hành chính của phòng khám. Vì vậy mặc định cả hai ca nên để hệ số 1.00.
                </div>

                <div class="form-grid three">
                    <div class="form-group">
                        <label class="form-label">Hệ số ca sáng</label>
                        <input type="number"
                               name="morning_shift_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('morning_shift_coefficient', $numberValue($safeConfig->morning_shift_coefficient ?? null, 1.00)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Hệ số ca tối</label>
                        <input type="number"
                               name="evening_shift_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('evening_shift_coefficient', $numberValue($safeConfig->evening_shift_coefficient ?? null, 1.00)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cuối tuần</label>
                        <input type="number"
                               name="weekend_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('weekend_coefficient', $numberValue($safeConfig->weekend_coefficient ?? null, 1.50)) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ngày thường trong giờ hành chính</label>
                        <input type="number"
                               name="weekday_office_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('weekday_office_coefficient', $numberValue($safeConfig->weekday_office_coefficient ?? null, 1.00)) }}"
                               required>
                        <div class="form-help">Giữ để tương thích logic cũ.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ngoài giờ hành chính</label>
                        <input type="number"
                               name="weekday_overtime_coefficient"
                               min="0"
                               max="10"
                               step="0.01"
                               class="form-input"
                               value="{{ old('weekday_overtime_coefficient', $numberValue($safeConfig->weekday_overtime_coefficient ?? null, 1.20)) }}"
                               required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="ri-time-line"></i>
                    Khung giờ ca làm việc và nghỉ giữa ca
                </div>

                <div class="section-subtitle">
                    Hệ thống đang dùng 2 ca hành chính: ca sáng 08:00 - 17:00 và ca tối 14:00 - 22:00.
                    Thời gian nghỉ giữa ca sẽ được trừ khỏi số giờ tính lương nếu được bật.
                </div>

                <div class="form-grid">
                    <div class="shift-box">
                        <div class="shift-box-title">
                            <span>Ca sáng</span>
                            <span class="shift-badge">08:00 - 17:00</span>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Bắt đầu ca sáng</label>
                                <input type="time"
                                       name="morning_shift_start"
                                       class="form-input"
                                       value="{{ old('morning_shift_start', $timeValue($safeConfig->morning_shift_start ?? null, '08:00')) }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kết thúc ca sáng</label>
                                <input type="time"
                                       name="morning_shift_end"
                                       class="form-input"
                                       value="{{ old('morning_shift_end', $timeValue($safeConfig->morning_shift_end ?? null, '17:00')) }}"
                                       required>
                            </div>

                            <div class="form-group full">
                                <label class="toggle-row">
                                    <input type="checkbox"
                                           name="exclude_lunch_break"
                                           value="1"
                                           @checked((bool) old('exclude_lunch_break', $safeConfig->exclude_lunch_break ?? true))>
                                    <span>Trừ thời gian nghỉ trưa khỏi số giờ tính lương ca sáng</span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Bắt đầu nghỉ trưa</label>
                                <input type="time"
                                       name="lunch_start_time"
                                       class="form-input"
                                       value="{{ old('lunch_start_time', $timeValue($safeConfig->lunch_start_time ?? null, '11:30')) }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kết thúc nghỉ trưa</label>
                                <input type="time"
                                       name="lunch_end_time"
                                       class="form-input"
                                       value="{{ old('lunch_end_time', $timeValue($safeConfig->lunch_end_time ?? null, '12:30')) }}"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="shift-box">
                        <div class="shift-box-title">
                            <span>Ca tối</span>
                            <span class="shift-badge">14:00 - 22:00</span>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Bắt đầu ca tối</label>
                                <input type="time"
                                       name="evening_shift_start"
                                       class="form-input"
                                       value="{{ old('evening_shift_start', $timeValue($safeConfig->evening_shift_start ?? null, '14:00')) }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kết thúc ca tối</label>
                                <input type="time"
                                       name="evening_shift_end"
                                       class="form-input"
                                       value="{{ old('evening_shift_end', $timeValue($safeConfig->evening_shift_end ?? null, '22:00')) }}"
                                       required>
                            </div>

                            <div class="form-group full">
                                <label class="toggle-row">
                                    <input type="checkbox"
                                           name="exclude_evening_break"
                                           value="1"
                                           @checked((bool) old('exclude_evening_break', $safeConfig->exclude_evening_break ?? true))>
                                    <span>Trừ thời gian nghỉ ca tối khỏi số giờ tính lương</span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Bắt đầu nghỉ ca tối</label>
                                <input type="time"
                                       name="evening_break_start_time"
                                       class="form-input"
                                       value="{{ old('evening_break_start_time', $timeValue($safeConfig->evening_break_start_time ?? null, '17:30')) }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Kết thúc nghỉ ca tối</label>
                                <input type="time"
                                       name="evening_break_end_time"
                                       class="form-input"
                                       value="{{ old('evening_break_end_time', $timeValue($safeConfig->evening_break_end_time ?? null, '18:00')) }}">
                            </div>
                        </div>
                    </div>

                    <input type="hidden"
                           name="office_start_time"
                           value="{{ old('office_start_time', $timeValue($safeConfig->office_start_time ?? null, '08:00')) }}">

                    <input type="hidden"
                           name="office_end_time"
                           value="{{ old('office_end_time', $timeValue($safeConfig->office_end_time ?? null, '17:00')) }}">

                    <div class="form-group full">
                        <label class="form-label">Ghi chú cấu hình</label>
                        <textarea name="notes" class="form-textarea" placeholder="Ghi chú quy định lương, thời điểm áp dụng...">{{ old('notes', $safeConfig->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary" @if($saveUrl === '#') disabled @endif>
                    <i class="ri-save-3-line"></i>
                    Lưu cấu hình mới
                </button>
            </div>
        </div>
    </form>

    <aside class="panel side-card">
        <div class="panel-title" style="margin-bottom:18px;">
            <i class="ri-information-line"></i>
            Cấu hình đang áp dụng
        </div>

        <div class="current-box">
            <div class="metric">
                <span>Tiền một giờ</span>
                <strong>{{ number_format((float) ($safeConfig->base_hourly_rate ?? 0), 0, ',', '.') }} đ</strong>
                <small>Mức tiền cơ bản để nhân với giờ quy đổi và hệ số bác sĩ.</small>
            </div>

            <div class="metric">
                <span>Hệ số ca sáng</span>
                <strong>{{ number_format((float) ($safeConfig->morning_shift_coefficient ?? $safeConfig->weekday_office_coefficient ?? 1), 2) }}</strong>
            </div>

            <div class="metric">
                <span>Hệ số ca tối</span>
                <strong>{{ number_format((float) ($safeConfig->evening_shift_coefficient ?? $safeConfig->weekday_office_coefficient ?? 1), 2) }}</strong>
            </div>

            <div class="metric">
                <span>Hệ số ngoài giờ</span>
                <strong>{{ number_format((float) ($safeConfig->weekday_overtime_coefficient ?? 1.2), 2) }}</strong>
            </div>

            <div class="metric">
                <span>Hệ số cuối tuần</span>
                <strong>{{ number_format((float) ($safeConfig->weekend_coefficient ?? 1.5), 2) }}</strong>
            </div>

            <div class="metric">
                <span>Ca sáng</span>
                <strong>
                    {{ $timeValue($safeConfig->morning_shift_start ?? null, '08:00') }}
                    -
                    {{ $timeValue($safeConfig->morning_shift_end ?? null, '17:00') }}
                </strong>
                <small>
                    Nghỉ trưa:
                    {{ $timeValue($safeConfig->lunch_start_time ?? null, '11:30') }}
                    -
                    {{ $timeValue($safeConfig->lunch_end_time ?? null, '12:30') }}
                </small>
            </div>

            <div class="metric">
                <span>Ca tối</span>
                <strong>
                    {{ $timeValue($safeConfig->evening_shift_start ?? null, '14:00') }}
                    -
                    {{ $timeValue($safeConfig->evening_shift_end ?? null, '22:00') }}
                </strong>
                <small>
                    Nghỉ ca tối:
                    {{ $timeValue($safeConfig->evening_break_start_time ?? null, '17:30') }}
                    -
                    {{ $timeValue($safeConfig->evening_break_end_time ?? null, '18:00') }}
                </small>
            </div>

            <div class="formula-box">
                <div><strong>Công thức đang dùng:</strong></div>
                <div>
                    <code>Tiền ca</code>
                    =
                    <code>Số giờ quy đổi</code>
                    x
                    <code>Hệ số bác sĩ</code>
                    x
                    <code>Tiền/giờ</code>
                </div>
                <div style="margin-top:8px;">
                    <code>Số giờ quy đổi</code>
                    =
                    <code>Số giờ ca</code>
                    x
                    <code>(Hệ số ca + Tổng hệ số bệnh nhân)</code>
                </div>
            </div>

            <div class="hint">
                Khi lưu cấu hình mới, cấu hình cũ không bị xóa mà chỉ chuyển sang ngừng áp dụng. Phiếu lương đã lập vẫn giữ snapshot cấu hình tại thời điểm lập.
            </div>
        </div>
    </aside>
</div>
@endsection