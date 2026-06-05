@extends('layouts.doctor-layout')

@section('title', 'Đăng ký ca làm việc & ngày nghỉ')

@section('page-title', 'Quản lý lịch trình')
@section('page-subtitle', 'Đăng ký ca làm việc hoặc xin ngày nghỉ')

@section('content')

@php
    if (request('week_start')) {
        try {
            $weekStart = \Carbon\Carbon::createFromFormat('Y-m-d', request('week_start'))->startOfWeek(\Carbon\Carbon::MONDAY);
        } catch (\Exception $e) {
            $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY);
        }
    } else {
        $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY);
    }
    $weekEnd = $weekStart->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
@endphp

<style>
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

    .tab-btn:hover { color: #3b82f6; }
    .tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .calendar-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .week-navigation {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .week-nav-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }

    .week-nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .week-display {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111827;
        min-width: 200px;
        text-align: center;
        padding: 0.5rem 1rem;
        background: #f0f4ff;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
    }

    .legend-section {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    .legend-dot.registered { background: #10b981; }
    .legend-dot.pending { background: #f59e0b; }
    .legend-dot.empty { background: #d1d5db; }

    .calendar-grid {
        display: grid;
        grid-template-columns: 150px repeat(7, 1fr);
        gap: 1px;
        background: #d1d5db;
        padding: 1px;
        border-radius: 8px;
        overflow-x: auto;
        transition: opacity 0.3s ease;
    }

    .grid-time-header {
        background: #f3f4f6;
        padding: 1rem 0.75rem;
        font-weight: 700;
        font-size: 0.85rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 110px;
    }

    .grid-time-icon { font-size: 1.8rem; }
    .grid-time-label { color: #111827; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
    .grid-time-hours { font-size: 0.7rem; color: #6b7280; }

    .grid-day-header {
        background: #dbeafe;
        padding: 1rem 0.75rem;
        text-align: center;
        font-weight: 700;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 0.3rem;
        min-height: 100px;
    }

    .grid-day-header.today { background: #3b82f6; color: white; }
    .grid-day-header.sunday { background: #fee2e2; }
    .grid-day-header.sunday .day-name, .grid-day-header.sunday .day-number { color: #dc2626; }

    .day-name {
        font-size: 0.7rem;
        color: #3b82f6;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .grid-day-header.today .day-name { color: white; }

    .day-number {
        font-size: 1.4rem;
        color: #3b82f6;
    }

    .grid-day-header.today .day-number { color: white; }

    .grid-cell {
        background: white;
        padding: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 110px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .grid-cell:hover { background: #f0f9ff; }

    .shift-card {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        padding: 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid;
        line-height: 1.3;
    }

    .shift-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .shift-card.registered {
        background: #dcfce7;
        border-color: #10b981;
        color: #065f46;
    }

    .shift-card.pending {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }

    .shift-card.empty {
        background: white;
        border: 2px dashed #d1d5db;
        color: #9ca3af;
    }

    .shift-card.empty:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        background: #f0f9ff;
    }

    .shift-icon {
        font-size: 1.4rem;
        margin-bottom: 0.2rem;
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 2rem;
    }

    .spinner {
        border: 4px solid #e5e7eb;
        border-top: 4px solid #3b82f6;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        margin-top: 1rem;
        color: #6b7280;
        font-weight: 600;
    }

    #weekPicker {
        padding: 0.6rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        background: white;
        transition: all 0.3s ease;
    }

    #weekPicker:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    }

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

    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
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
        display: none;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 0.75rem;
    }

    .hour-inputs.active { display: grid; }
    .hour-inputs .form-group { margin-bottom: 0; }

    .custom-hours-header {
        background: #f0f4ff;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #3b82f6;
        display: none;
    }

    .custom-hours-header.active { display: block; }
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

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-edit, .btn-delete, .btn-cancel {
        width: 100%;
        padding: 0.875rem 1rem;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 0.5rem;
    }

    .btn-edit {
        color: white;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
    }

    .btn-cancel {
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
    }

    .btn-cancel:hover {
        border-color: #6b7280;
        color: #374151;
    }

    .btn-delete {
        color: white;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
    }

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

    .offday-request-card.approved {
        border-left-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
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

    .offday-card-body { margin: 0.75rem 0; }

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
        border: 2px solid;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .btn-action.edit {
        color: #f59e0b;
        border-color: #f59e0b;
    }

    .btn-action.edit:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-2px);
    }

    .btn-action.delete {
        color: #ef4444;
        border-color: #ef4444;
    }

    .btn-action.delete:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
    }

    textarea#reason, textarea#editReason {
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
        .calendar-grid { grid-template-columns: 80px repeat(7, 1fr); }
        .modal-content { max-width: 90vw; }
        .hour-inputs { grid-template-columns: 1fr 1fr; }
        .offday-card-actions { grid-template-columns: 1fr; }
        .calendar-header { flex-direction: column; align-items: flex-start; }
        .week-navigation { flex-direction: column; width: 100%; }
        .week-display { width: 100%; }
    }
</style>

<!-- Stats -->
<div class="stats-container">
    <div class="stat-card pending">
        <p>⏳ Đơn ca chờ duyệt</p>
        <p id="pendingCount">{{ $pendingRequests->count() }}</p>
    </div>
    <div class="stat-card approved">
        <p>✅ Ca được duyệt</p>
        <p id="approvedCount">{{ $approvedRequests->count() }}</p>
    </div>
    <div class="stat-card offday">
        <p>🏖️ Ngày nghỉ được duyệt</p>
        <p id="offDayCount">{{ $approvedOffDays->count() }}</p>
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
            <div class="week-navigation">
                <button type="button" class="week-nav-btn" onclick="previousWeek()">← Tuần trước</button>
                <div class="week-display" id="weekDisplay" data-week-start="{{ $weekStart->format('Y-m-d') }}">
                    {{ $weekStart->format('d/m') }} - {{ $weekEnd->format('d/m/Y') }}
                </div>
                <button type="button" class="week-nav-btn" onclick="nextWeek()">Tuần sau →</button>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <input type="date" id="weekPicker" title="Chọn ngày để đi đến tuần đó">
                <div class="legend-section">
                    <div class="legend-item">
                        <div class="legend-dot registered"></div>
                        <span>Đã đăng ký</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot pending"></div>
                        <span>Chờ duyệt</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot empty"></div>
                        <span>Còn trống</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="loadingSpinner" class="loading-spinner">
            <div class="spinner"></div>
            <p class="loading-text">⏳ Đang tải lịch...</p>
        </div>

        <div class="calendar-grid" id="calendarGrid">
            <!-- Calendar will be generated by JavaScript -->
        </div>
    </div>
</div>

<!-- Tab 2: Off-Day -->
<div id="tab-offday" class="tab-content">
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
                    <input type="date" name="start_date" id="startDate" required>
                </div>

                <div class="form-group">
                    <label>📅 Đến ngày <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="end_date" id="endDate" required>
                </div>
            </div>

            <div class="form-group">
                <label>📝 Lý do xin nghỉ <span style="color: #ef4444;">*</span></label>
                <textarea name="reason" id="reason" rows="4" placeholder="Nhập lý do xin nghỉ..." required></textarea>
                <p class="char-counter">0 / 200 ký tự</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button type="submit" class="btn-submit">✅ Gửi đơn xin nghỉ</button>
                <button type="reset" class="btn-cancel">🔄 Xóa</button>
            </div>
        </form>
    </div>

    @if($pendingOffDays && $pendingOffDays->count() > 0)
        <div class="offday-requests-container">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #f59e0b;">
                    ⏳ Đơn xin nghỉ chờ duyệt ({{ $pendingOffDays->count() }})
                </h3>
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
                        </div>
                        <div class="offday-card-actions">
                            <button type="button" class="btn-action edit" onclick="editOffDay({{ $offday->id }}, '{{ optional($offday->date)->format('Y-m-d') }}', '{{ addslashes($offday->reason ?? '') }}')">
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

    @if($approvedOffDays->count() > 0)
        <div class="offday-requests-container">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #10b981;">
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

<!-- Modal Shift -->
<div id="shiftModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-header" id="modalTitle">✏️ Tùy chỉnh giờ làm việc</div>

        <form id="shiftForm" method="POST">
            @csrf
            <input type="hidden" id="methodInput" name="_method" value="POST">

            <div class="form-group">
                <label>📅 Ngày làm việc</label>
                <input type="date" id="workDate" name="work_date" required readonly style="background: #f3f4f6; cursor: not-allowed;">
            </div>

            <div class="form-group" id="shiftTypeFormGroup">
                <label>Chọn loại ca <span style="color: #ef4444;">*</span></label>
                <select id="shiftTypeSelect" name="shift_type" onchange="onShiftTypeChange()">
                    <option value="">-- Chọn loại ca --</option>
                    <option value="morning">☀️ Ca sáng (08:00 - 17:00)</option>
                    <option value="evening">🌙 Ca tối (14:00 - 22:00)</option>
                    <option value="custom">⚙️ Tùy chỉnh</option>
                </select>
            </div>

            <div class="custom-hours-header" id="customHoursHeader">
                <p>✏️ Tùy chỉnh giờ làm việc</p>
            </div>
            
            <div class="hour-inputs" id="hourInputs">
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

            <input type="hidden" id="shiftId" name="shift_id" value="">

            <button type="submit" class="btn-submit" id="submitBtn">✅ Đăng ký ca</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">❌ Hủy</button>
        </form>
    </div>
</div>

<!-- Modal Chi tiết -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeDetailModal()">✕</button>
        <div class="modal-header" style="color: #111827; font-size: 1.25rem;">📋 Thông tin ca làm việc</div>
        <div id="detailContent"></div>
        <button type="button" class="btn-edit" onclick="openEditModal()">✏️ Cập nhật ca làm việc</button>
        <button type="button" class="btn-delete" id="deleteScheduleBtn" onclick="deleteScheduleRequest()" style="display: none;">🗑️ Hủy ca</button>
        <button type="button" class="btn-cancel" onclick="closeDetailModal()">Đóng</button>
    </div>
</div>

<!-- Modal Chỉnh sửa Off-Day -->
<div id="editOffdayModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeEditOffdayModal()">✕</button>
        <div class="modal-header">✏️ Chỉnh sửa đơn xin nghỉ</div>

        <form id="editOffdayForm" method="POST">
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
    let selectedDate = null;
    let editingSchedule = null;
    let currentWeekStart = new Date("{{ $weekStart->format('Y-m-d') }}" + 'T00:00:00');

    let shiftsData = {!! json_encode($shifts->map(function($s) {
        return [
            'id' => $s->id,
            'name' => strtolower($s->name),
            'start_hour' => $s->start_hour,
            'end_hour' => $s->end_hour
        ];
    })) !!};

    let allSchedulesData = {
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

    function formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function rebuildCalendarGrid(weekStart, weekEnd, pendingReqs, approvedReqs) {
        let html = '<div style="background: white;"></div>';
        
        const morningShiftId = shiftsData.find(s => s.start_hour < 12)?.id;
        const eveningShiftId = shiftsData.find(s => s.start_hour >= 14)?.id;
        
        console.log('🔧 DEBUG rebuildCalendarGrid:', {
            shiftsData: shiftsData,
            morningShiftId: morningShiftId,
            eveningShiftId: eveningShiftId,
            pendingReqs: pendingReqs,
            approvedReqs: approvedReqs
        });
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            const isToday = dateStr === formatLocalDate(new Date());
            const isSunday = i === 6;
            
            const dayNum = String(date.getDate()).padStart(2, '0');
            const dayName = i === 6 ? 'TCN' : 'T' + (i + 2);
            
            html += `<div class="grid-day-header ${isToday ? 'today' : ''} ${isSunday ? 'sunday' : ''}">
                <div class="day-name">${dayName}</div>
                <div class="day-number">${dayNum}</div>
            </div>`;
        }
        
        function getRequestForDateShift(dateStr, shiftId) {
            console.log('🔍 Looking for:', { dateStr, shiftId, pendingCount: pendingReqs.length, approvedCount: approvedReqs.length });
            const approved = approvedReqs.find(r => r.date === dateStr && r.shift_id === shiftId);
            if (approved) {
                console.log('✅ Found approved:', approved);
                return { ...approved, status: 'approved' };
            }
            const pending = pendingReqs.find(r => r.date === dateStr && r.shift_id === shiftId);
            if (pending) {
                console.log('✅ Found pending:', pending);
                return { ...pending, status: 'pending' };
            }
            console.log('❌ Not found');
            return null;
        }
        
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">☀️</div>
            <div class="grid-time-label">SÁNG</div>
            <div class="grid-time-hours">08:00 - 17:00</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            if (!morningShiftId) {
                html += '<div class="grid-cell"><div style="color: #d1d5db; font-weight: 600;">—</div></div>';
                continue;
            }
            
            const request = getRequestForDateShift(dateStr, morningShiftId);
            
            if (request) {
                const statusClass = request.status === 'approved' ? 'registered' : 'pending';
                const statusText = request.status === 'approved' ? '✅ Đã đăng ký' : '⏳ Chờ duyệt';
                html += `<div class="grid-cell"><div class="shift-card ${statusClass}" onclick="showDetailModalById(${request.id})">${statusText}<br><small>${request.name}</small></div></div>`;
            } else {
                const morningShift = shiftsData.find(s => s.id === morningShiftId);
                const shiftName = morningShift?.name || 'Ca sáng';
                html += `<div class="grid-cell"><div class="shift-card empty" onclick="quickRegister('${dateStr}', ${morningShiftId}, '${shiftName}')"><div class="shift-icon">➕</div>Đăng ký</div></div>`;
            }
        }
        
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">🌙</div>
            <div class="grid-time-label">TỐI</div>
            <div class="grid-time-hours">14:00 - 22:00</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            if (!eveningShiftId) {
                html += '<div class="grid-cell"><div style="color: #d1d5db; font-weight: 600;">—</div></div>';
                continue;
            }
            
            const request = getRequestForDateShift(dateStr, eveningShiftId);
            
            if (request) {
                const statusClass = request.status === 'approved' ? 'registered' : 'pending';
                const statusText = request.status === 'approved' ? '✅ Đã đăng ký' : '⏳ Chờ duyệt';
                html += `<div class="grid-cell"><div class="shift-card ${statusClass}" onclick="showDetailModalById(${request.id})">${statusText}<br><small>${request.name}</small></div></div>`;
            } else {
                const eveningShift = shiftsData.find(s => s.id === eveningShiftId);
                const shiftName = eveningShift?.name || 'Ca tối';
                html += `<div class="grid-cell"><div class="shift-card empty" onclick="quickRegister('${dateStr}', ${eveningShiftId}, '${shiftName}')"><div class="shift-icon">➕</div>Đăng ký</div></div>`;
            }
        }
        
        document.getElementById('calendarGrid').innerHTML = html;
    }

    function loadWeek(date) {
        currentWeekStart = date;
        const dateStr = formatLocalDate(date);
        
        document.getElementById('loadingSpinner').style.display = 'block';
        document.getElementById('calendarGrid').style.opacity = '0.5';
        
        const url = new URL(window.location);
        url.searchParams.set('week_start', dateStr);
        window.history.pushState(null, '', url);
        
        fetch('{{ route("doctor.schedule.get-week-data") }}?week_start=' + dateStr, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            console.log('📥 API Response:', data);
            
            if (data.shifts && data.shifts.length > 0) {
                shiftsData = data.shifts;
                console.log('✅ shiftsData updated:', shiftsData);
            } else {
                console.error('❌ WARNING: API không trả về shifts!');
            }
            
            allSchedulesData.pending = data.pending_requests || [];
            allSchedulesData.approved = data.approved_requests || [];
            
            document.getElementById('pendingCount').textContent = data.pending_count;
            document.getElementById('approvedCount').textContent = data.approved_count;
            document.getElementById('offDayCount').textContent = data.approved_off_days;
            
            const weekStart = new Date(data.week_start + 'T00:00:00');
            const weekEnd = new Date(data.week_end + 'T00:00:00');
            
            rebuildCalendarGrid(weekStart, weekEnd, allSchedulesData.pending, allSchedulesData.approved);
            
            const displayEl = document.getElementById('weekDisplay');
            displayEl.setAttribute('data-week-start', data.week_start);
            displayEl.textContent = 
                String(weekStart.getDate()).padStart(2, '0') + '/' +
                String(weekStart.getMonth() + 1).padStart(2, '0') + ' - ' +
                String(weekEnd.getDate()).padStart(2, '0') + '/' +
                String(weekEnd.getMonth() + 1).padStart(2, '0') + '/' +
                weekEnd.getFullYear();
            
            document.getElementById('weekPicker').value = data.week_start;
        })
        .catch(e => {
            console.error('❌ Fetch error:', e);
            alert('Lỗi tải dữ liệu!');
        })
        .finally(() => {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('calendarGrid').style.opacity = '1';
        });
    }

    function previousWeek() {
        const newDate = new Date(currentWeekStart);
        newDate.setDate(newDate.getDate() - 7);
        loadWeek(newDate);
    }

    function nextWeek() {
        const newDate = new Date(currentWeekStart);
        newDate.setDate(newDate.getDate() + 7);
        loadWeek(newDate);
    }

    function jumpToWeek(dateStr) {
        if (!dateStr) return;
        loadWeek(new Date(dateStr + 'T00:00:00'));
    }

    function getShiftIdByType(type) {
        if (type === 'morning') {
            const shift = shiftsData.find(s => s.name.includes('sáng') || s.start_hour < 12);
            return shift ? shift.id : null;
        } else if (type === 'evening') {
            const shift = shiftsData.find(s => s.name.includes('tối') || s.start_hour >= 14);
            return shift ? shift.id : null;
        }
        return null;
    }

    function getShiftTypeById(shiftId) {
        const shift = shiftsData.find(s => s.id == shiftId);
        if (!shift) return 'custom';
        if (shift.name.includes('sáng') || shift.start_hour < 12) return 'morning';
        if (shift.name.includes('tối') || shift.start_hour >= 14) return 'evening';
        return 'custom';
    }

    function onShiftTypeChange() {
        const shiftType = document.getElementById('shiftTypeSelect').value;
        const customHoursHeader = document.getElementById('customHoursHeader');
        const hourInputs = document.getElementById('hourInputs');
        
        if (shiftType === 'morning') {
            const morningShift = shiftsData.find(s => s.name.includes('sáng') || s.start_hour < 12);
            if (morningShift) {
                document.getElementById('shiftId').value = morningShift.id;
                console.log('✅ shift_id updated to morning:', morningShift.id);
            }
            
            document.getElementById('startHour').value = '8';
            document.getElementById('startMinute').value = '0';
            document.getElementById('endHour').value = '17';
            document.getElementById('endMinute').value = '0';
            customHoursHeader.classList.add('active');
            hourInputs.classList.add('active');
            document.querySelector('.custom-hours-header p').textContent = '✏️ Ca sáng (08:00 - 17:00)';
        } else if (shiftType === 'evening') {
            const eveningShift = shiftsData.find(s => s.name.includes('tối') || s.start_hour >= 14);
            if (eveningShift) {
                document.getElementById('shiftId').value = eveningShift.id;
                console.log('✅ shift_id updated to evening:', eveningShift.id);
            }
            
            document.getElementById('startHour').value = '14';
            document.getElementById('startMinute').value = '0';
            document.getElementById('endHour').value = '22';
            document.getElementById('endMinute').value = '0';
            customHoursHeader.classList.add('active');
            hourInputs.classList.add('active');
            document.querySelector('.custom-hours-header p').textContent = '✏️ Ca tối (14:00 - 22:00)';
        } else if (shiftType === 'custom') {
            document.getElementById('shiftId').value = '';
            console.log('✅ shift_id reset for custom');
            
            document.getElementById('startHour').value = '';
            document.getElementById('startMinute').value = '';
            document.getElementById('endHour').value = '';
            document.getElementById('endMinute').value = '';
            customHoursHeader.classList.add('active');
            hourInputs.classList.add('active');
            document.querySelector('.custom-hours-header p').textContent = '✏️ Tùy chỉnh giờ làm việc';
        }
    }

    function showDetailModalById(scheduleId) {
        const allSchedules = [...allSchedulesData.approved, ...allSchedulesData.pending];
        const schedule = allSchedules.find(s => s.id == scheduleId);

        if (!schedule) return;

        editingSchedule = schedule;

        const statusText = schedule.status === 'approved' ? '✅ Đã duyệt' : '⏳ Chờ duyệt';
        const statusColor = schedule.status === 'approved' ? '#10b981' : '#f59e0b';
        const date = new Date(schedule.date + 'T00:00:00').toLocaleDateString('vi-VN');

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
        
        const deleteBtn = document.getElementById('deleteScheduleBtn');
        if (schedule.status === 'pending') {
            deleteBtn.style.display = 'block';
        } else {
            deleteBtn.style.display = 'none';
        }
        
        document.getElementById('detailModal').classList.add('active');
    }

    function deleteScheduleRequest() {
        if (!editingSchedule) {
            alert('Không tìm thấy thông tin ca làm việc');
            return;
        }

        if (editingSchedule.status !== 'pending') {
            alert('❌ Chỉ có thể hủy các đơn đang chờ duyệt');
            return;
        }

        if (confirm('Bạn chắc chắn muốn hủy đăng ký ca này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ url('doctor/schedule') }}/" + editingSchedule.id;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
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
            const opt1 = document.createElement('option');
            opt1.value = i;
            opt1.textContent = `${String(i).padStart(2, '0')}h`;
            startHour.appendChild(opt1);

            const opt2 = document.createElement('option');
            opt2.value = i;
            opt2.textContent = `${String(i).padStart(2, '0')}h`;
            endHour.appendChild(opt2);
        }
        
        for (let i = 0; i < 60; i++) {
            const opt1 = document.createElement('option');
            opt1.value = i;
            opt1.textContent = `${String(i).padStart(2, '0')} phút`;
            startMinute.appendChild(opt1);

            const opt2 = document.createElement('option');
            opt2.value = i;
            opt2.textContent = `${String(i).padStart(2, '0')} phút`;
            endMinute.appendChild(opt2);
        }
    }

    function quickRegister(dateStr, shiftId, shiftName) {
        selectedDate = dateStr;
        document.getElementById('workDate').value = dateStr;
        document.getElementById('shiftId').value = shiftId;
        document.getElementById('methodInput').value = 'POST';
        document.getElementById('shiftForm').action = "{{ route('doctor.schedule.store') }}";
        document.getElementById('modalTitle').textContent = '✏️ Đăng ký ca: ' + shiftName;
        document.getElementById('submitBtn').textContent = '✅ Đăng ký ca';
        
        const shiftType = getShiftTypeById(shiftId);
        document.getElementById('shiftTypeSelect').value = shiftType;
        document.getElementById('shiftTypeFormGroup').style.display = 'none';
        
        onShiftTypeChange();
        document.getElementById('shiftModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('shiftTypeFormGroup').style.display = 'block';
        document.getElementById('shiftTypeSelect').value = '';
        document.getElementById('customHoursHeader').classList.remove('active');
        document.getElementById('hourInputs').classList.remove('active');
        document.getElementById('shiftModal').classList.remove('active');
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
            selectedDate = editingSchedule.date;
            document.getElementById('workDate').value = editingSchedule.date;
            
            const shiftId = editingSchedule.shift_id || '';
            document.getElementById('shiftId').value = shiftId;
            console.log('✏️ Edit mode - shift_id:', shiftId);
            
            document.getElementById('startHour').value = editingSchedule.start_hour || '';
            document.getElementById('startMinute').value = editingSchedule.start_minute || '';
            document.getElementById('endHour').value = editingSchedule.end_hour || '';
            document.getElementById('endMinute').value = editingSchedule.end_minute || '';
            document.getElementById('methodInput').value = 'PUT';
            
            if (shiftId) {
                const shiftType = getShiftTypeById(shiftId);
                document.getElementById('shiftTypeSelect').value = shiftType;
            } else {
                document.getElementById('shiftTypeSelect').value = 'custom';
            }
            
            document.getElementById('shiftTypeFormGroup').style.display = 'block';
            document.getElementById('customHoursHeader').classList.add('active');
            document.getElementById('hourInputs').classList.add('active');
            onShiftTypeChange();
            
            document.getElementById('shiftForm').action = "{{ url('doctor/schedule') }}/" + editingSchedule.id;
            document.getElementById('modalTitle').textContent = '✏️ Cập nhật ca làm việc';
            document.getElementById('submitBtn').textContent = '✅ Cập nhật ca';
            
            document.getElementById('shiftModal').classList.add('active');
        }, 100);
    }

    function editOffDay(id, startDate, reason) {
        document.getElementById('editStartDate').value = startDate;
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
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    const reasonField = document.getElementById('reason');
    if (reasonField) {
        reasonField.addEventListener('input', function() {
            const counter = this.parentElement.querySelector('.char-counter');
            if (counter) counter.textContent = this.value.length + ' / 200 ký tự';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const weekStartStr = document.getElementById('weekDisplay').getAttribute('data-week-start');
        document.getElementById('weekPicker').value = weekStartStr;
        document.getElementById('weekPicker').addEventListener('change', function() {
            jumpToWeek(this.value);
        });
        populateHours();
        rebuildCalendarGrid(currentWeekStart, new Date(currentWeekStart.getTime() + 6*24*60*60*1000), allSchedulesData.pending, allSchedulesData.approved);
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            this.classList.add('active');
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('shiftModal')) closeModal();
        if (e.target === document.getElementById('detailModal')) closeDetailModal();
        if (e.target === document.getElementById('editOffdayModal')) closeEditOffdayModal();
    });

    // ✅ FIX: LUÔN update shift_id trước khi submit (không chỉ khi trống)
    document.getElementById('shiftForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const startHour = document.getElementById('startHour').value;
        const endHour = document.getElementById('endHour').value;
        
        if (!startHour || !endHour) {
            alert('Vui lòng chọn giờ bắt đầu và giờ kết thúc');
            return;
        }
        
        // ✅ FIX: LUÔN update shift_id trước khi submit
        const shiftType = document.getElementById('shiftTypeSelect').value;
        if (shiftType === 'morning' || shiftType === 'evening') {
            const shiftId = getShiftIdByType(shiftType);
            document.getElementById('shiftId').value = shiftId;
            console.log('✅ shift_id UPDATED to:', shiftId);
        } else if (shiftType === 'custom') {
            console.log('✅ Custom - keeping shift_id:', document.getElementById('shiftId').value);
        } else {
            alert('Vui lòng chọn loại ca');
            return;
        }
        
        console.log('📤 Submitting form WITH UPDATED SHIFT_ID:', {
            action: this.action,
            shift_id: document.getElementById('shiftId').value,
            method: document.getElementById('methodInput').value
        });
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Đang xử lý...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => { throw new Error(d.message || 'Lỗi'); });
            return r.json();
        })
        .then(data => {
            closeModal();
            console.log('✅ Shift form submitted, reloading week data...');
            loadWeek(currentWeekStart);
        })
        .catch(err => alert('❌ ' + err.message))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    document.getElementById('offdayForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Đang xử lý...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => { throw new Error(d.message || 'Lỗi'); });
            return r.json();
        })
        .then(() => {
            this.reset();
            document.querySelector('.char-counter').textContent = '0 / 200 ký tự';
            location.reload();
        })
        .catch(err => alert('❌ ' + err.message))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    document.getElementById('editOffdayForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Đang xử lý...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
        })
        .then(r => {
            if (!r.ok) return r.json().then(d => { throw new Error(d.message || 'Lỗi'); });
            return r.json();
        })
        .then(() => {
            closeEditOffdayModal();
            location.reload();
        })
        .catch(err => alert('❌ ' + err.message))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
</script>

@endsection