@extends('layouts.employee-layout')

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
    .stat-card.approved { border-left-color: #0284c7; }
    .stat-card.offday { border-left-color: #ec4899; }

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
    .stat-card.approved p:last-child { color: #0284c7; }
    .stat-card.offday p:last-child { color: #ec4899; }

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

    .tab-btn:hover { color: #0284c7; }
    .tab-btn.active { color: #0284c7; border-bottom-color: #0284c7; }

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
        background: linear-gradient(135deg, #0284c7 0%, #0c4a6e 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.2);
    }

    .week-nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
    }

    .week-display {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111827;
        min-width: 200px;
        text-align: center;
        padding: 0.5rem 1rem;
        background: #f0f9ff;
        border-radius: 8px;
        border-left: 4px solid #0284c7;
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

    .legend-dot.registered { background: #0284c7; }
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

    .grid-day-header.today { background: #0284c7; color: white; }
    .grid-day-header.sunday { background: #fee2e2; }
    .grid-day-header.sunday .day-name, .grid-day-header.sunday .day-number { color: #dc2626; }

    .day-name {
        font-size: 0.7rem;
        color: #0284c7;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .grid-day-header.today .day-name { color: white; }

    .day-number {
        font-size: 1.4rem;
        color: #0284c7;
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
        background: #dbeafe;
        border-color: #0284c7;
        color: #0c4a6e;
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
        border-color: #0284c7;
        color: #0284c7;
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
        border-top: 4px solid #0284c7;
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
        border-color: #0284c7;
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.1);
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
        border-left: 4px solid #0284c7;
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
        font-size: 0.9rem;
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
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
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
        background: linear-gradient(135deg, #0284c7 0%, #0c4a6e 100%);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-edit {
        color: white;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        width: 100%;
        padding: 0.875rem 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
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
        border-top: 4px solid #0284c7;
        margin-bottom: 2rem;
    }

    .offday-request-card {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        border-left: 5px solid;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 1rem;
    }

    .offday-request-card.pending {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }

    .offday-request-card.approved {
        border-left-color: #0284c7;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }

    .offday-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-badge.pending-badge { background: #fef3c7; color: #92400e; }
    .status-badge.approved-badge { background: #dbeafe; color: #0284c7; }

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

    .btn-action.edit { color: #f59e0b; border-color: #f59e0b; }
    .btn-action.edit:hover { background: #f59e0b; color: white; transform: translateY(-2px); }

    .btn-action.delete { color: #ef4444; border-color: #ef4444; }
    .btn-action.delete:hover { background: #ef4444; color: white; transform: translateY(-2px); }

    textarea#reason, textarea#editReason { font-family: inherit; resize: vertical; min-height: 100px; }
    .char-counter { font-size: 0.8rem; color: #9ca3af; margin-top: 0.5rem; }

    @media (max-width: 768px) {
        .calendar-grid { grid-template-columns: 80px repeat(7, 1fr); }
        .modal-content { max-width: 90vw; }
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
        <p id="pendingCount">0</p>
    </div>
    <div class="stat-card approved">
        <p>✅ Ca được duyệt</p>
        <p id="approvedCount">0</p>
    </div>
    <div class="stat-card offday">
        <p>🏖️ Ngày nghỉ được duyệt</p>
        <p id="offdayCount">0</p>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-nav">
    <button class="tab-btn active" data-tab="tab-shift">📅 Lịch đăng ký ca</button>
    <button class="tab-btn" data-tab="tab-offday">🏖️ Xin ngày nghỉ</button>
</div>

<!-- Tab 1: Weekly Schedule -->
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
                        <span>✅ Đã đăng ký</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot pending"></div>
                        <span>⏳ Chờ duyệt</span>
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
            <!-- Generated by JavaScript -->
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

        <form action="{{ route('employees.schedule.off-day.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>📅 Từ ngày <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label>📅 Đến ngày <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="end_date" required>
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
            <h3 style="margin: 0 0 1.5rem 0; color: #f59e0b;">⏳ Đơn xin nghỉ chờ duyệt ({{ $pendingOffDays->count() }})</h3>
            @foreach($pendingOffDays as $offday)
                <div class="offday-request-card pending">
                    <div class="offday-card-header">
                        <div>{{ optional($offday->date)->format('d/m/Y') }} - {{ $offday->reason ?? 'Không có lý do' }}</div>
                        <span class="status-badge pending-badge">⏳ Chờ duyệt</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem;">
                        <button type="button" class="btn-action edit" onclick="editOffDay({{ $offday->id }}, '{{ optional($offday->date)->format('Y-m-d') }}', '{{ addslashes($offday->reason ?? '') }}')">✏️ Chỉnh sửa</button>
                        <button type="button" class="btn-action delete" onclick="deleteOffDay({{ $offday->id }})">🗑️ Hủy</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($approvedOffDays->count() > 0)
        <div class="offday-requests-container">
            <h3 style="margin: 0 0 1.5rem 0; color: #0284c7;">✅ Đơn xin nghỉ đã duyệt ({{ $approvedOffDays->count() }})</h3>
            @foreach($approvedOffDays as $offday)
                <div class="offday-request-card approved">
                    <div class="offday-card-header">
                        <div>{{ optional($offday->date)->format('d/m/Y') }} - {{ $offday->reason ?? 'Không có lý do' }}</div>
                        <span class="status-badge approved-badge">✅ Đã duyệt</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Shift -->
<div id="shiftModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-header" id="modalTitle">📋 Đăng ký ca làm việc</div>

        <form id="shiftForm" method="POST">
            @csrf
            <input type="hidden" id="methodInput" name="_method" value="POST">

            <div class="form-group">
                <label>📅 Ngày làm việc</label>
                <input type="date" id="workDate" name="work_date" required readonly style="background: #f3f4f6;">
            </div>

            <div class="form-group">
                <label>⏰ Chọn ca làm việc <span style="color: red;">*</span></label>
                <select id="shiftId" name="shift_id" required>
                    <option value="">-- Chọn ca --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" data-start-hour="{{ $shift->start_hour }}" data-end-hour="{{ $shift->end_hour }}">
                            {{ $shift->name }} ({{ $shift->time_range }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">✅ Đăng ký ca</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">❌ Hủy</button>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeDetailModal()">✕</button>
        <div class="modal-header">📋 Thông tin ca làm việc</div>
        <div id="detailContent"></div>
        <button type="button" class="btn-cancel" onclick="closeDetailModal()">Đóng</button>
    </div>
</div>

<!-- Modal Edit Off-Day -->
<div id="editOffdayModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeEditOffdayModal()">✕</button>
        <div class="modal-header">✏️ Chỉnh sửa đơn xin nghỉ</div>

        <form method="POST" id="editOffdayForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>📅 Ngày <span style="color: #ef4444;">*</span></label>
                <input type="date" name="date" id="editDate" required>
            </div>

            <div class="form-group">
                <label>📝 Lý do <span style="color: #ef4444;">*</span></label>
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
    let currentWeekStart = new Date("{{ $weekStart->format('Y-m-d') }}" + 'T00:00:00');
    let shiftsData = [];
    let schedulesData = { pending: [], approved: [] };

    function formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function loadWeek(date) {
        currentWeekStart = date;
        const dateStr = formatLocalDate(date);
        
        document.getElementById('loadingSpinner').style.display = 'block';
        document.getElementById('calendarGrid').style.opacity = '0.5';
        
        const url = new URL(window.location);
        url.searchParams.set('week_start', dateStr);
        window.history.pushState(null, '', url);
        
        fetch('{{ route("employees.schedule.get-week-data") }}?week_start=' + dateStr, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            shiftsData = data.shifts || [];
            schedulesData.pending = data.pending_requests || [];
            schedulesData.approved = data.approved_requests || [];
            
            document.getElementById('pendingCount').textContent = data.pending_count || 0;
            document.getElementById('approvedCount').textContent = data.approved_count || 0;
            document.getElementById('offdayCount').textContent = data.approved_off_days || 0;
            
            const weekStart = new Date(data.week_start + 'T00:00:00');
            const weekEnd = new Date(data.week_end + 'T00:00:00');
            
            rebuildCalendarGrid(weekStart, weekEnd);
            
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
            console.error('Fetch error:', e);
            alert('Lỗi tải dữ liệu!');
        })
        .finally(() => {
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('calendarGrid').style.opacity = '1';
        });
    }

    function rebuildCalendarGrid(weekStart, weekEnd) {
        let html = '<div style="background: white;"></div>';
        
        const morningShift = shiftsData.find(s => s.start_hour < 12);
        const eveningShift = shiftsData.find(s => s.start_hour >= 14);
        
        // Day headers
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
        
        // Morning shifts
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">☀️</div>
            <div class="grid-time-label">SÁNG</div>
            <div class="grid-time-hours">08:00 - 17:00</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            const allSchedules = [...schedulesData.approved, ...schedulesData.pending];
            const schedule = allSchedules.find(s => s.date === dateStr && s.shift_id === morningShift?.id);
            
            if (schedule) {
                const statusClass = schedule.status === 'approved' ? 'registered' : 'pending';
                const statusText = schedule.status === 'approved' ? '✅ Đã đăng ký' : '⏳ Chờ duyệt';
                html += `<div class="grid-cell"><div class="shift-card ${statusClass}" onclick="showDetailModal('${schedule.id}', '${schedule.name}', '${schedule.time}', '${dateStr}', '${schedule.status}')"><small>${statusText}</small></div></div>`;
            } else {
                html += `<div class="grid-cell"><div class="shift-card empty" onclick="quickRegister('${dateStr}', ${morningShift?.id || 0})"><div class="shift-icon">➕</div>Đăng ký</div></div>`;
            }
        }
        
        // Evening shifts
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">🌙</div>
            <div class="grid-time-label">TỐI</div>
            <div class="grid-time-hours">14:00 - 22:00</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            const allSchedules = [...schedulesData.approved, ...schedulesData.pending];
            const schedule = allSchedules.find(s => s.date === dateStr && s.shift_id === eveningShift?.id);
            
            if (schedule) {
                const statusClass = schedule.status === 'approved' ? 'registered' : 'pending';
                const statusText = schedule.status === 'approved' ? '✅ Đã đăng ký' : '⏳ Chờ duyệt';
                html += `<div class="grid-cell"><div class="shift-card ${statusClass}" onclick="showDetailModal('${schedule.id}', '${schedule.name}', '${schedule.time}', '${dateStr}', '${schedule.status}')"><small>${statusText}</small></div></div>`;
            } else {
                html += `<div class="grid-cell"><div class="shift-card empty" onclick="quickRegister('${dateStr}', ${eveningShift?.id || 0})"><div class="shift-icon">➕</div>Đăng ký</div></div>`;
            }
        }
        
        document.getElementById('calendarGrid').innerHTML = html;
    }

    function quickRegister(dateStr, shiftId) {
        document.getElementById('workDate').value = dateStr;
        document.getElementById('shiftId').value = shiftId;
        document.getElementById('methodInput').value = 'POST';
        document.getElementById('shiftForm').action = "{{ route('employees.schedule.store') }}";
        document.getElementById('modalTitle').textContent = '📋 Đăng ký ca làm việc';
        document.getElementById('submitBtn').textContent = '✅ Đăng ký ca';
        document.getElementById('shiftModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('shiftModal').classList.remove('active');
    }

    function showDetailModal(scheduleId, name, time, dateStr, status) {
        const date = new Date(dateStr + 'T00:00:00').toLocaleDateString('vi-VN');
        const statusText = status === 'approved' ? '✅ Đã duyệt' : '⏳ Chờ duyệt';
        
        const html = `
            <div class="detail-item">
                <label>📅 Ngày làm việc</label>
                <p>${date}</p>
            </div>
            <div class="detail-item">
                <label>⏰ Ca làm việc</label>
                <p>${name}</p>
            </div>
            <div class="detail-item">
                <label>🕐 Thời gian</label>
                <p>${time}</p>
            </div>
            <div class="detail-item">
                <label>✅ Trạng thái</label>
                <p>${statusText}</p>
            </div>
        `;
        
        document.getElementById('detailContent').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.remove('active');
    }

    function editOffDay(id, date, reason) {
        document.getElementById('editDate').value = date;
        document.getElementById('editReason').value = reason;
        document.getElementById('editOffdayForm').action = `{{ route('employees.schedule.off-day.update', ':id') }}`.replace(':id', id);
        document.getElementById('editOffdayModal').classList.add('active');
    }

    function closeEditOffdayModal() {
        document.getElementById('editOffdayModal').classList.remove('active');
    }

    function deleteOffDay(id) {
        if (!confirm('Bạn có chắc chắn muốn hủy đơn xin nghỉ này không?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('employees.schedule.off-day.cancel', ':id') }}`.replace(':id', id);
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
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

    document.getElementById('weekPicker').addEventListener('change', function() {
        if (this.value) loadWeek(new Date(this.value + 'T00:00:00'));
    });

    document.getElementById('reason')?.addEventListener('input', function() {
        const counter = this.parentElement.querySelector('.char-counter');
        if (counter) counter.textContent = this.value.length + ' / 200 ký tự';
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

    document.addEventListener('DOMContentLoaded', function() {
        loadWeek(currentWeekStart);
    });
</script>

@endsection