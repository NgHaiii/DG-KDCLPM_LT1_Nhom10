@extends('layouts.doctor-layout')

@section('title', 'Đăng ký ca làm việc & ngày nghỉ')

@section('page-title', 'Quản lý lịch trình')
@section('page-subtitle', 'Đăng ký ca làm việc hoặc xin ngày nghỉ')

@section('content')

<style>
    /* Stats */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-left: 5px solid;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .stat-card.pending { border-left-color: #f59e0b; }
    .stat-card.approved { border-left-color: #10b981; }
    .stat-card.offday { border-left-color: #0ea5e9; }

    .stat-card p:first-child {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .stat-card p:last-child {
        font-size: 2.25rem;
        font-weight: 700;
    }

    .stat-card.pending p:last-child { color: #f59e0b; }
    .stat-card.approved p:last-child { color: #10b981; }
    .stat-card.offday p:last-child { color: #0ea5e9; }

    /* Tabs */
    .tabs-nav {
        display: flex;
        gap: 1rem;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 2rem;
        background: white;
        padding: 0 1rem;
        border-radius: 12px 12px 0 0;
        overflow-x: auto;
    }

    .tab-btn {
        padding: 1rem 1.5rem;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: #3b82f6;
    }

    .tab-btn.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    /* Tab Content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Calendar */
    .calendar-container {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .calendar-header h3 {
        font-size: 1.1rem;
        margin: 0;
        color: #111827;
    }

    .calendar-nav {
        display: flex;
        gap: 0.5rem;
    }

    .calendar-nav button {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.75rem;
    }

    .calendar-nav button:hover {
        background: #1e40af;
    }

    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.2rem;
        margin-bottom: 0.3rem;
    }

    .weekday {
        padding: 0.3rem;
        text-align: center;
        font-weight: 700;
        color: #6b7280;
        background: #f3f4f6;
        border-radius: 4px;
        font-size: 0.65rem;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.2rem;
    }

    .calendar-day {
        aspect-ratio: 1;
        padding: 0.3rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        font-size: 0.7rem;
    }

    .calendar-day:hover {
        border-color: #3b82f6;
        background: #f0f9ff;
        transform: scale(1.05);
    }

    .calendar-day.other-month {
        color: #d1d5db;
        background: #f9fafb;
        cursor: not-allowed;
    }

    .calendar-day.today {
        background: #dbeafe;
        border-color: #3b82f6;
        font-weight: 700;
        color: #1e40af;
    }

    .calendar-day.registered {
        background: linear-gradient(135deg, #fef08a 0%, #fef3c7 100%);
        border-color: #f59e0b;
        font-weight: 600;
    }

    .calendar-day.registered-approved {
        background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
        border-color: #10b981;
        font-weight: 600;
    }

    .calendar-day-number {
        font-size: 0.75rem;
        font-weight: 600;
    }

    .calendar-day-badge {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #f59e0b;
    }

    .calendar-day.registered-approved .calendar-day-badge {
        background: #10b981;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .detail-modal-content {
        background-color: white;
        padding: 1.5rem;
        border-radius: 12px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease;
        position: relative;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #111827;
    }

    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
    }

    .detail-item {
        padding: 0.75rem;
        background: #f0f9ff;
        border-left: 4px solid #0ea5e9;
        margin-bottom: 0.75rem;
        border-radius: 4px;
    }

    .detail-item label {
        font-weight: 600;
        color: #0c4a6e;
        font-size: 0.85rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    .detail-item p {
        margin: 0;
        color: #0c4a6e;
        font-size: 0.8rem;
    }

    /* Form */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .hour-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 0.75rem;
    }

    .hour-inputs .form-group {
        margin-bottom: 0;
    }

    .custom-hours-header {
        background: #f0f4ff;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #3b82f6;
    }

    .custom-hours-header p {
        font-size: 0.875rem;
        color: #3b82f6;
        font-weight: 600;
        margin: 0;
    }

    .btn-submit {
        width: 100%;
        padding: 0.875rem 1rem;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    }

    .btn-submit:active {
        transform: scale(0.98);
    }

    .btn-edit {
        width: 100%;
        padding: 0.875rem 1rem;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        margin-top: 0.5rem;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
    }

    .btn-cancel {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        color: #6b7280;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 0.5rem;
    }

    .btn-cancel:hover {
        border-color: #6b7280;
        color: #374151;
    }

    /* Off-Day Form & Requests */
    .offday-form {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        border-top: 4px solid #ec4899;
    }

    .offday-requests-container {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-top: 4px solid #3b82f6;
        margin-bottom: 2rem;
    }

    .offday-request-card {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        border-left: 5px solid;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .offday-request-card.pending {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .offday-request-card.pending:hover {
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.15);
        transform: translateY(-2px);
    }

    .offday-request-card.approved {
        border-left-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    }

    .offday-request-card.approved:hover {
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
        transform: translateY(-2px);
    }

    .offday-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .offday-card-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .date-badge {
        background: white;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.9rem;
        border: 2px solid #e5e7eb;
    }

    .offday-request-card.pending .date-badge {
        color: #f59e0b;
        border-color: #f59e0b;
    }

    .offday-request-card.approved .date-badge {
        color: #10b981;
        border-color: #10b981;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-badge.pending-badge {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.approved-badge {
        background: #dcfce7;
        color: #065f46;
    }

    .offday-card-body {
        margin: 0.75rem 0;
    }

    .offday-card-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid;
    }

    .btn-action.edit {
        background: white;
        color: #f59e0b;
        border-color: #f59e0b;
    }

    .btn-action.edit:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }

    .btn-action.delete {
        background: white;
        color: #ef4444;
        border-color: #ef4444;
    }

    .btn-action.delete:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    textarea#reason,
    textarea#editReason {
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
    }

    .char-counter {
        font-size: 0.8rem;
        color: #9ca3af;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .calendar-day {
            padding: 0.2rem;
            font-size: 0.6rem;
        }

        .calendar-day-number {
            font-size: 0.6rem;
        }

        .modal-content {
            max-width: 90vw;
        }

        .hour-inputs {
            grid-template-columns: 1fr 1fr;
        }

        .offday-card-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Stats -->
<div class="stats-container">
    <div class="stat-card pending">
        <p>⏳ Đơn ca chờ duyệt</p>
        <p>{{ $pendingRequests->count() }}</p>
    </div>

    <div class="stat-card approved">
        <p>✅ Ca được duyệt</p>
        <p>{{ $approvedRequests->count() }}</p>
    </div>

    <div class="stat-card offday">
        <p>🏖️ Ngày nghỉ được duyệt</p>
        <p>{{ $approvedOffDays->count() }}</p>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-nav">
    <button class="tab-btn active" data-tab="tab-shift">📅 Lịch đăng ký ca</button>
    <button class="tab-btn" data-tab="tab-offday">🏖️ Xin ngày nghỉ</button>
</div>

<!-- Tab 1: Shift Calendar -->
<div id="tab-shift" class="tab-content active">
    <div class="calendar-container">
        <div class="calendar-header">
            <h3 id="monthYear">Tháng 5 - 2026</h3>
            <div class="calendar-nav">
                <button type="button" id="prevMonth">← Tháng trước</button>
                <button type="button" id="nextMonth">Tháng sau →</button>
            </div>
        </div>

        <div class="calendar-weekdays">
            <div class="weekday">T2</div>
            <div class="weekday">T3</div>
            <div class="weekday">T4</div>
            <div class="weekday">T5</div>
            <div class="weekday">T6</div>
            <div class="weekday">T7</div>
            <div class="weekday">CN</div>
        </div>

        <div class="calendar-days" id="calendarDays"></div>
    </div>
</div>

<!-- Tab 2: Off-Day -->
<div id="tab-offday" class="tab-content">
    <!-- Off-Day Form -->
    <div class="offday-form">
        <div style="margin-bottom: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; color: #111827;">🏖️ Đơn Xin Ngày Nghỉ</h3>
            <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Gửi đơn xin phép nghỉ và chờ quản lý phê duyệt</p>
        </div>

        <form action="{{ route('doctor.schedule.request-off-day') }}" method="POST" id="offdayForm">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>📅 Từ ngày <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="start_date" id="startDate" required style="font-weight: 500;">
                </div>

                <div class="form-group">
                    <label>📅 Đến ngày <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="end_date" id="endDate" required style="font-weight: 500;">
                </div>
            </div>

            <div class="form-group">
                <label>📝 Lý do xin nghỉ <span style="color: #ef4444;">*</span></label>
                <textarea name="reason" id="reason" rows="4" placeholder="Nhập lý do xin nghỉ (tối thiểu 5 ký tự)..." required style="resize: vertical;"></textarea>
                <p class="char-counter">0 / 200 ký tự</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button type="submit" class="btn-submit">✅ Gửi đơn xin nghỉ</button>
                <button type="reset" class="btn-cancel">🔄 Xóa</button>
            </div>
        </form>
    </div>

    <!-- Pending Off-Day Requests -->
    @if($pendingOffDays && $pendingOffDays->count() > 0)
        <div class="offday-requests-container">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #f59e0b; display: flex; align-items: center; gap: 0.5rem;">
                    ⏳ Đơn xin nghỉ chờ duyệt ({{ $pendingOffDays->count() }})
                </h3>
                <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Nhấn để chỉnh sửa hoặc hủy đơn xin nghỉ</p>
            </div>

            <div style="display: grid; gap: 1rem;">
                @foreach($pendingOffDays as $offday)
                    <div class="offday-request-card pending">
                        <div class="offday-card-header">
                            <div class="offday-card-date">
                                <span class="date-badge">{{ optional($offday->date)->format('d/m') }}</span>
                            </div>
                            <span class="status-badge pending-badge">⏳ Chờ duyệt</span>
                        </div>

                        <div class="offday-card-body">
                            <p style="color: #111827; font-weight: 600; margin: 0.5rem 0 0 0;">{{ $offday->reason ?? 'Không có lý do' }}</p>
                            <p style="color: #6b7280; font-size: 0.85rem; margin: 0.5rem 0;">
                                📆 {{ optional($offday->date)->format('d/m/Y') }} • 
                                Gửi lúc {{ optional($offday->created_at)->format('d/m/Y H:i') ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="offday-card-actions">
                            <button type="button" class="btn-action edit" onclick="editOffDay({{ $offday->id }}, '{{ optional($offday->date)->format('Y-m-d') }}', '{{ optional($offday->date)->format('Y-m-d') }}', '{{ addslashes($offday->reason ?? '') }}')">
                                ✏️ Chỉnh sửa
                            </button>
                            <button type="button" class="btn-action delete" onclick="deleteOffDay({{ $offday->id }})">
                                🗑️ Hủy đơn
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Approved Off-Day Requests -->
    @if($approvedOffDays->count() > 0)
        <div class="offday-requests-container">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #10b981; display: flex; align-items: center; gap: 0.5rem;">
                    ✅ Đơn xin nghỉ đã duyệt ({{ $approvedOffDays->count() }})
                </h3>
            </div>

            <div style="display: grid; gap: 1rem;">
                @foreach($approvedOffDays as $offday)
                    <div class="offday-request-card approved">
                        <div class="offday-card-header">
                            <div class="offday-card-date">
                                <span class="date-badge">{{ optional($offday->date)->format('d/m/Y') }}</span>
                            </div>
                            <span class="status-badge approved-badge">✅ Đã duyệt</span>
                        </div>

                        <div class="offday-card-body">
                            <p style="color: #111827; font-weight: 600; margin: 0.5rem 0 0 0;">{{ $offday->reason ?? 'Không có lý do' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Modal Đăng ký/Cập nhật -->
<div id="shiftModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-header" id="modalTitle">📋 Đăng ký ca làm việc</div>

        <form action="{{ route('doctor.schedule.store') }}" method="POST" id="shiftForm">
            @csrf
            <input type="hidden" id="methodInput" name="_method" value="POST">

            <div class="form-group">
                <label>📅 Ngày làm việc</label>
                <input type="date" id="workDate" name="work_date" required readonly style="background: #f3f4f6; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label>⏰ Chọn ca làm việc <span style="color: red;">*</span></label>
                <select id="shiftId" name="shift_id" required>
                    <option value="">-- Chọn ca --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" 
                                data-start-hour="{{ $shift->start_hour ?? 0 }}" 
                                data-start-minute="{{ $shift->start_minute ?? 0 }}" 
                                data-end-hour="{{ $shift->end_hour ?? 0 }}" 
                                data-end-minute="{{ $shift->end_minute ?? 0 }}">
                            {{ $shift->name }} ({{ sprintf('%02d:%02d', $shift->start_hour ?? 0, $shift->start_minute ?? 0) }} - {{ sprintf('%02d:%02d', $shift->end_hour ?? 0, $shift->end_minute ?? 0) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="customHours" style="display: none;">
                <div class="custom-hours-header">
                    <p>✏️ Tùy chỉnh giờ làm việc</p>
                </div>
                
                <div class="hour-inputs">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Từ giờ</label>
                        <select id="startHour" name="start_hour">
                            <option value="">Chọn giờ</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Từ phút</label>
                        <select id="startMinute" name="start_minute">
                            <option value="">Chọn phút</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Đến giờ</label>
                        <select id="endHour" name="end_hour">
                            <option value="">Chọn giờ</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Đến phút</label>
                        <select id="endMinute" name="end_minute">
                            <option value="">Chọn phút</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">✅ Đăng ký ca</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">❌ Hủy</button>
        </form>
    </div>
</div>

<!-- Modal Chi tiết ca đã đăng ký -->
<div id="detailModal" class="modal">
    <div class="detail-modal-content">
        <button type="button" class="modal-close" onclick="closeDetailModal()">✕</button>
        <div class="modal-header" style="color: #111827; font-size: 1.25rem;">📋 Thông tin ca làm việc</div>
        <div id="detailContent"></div>
        <button type="button" class="btn-edit" onclick="openEditModal()">✏️ Cập nhật ca làm việc</button>
        <button type="button" class="btn-cancel" onclick="closeDetailModal()">Đóng</button>
    </div>
</div>

<!-- Modal Chỉnh Sửa Đơn Xin Nghỉ -->
<div id="editOffdayModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeEditOffdayModal()">✕</button>
        <div class="modal-header">✏️ Chỉnh sửa đơn xin nghỉ</div>

        <form action="" method="POST" id="editOffdayForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>📅 Ngày xin nghỉ <span style="color: #ef4444;">*</span></label>
                <input type="date" name="start_date" id="editStartDate" required>
            </div>

            <div class="form-group">
                <label>📝 Lý do xin nghỉ <span style="color: #ef4444;">*</span></label>
                <textarea name="reason" id="editReason" rows="4" required></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button type="submit" class="btn-submit">✅ Cập nhật</button>
                <button type="button" class="btn-cancel" onclick="closeEditOffdayModal()">❌ Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let selectedDate = null;
    let editingSchedule = null;

    const registeredDates = {
        pending: [
            @foreach($pendingRequests as $req)
                '{{ $req->work_date->format("Y-m-d") }}',
            @endforeach
        ],
        approved: [
            @foreach($approvedRequests as $sch)
                '{{ $sch->work_date->format("Y-m-d") }}',
            @endforeach
        ]
    };

    const allSchedulesData = {
        pending: {!! json_encode($pendingRequests->map(function($r) {
            return [
                'id' => $r->id,
                'date' => $r->work_date->format('Y-m-d'),
                'name' => optional($r->shift)->name ?? 'Ca làm việc',
                'time' => sprintf('%02d:%02d', $r->start_hour ?? 0, $r->start_minute ?? 0) . ' - ' . sprintf('%02d:%02d', $r->end_hour ?? 0, $r->end_minute ?? 0),
                'shift_id' => $r->shift_id,
                'status' => 'pending',
                'start_hour' => $r->start_hour,
                'start_minute' => $r->start_minute,
                'end_hour' => $r->end_hour,
                'end_minute' => $r->end_minute,
            ];
        })) !!},
        approved: {!! json_encode($approvedRequests->map(function($s) {
            return [
                'id' => $s->id,
                'date' => $s->work_date->format('Y-m-d'),
                'name' => optional($s->shift)->name ?? 'Ca làm việc',
                'time' => sprintf('%02d:%02d', $s->start_hour ?? 0, $s->start_minute ?? 0) . ' - ' . sprintf('%02d:%02d', $s->end_hour ?? 0, $s->end_minute ?? 0),
                'shift_id' => $s->shift_id,
                'status' => 'approved',
                'start_hour' => $s->start_hour,
                'start_minute' => $s->start_minute,
                'end_hour' => $s->end_hour,
                'end_minute' => $s->end_minute,
            ];
        })) !!}
    };

    function formatDate(year, month, day) {
        const m = String(month + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        return `${year}-${m}-${d}`;
    }

    function populateHours() {
        const startHour = document.getElementById('startHour');
        const startMinute = document.getElementById('startMinute');
        const endHour = document.getElementById('endHour');
        const endMinute = document.getElementById('endMinute');
        
        startHour.innerHTML = '<option value="">Chọn giờ</option>';
        endHour.innerHTML = '<option value="">Chọn giờ</option>';
        startMinute.innerHTML = '<option value="">Chọn phút</option>';
        endMinute.innerHTML = '<option value="">Chọn phút</option>';

        for (let i = 0; i <= 23; i++) {
            const option1 = document.createElement('option');
            option1.value = i;
            option1.textContent = `${String(i).padStart(2, '0')}h`;
            startHour.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = i;
            option2.textContent = `${String(i).padStart(2, '0')}h`;
            endHour.appendChild(option2);
        }
        
        for (let i = 0; i < 60; i++) {
            const option1 = document.createElement('option');
            option1.value = i;
            option1.textContent = `${String(i).padStart(2, '0')} phút`;
            startMinute.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = i;
            option2.textContent = `${String(i).padStart(2, '0')} phút`;
            endMinute.appendChild(option2);
        }
    }

    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const today = new Date();
        const todayDate = formatDate(today.getFullYear(), today.getMonth(), today.getDate());
        
        const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                           'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        
        document.getElementById('monthYear').textContent = `${monthNames[currentMonth]} - ${currentYear}`;

        const calendarDays = document.getElementById('calendarDays');
        calendarDays.innerHTML = '';

        const prevLastDay = new Date(currentYear, currentMonth, 0).getDate();
        const firstDayOfWeek = firstDay.getDay() || 7;
        
        for (let i = prevLastDay - firstDayOfWeek + 2; i <= prevLastDay; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.innerHTML = `<div class="calendar-day-number">${i}</div>`;
            calendarDays.appendChild(day);
        }

        for (let i = 1; i <= lastDay.getDate(); i++) {
            const day = document.createElement('div');
            const dateStr = formatDate(currentYear, currentMonth, i);
            
            day.className = 'calendar-day';
            
            if (dateStr === todayDate) {
                day.classList.add('today');
            }

            const isApproved = registeredDates.approved.includes(dateStr);
            const isPending = registeredDates.pending.includes(dateStr);

            if (isApproved) {
                day.classList.add('registered-approved');
                day.innerHTML = `<div class="calendar-day-number">${i}</div><div class="calendar-day-badge"></div>`;
            } else if (isPending) {
                day.classList.add('registered');
                day.innerHTML = `<div class="calendar-day-number">${i}</div><div class="calendar-day-badge"></div>`;
            } else {
                day.innerHTML = `<div class="calendar-day-number">${i}</div>`;
            }
            
            day.style.cursor = 'pointer';
            day.onclick = () => {
                if (isApproved || isPending) {
                    showDetailModal(dateStr);
                } else {
                    openModal(dateStr, false);
                }
            };
            
            calendarDays.appendChild(day);
        }

        const totalCells = calendarDays.children.length;
        const nextDaysCount = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        
        for (let i = 1; i <= nextDaysCount; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.innerHTML = `<div class="calendar-day-number">${i}</div>`;
            calendarDays.appendChild(day);
        }
    }

    function openModal(dateStr, isEdit = false) {
        selectedDate = dateStr;
        document.getElementById('workDate').value = dateStr;
        document.getElementById('shiftId').value = '';
        document.getElementById('startHour').value = '';
        document.getElementById('startMinute').value = '';
        document.getElementById('endHour').value = '';
        document.getElementById('endMinute').value = '';
        document.getElementById('customHours').style.display = 'none';
        document.getElementById('methodInput').value = 'POST';
        document.getElementById('shiftForm').action = "{{ route('doctor.schedule.store') }}";
        
        if (isEdit && editingSchedule) {
            document.getElementById('modalTitle').textContent = '✏️ Cập nhật ca làm việc';
            document.getElementById('submitBtn').textContent = '✅ Cập nhật ca';
            document.getElementById('workDate').value = editingSchedule.date;
            document.getElementById('shiftId').value = editingSchedule.shift_id;
            document.getElementById('methodInput').value = 'PUT';
            
            const actionUrl = "{{ url('doctor/schedule') }}/" + editingSchedule.id;
            document.getElementById('shiftForm').action = actionUrl;
            
            setTimeout(() => {
                document.getElementById('startHour').value = editingSchedule.start_hour || '';
                document.getElementById('startMinute').value = editingSchedule.start_minute || '';
                document.getElementById('endHour').value = editingSchedule.end_hour || '';
                document.getElementById('endMinute').value = editingSchedule.end_minute || '';
                
                document.getElementById('customHours').style.display = 'block';
                document.getElementById('shiftId').dispatchEvent(new Event('change'));
            }, 100);
        } else {
            document.getElementById('modalTitle').textContent = '📋 Đăng ký ca làm việc';
            document.getElementById('submitBtn').textContent = '✅ Đăng ký ca';
        }
        
        document.getElementById('shiftModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('shiftModal').classList.remove('active');
    }

    function showDetailModal(dateStr) {
        const allSchedules = [...allSchedulesData.approved, ...allSchedulesData.pending];
        const schedule = allSchedules.find(s => s.date === dateStr);

        if (!schedule) return;

        editingSchedule = schedule;

        const statusText = schedule.status === 'approved' ? '✅ Đã duyệt' : '⏳ Chờ duyệt';
        const statusColor = schedule.status === 'approved' ? '#10b981' : '#f59e0b';
        const date = new Date(dateStr + 'T00:00:00').toLocaleDateString('vi-VN');

        const html = `
            <div class="detail-item">
                <label>📅 Ngày làm việc</label>
                <p>${date}</p>
            </div>
            <div class="detail-item">
                <label>⏰ Ca làm việc</label>
                <p>${schedule.name}</p>
            </div>
            <div class="detail-item">
                <label>🕐 Thời gian</label>
                <p>${schedule.time}</p>
            </div>
            <div class="detail-item" style="border-left-color: ${statusColor}; background: ${statusColor}20;">
                <label style="color: ${statusColor};">Trạng thái</label>
                <p style="color: ${statusColor};">${statusText}</p>
            </div>
        `;

        document.getElementById('detailContent').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.remove('active');
    }

    function openEditModal() {
        if (!editingSchedule) {
            alert('Không tìm thấy thông tin ca làm việc');
            return;
        }
        
        closeDetailModal();
        
        setTimeout(() => {
            openModal(editingSchedule.date, true);
        }, 100);
    }

    function editOffDay(id, startDate, endDate, reason) {
        document.getElementById('editStartDate').value = startDate;
        document.getElementById('editEndDate').value = endDate;
        document.getElementById('editReason').value = reason;
        document.getElementById('editOffdayForm').action = "{{ url('doctor/schedule/off-day') }}/" + id;
        document.getElementById('editOffdayModal').classList.add('active');
    }

    function closeEditOffdayModal() {
        document.getElementById('editOffdayModal').classList.remove('active');
    }

    function deleteOffDay(id) {
        if (confirm('Bạn chắc chắn muốn hủy đơn xin nghỉ này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ url('doctor/schedule/off-day') }}/" + id;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Character counter for reason field
    const reasonField = document.getElementById('reason');
    if (reasonField) {
        reasonField.addEventListener('input', function() {
            const counter = this.parentElement.querySelector('.char-counter');
            if (counter) {
                counter.textContent = this.value.length + ' / 200 ký tự';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const shiftId = document.getElementById('shiftId');
        const customHours = document.getElementById('customHours');

        if (shiftId) {
            shiftId.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                
                if (option.value && option.dataset.startHour) {
                    document.getElementById('startHour').value = option.dataset.startHour;
                    document.getElementById('startMinute').value = option.dataset.startMinute;
                    document.getElementById('endHour').value = option.dataset.endHour;
                    document.getElementById('endMinute').value = option.dataset.endMinute;
                    customHours.style.display = 'block';
                } else if (!option.value) {
                    customHours.style.display = 'none';
                }
            });
        }

        populateHours();
    });

    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            document.getElementById(tabId).classList.add('active');
            this.classList.add('active');
        });
    });

    window.addEventListener('click', (e) => {
        const modal = document.getElementById('shiftModal');
        const detailModal = document.getElementById('detailModal');
        const editOffdayModal = document.getElementById('editOffdayModal');
        if (e.target === modal) closeModal();
        if (e.target === detailModal) closeDetailModal();
        if (e.target === editOffdayModal) closeEditOffdayModal();
    });

    renderCalendar();
</script>

@endsection