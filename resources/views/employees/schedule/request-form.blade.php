@extends('layouts.employee-layout')

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
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .calendar-header h3 {
        font-size: 1.5rem;
        margin: 0;
        color: #111827;
    }

    .calendar-nav {
        display: flex;
        gap: 1rem;
    }

    .calendar-nav button {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .calendar-nav button:hover {
        background: #1e40af;
    }

    /* Calendar Grid */
    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .weekday {
        padding: 1rem;
        text-align: center;
        font-weight: 700;
        color: #6b7280;
        background: #f3f4f6;
        border-radius: 8px;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
    }

    .calendar-day {
        aspect-ratio: 1;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
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

    .calendar-day-number {
        font-size: 1.125rem;
        font-weight: 600;
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

    .form-group input[type="date"],
    .form-group textarea {
        cursor: text;
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

    /* Off-Day Form */
    .offday-form {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .pending-list-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
    }

    .pending-list-header {
        padding: 1.25rem 1.5rem;
        font-size: 1.125rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .pending-list-body {
        padding: 1.5rem;
    }

    .pending-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #fef3c7 0%, #fef08a 100%);
        border: 2px solid #fde047;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pending-item:last-child {
        margin-bottom: 0;
    }

    .pending-item:hover {
        border-color: #f59e0b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
    }

    .pending-item-left p:first-child {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .pending-item-left p:last-child {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #fef3c7;
        color: #92400e;
    }

    .btn-cancel-request {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-cancel-request:hover {
        background: #dc2626;
    }

    @media (max-width: 768px) {
        .calendar-day {
            padding: 0.5rem;
            font-size: 0.85rem;
        }

        .calendar-day-number {
            font-size: 0.95rem;
        }

        .modal-content {
            max-width: 90vw;
        }

        .hour-inputs {
            grid-template-columns: 1fr 1fr;
        }

        .pending-item {
            flex-direction: column;
            align-items: flex-start;
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
        <p>{{ $approvedSchedules->count() }}</p>
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
    <button class="tab-btn" data-tab="tab-approved">✅ Ca đã duyệt</button>
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

        <!-- Weekdays -->
        <div class="calendar-weekdays">
            <div class="weekday">T2</div>
            <div class="weekday">T3</div>
            <div class="weekday">T4</div>
            <div class="weekday">T5</div>
            <div class="weekday">T6</div>
            <div class="weekday">T7</div>
            <div class="weekday">CN</div>
        </div>

        <!-- Days -->
        <div class="calendar-days" id="calendarDays"></div>
    </div>

    @if($pendingRequests->count() > 0)
        <div class="pending-list-container">
            <div class="pending-list-header">
                ⏳ Đơn chờ duyệt ({{ $pendingRequests->count() }})
            </div>
            
            <div class="pending-list-body">
                @foreach($pendingRequests as $request)
                    <div class="pending-item">
                        <div class="pending-item-left">
                            <p>{{ optional($request->shift)->name ?? 'Ca làm việc' }}</p>
                            <p>{{ $request->work_date->format('d/m/Y') }} • {{ optional($request->shift)->time_range ?? 'Tùy chỉnh' }}</p>
                        </div>
                        <div>
                            <span class="badge">Chờ duyệt</span>
                        </div>
                        <form action="{{ route('employees.schedule.cancel', $request->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-cancel-request">❌ Hủy</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Tab 2: Off-Day -->
<div id="tab-offday" class="tab-content">
    <div class="offday-form">
        <h3 style="margin-top: 0; color: #111827;">🏖️ Xin ngày nghỉ</h3>
        <form action="{{ route('employees.schedule.off-day.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>📅 Từ ngày</label>
                <input type="date" name="start_date" required min="{{ now()->toDateString() }}">
            </div>

            <div class="form-group">
                <label>📅 Đến ngày</label>
                <input type="date" name="end_date" required min="{{ now()->toDateString() }}">
            </div>

            <div class="form-group">
                <label>📝 Lý do</label>
                <textarea name="reason" rows="4" placeholder="Nhập lý do xin nghỉ..." required></textarea>
            </div>

            <button type="submit" class="btn-submit">✅ Gửi đơn xin nghỉ</button>
        </form>
    </div>

    @if($approvedOffDays->count() > 0)
        <div class="pending-list-container">
            <div class="pending-list-header">
                ✅ Ngày nghỉ đã duyệt ({{ $approvedOffDays->count() }})
            </div>
            
            <div class="pending-list-body">
                @foreach($approvedOffDays as $offday)
                    <div class="pending-item">
                        <div class="pending-item-left">
                            <p>{{ $offday->reason ?? 'Không có lý do' }}</p>
                            <p>{{ optional($offday->off_date)->format('d/m/Y') ?? $offday->off_date }} • Đã duyệt</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Tab 3: Approved Schedules -->
<div id="tab-approved" class="tab-content">
    <div class="offday-form">
        @if($approvedSchedules->count() > 0)
            <h3 style="margin-top: 0; color: #111827;">📅 Danh sách ca đã duyệt</h3>
            <div style="display: grid; gap: 1rem;">
                @foreach($approvedSchedules as $schedule)
                    <div style="padding: 1.25rem; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); border: 2px solid #0ea5e9; border-radius: 8px;">
                        <h4 style="color: #0c4a6e; margin: 0 0 0.5rem 0;">{{ optional($schedule->shift)->name ?? 'Ca làm việc' }}</h4>
                        <p style="font-size: 0.875rem; color: #0c4a6e; margin: 0.25rem 0;">📅 Ngày: {{ $schedule->work_date->format('d/m/Y') }}</p>
                        <p style="font-size: 0.875rem; color: #0c4a6e; margin: 0.25rem 0;">⏰ Giờ: {{ optional($schedule->shift)->time_range ?? 'Tùy chỉnh' }}</p>
                        <p style="font-size: 0.875rem; color: #0c4a6e; margin: 0.25rem 0;">✅ Trạng thái: Đã duyệt</p>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: #9ca3af;">
                <p>📭 Chưa có ca làm việc nào được duyệt</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal -->
<div id="shiftModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-header">📋 Đăng ký ca làm việc</div>

        <form action="{{ route('employees.schedule.store') }}" method="POST" id="shiftForm">
            @csrf

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
                                data-start-hour="{{ $shift->start_hour }}" 
                                data-start-minute="{{ $shift->start_minute }}" 
                                data-end-hour="{{ $shift->end_hour }}" 
                                data-end-minute="{{ $shift->end_minute }}">
                            {{ $shift->name }} ({{ $shift->time_range }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Custom Hours Section -->
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

            <button type="submit" class="btn-submit">✅ Đăng ký ca</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">❌ Hủy</button>
        </form>
    </div>
</div>


<script>
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let selectedDate = null;

    // Hàm format ngày YYYY-MM-DD
    function formatDate(year, month, day) {
        const m = String(month + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        return `${year}-${m}-${d}`;
    }

    // Hàm so sánh ngày
    function isTodayOrFuture(dateStr) {
        const today = new Date();
        const todayStr = formatDate(today.getFullYear(), today.getMonth(), today.getDate());
        return dateStr >= todayStr;
    }

    // Tạo option giờ 0-23 và phút 0-59
    function populateHours() {
        const startHour = document.getElementById('startHour');
        const startMinute = document.getElementById('startMinute');
        const endHour = document.getElementById('endHour');
        const endMinute = document.getElementById('endMinute');
        
        // Giờ 0-23
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
        
        // Phút 0-59
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

            day.innerHTML = `<div class="calendar-day-number">${i}</div>`;
            
            if (isTodayOrFuture(dateStr)) {
                day.style.cursor = 'pointer';
                day.onclick = () => openModal(dateStr);
            } else {
                day.style.cursor = 'not-allowed';
                day.style.opacity = '0.5';
            }
            
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

    function openModal(dateStr) {
        if (!isTodayOrFuture(dateStr)) {
            alert('❌ Không thể đăng ký ngày trong quá khứ!');
            return;
        }

        selectedDate = dateStr;
        document.getElementById('workDate').value = dateStr;
        document.getElementById('shiftId').value = '';
        document.getElementById('customHours').style.display = 'none';
        document.getElementById('shiftModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('shiftModal').classList.remove('active');
    }

    // Handle shift type change
    document.addEventListener('DOMContentLoaded', function() {
        const shiftId = document.getElementById('shiftId');
        const customHours = document.getElementById('customHours');

        if (shiftId) {
            shiftId.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                if (option.value && option.dataset.startHour) {
                    // Auto-fill từ ca được chọn
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
        if (e.target === modal) {
            closeModal();
        }
    });

    renderCalendar();
</script>
@endsection