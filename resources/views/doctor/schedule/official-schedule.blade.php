@extends('layouts.doctor-layout')

@section('title', 'Lịch làm việc chính thức')

@section('page-title', 'Lịch Làm Việc Chính Thức')
@section('page-subtitle', 'Lịch làm việc & ngày nghỉ được phê duyệt từ quản lý')

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

    .stat-card.schedule { border-left-color: #0284c7; }
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

    .stat-card.schedule p:last-child { color: #0284c7; }
    .stat-card.offday p:last-child { color: #ec4899; }

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

    .legend-dot.approved { background: #0284c7; }
    .legend-dot.offday-dot { background: #ec4899; }

    .calendar-grid {
        display: grid;
        grid-template-columns: 150px repeat(7, 1fr);
        gap: 1px;
        background: #d1d5db;
        padding: 1px;
        border-radius: 8px;
        overflow-x: auto;
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

    .grid-cell:hover { background: #f0fdf4; }

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

    .shift-card.approved {
        background: #dbeafe;
        border-color: #0284c7;
        color: #0c4a6e;
    }

    .shift-card.offday {
        background: #fce7f3;
        border-color: #ec4899;
        color: #9d174d;
    }

    .shift-card.empty {
        background: white;
        border: 2px dashed #d1d5db;
        color: #9ca3af;
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
        background: #dbeafe;
        border-left: 4px solid #0284c7;
        margin-bottom: 0.75rem;
        border-radius: 4px;
    }

    .detail-item.offday {
        background: #fce7f3;
        border-left-color: #ec4899;
    }

    .detail-item label {
        font-weight: 600;
        color: #0284c7;
        font-size: 0.85rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    .detail-item.offday label {
        color: #9d174d;
    }

    .detail-item p {
        margin: 0;
        color: #0284c7;
        font-size: 0.9rem;
    }

    .detail-item.offday p {
        color: #9d174d;
    }

    @media (max-width: 768px) {
        .calendar-grid { grid-template-columns: 80px repeat(7, 1fr); }
        .modal-content { max-width: 90vw; }
        .calendar-header { flex-direction: column; align-items: flex-start; }
        .week-navigation { flex-direction: column; width: 100%; }
        .week-display { width: 100%; }
    }
</style>

<!-- Stats -->
<div class="stats-container">
    <div class="stat-card schedule">
        <p>📋 Ca làm việc được duyệt</p>
        <p id="scheduleCount">0</p>
    </div>

    <div class="stat-card offday">
        <p>🏖️ Ngày nghỉ được duyệt</p>
        <p id="offDayCount">0</p>
    </div>
</div>

<!-- Calendar -->
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
                    <div class="legend-dot approved"></div>
                    <span>✅ Đã duyệt</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot offday-dot"></div>
                    <span>🏖️ Ngày nghỉ</span>
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

<!-- Detail Modal -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeDetailModal()">✕</button>
        <div class="modal-header">📋 Chi tiết ca làm việc</div>
        <div id="detailContent"></div>
        <button type="button" class="btn-cancel" onclick="closeDetailModal()" style="width: 100%; padding: 0.875rem 1rem; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 1rem;">Đóng</button>
    </div>
</div>

<script>
    let currentWeekStart = new Date("{{ $weekStart->format('Y-m-d') }}" + 'T00:00:00');

    let allSchedulesData = {
        schedules: [],
        offDays: []
    };

    let shiftsData = [];

    function formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function rebuildCalendarGrid(weekStart, weekEnd, schedules, offDays) {
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
        
        // Morning shift row
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">☀️</div>
            <div class="grid-time-label">SÁNG</div>
            <div class="grid-time-hours">08:00 - 17:00</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            const schedule = schedules.find(s => s.date === dateStr && s.shift_id === morningShift?.id);
            
            if (schedule) {
                html += `<div class="grid-cell"><div class="shift-card approved" onclick="showDetailModal('${schedule.id}', '${schedule.name}', '${schedule.time}', '${dateStr}')">✅ Duyệt<br><small>${schedule.name}</small></div></div>`;
            } else {
                html += '<div class="grid-cell"><div class="shift-card empty" style="opacity: 0.5;">—</div></div>';
            }
        }
        
        // Evening shift row
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">🌙</div>
            <div class="grid-time-label">TỐI</div>
            <div class="grid-time-hours">14:00 - 22:00</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            const schedule = schedules.find(s => s.date === dateStr && s.shift_id === eveningShift?.id);
            
            if (schedule) {
                html += `<div class="grid-cell"><div class="shift-card approved" onclick="showDetailModal('${schedule.id}', '${schedule.name}', '${schedule.time}', '${dateStr}')">✅ Duyệt<br><small>${schedule.name}</small></div></div>`;
            } else {
                html += '<div class="grid-cell"><div class="shift-card empty" style="opacity: 0.5;">—</div></div>';
            }
        }
        
        // Off-days row
        html += `<div class="grid-time-header">
            <div class="grid-time-icon">🏖️</div>
            <div class="grid-time-label">NGHỈ</div>
            <div class="grid-time-hours">Ngày nghỉ</div>
        </div>`;
        
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(date.getDate() + i);
            const dateStr = formatLocalDate(date);
            
            const offDay = offDays.find(o => o.date === dateStr);
            
            if (offDay) {
                html += `<div class="grid-cell"><div class="shift-card offday" onclick="showOffDayModal('${offDay.reason}', '${dateStr}')">🏖️ Nghỉ<br><small>${offDay.reason}</small></div></div>`;
            } else {
                html += '<div class="grid-cell"><div class="shift-card empty" style="opacity: 0.5;">—</div></div>';
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
        
        fetch('{{ route("doctor.schedule.official-schedule.get-week-data") }}?week_start=' + dateStr, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            console.log('📥 API Response:', data);
            
            shiftsData = data.shifts || [];
            allSchedulesData.schedules = data.schedules || [];
            allSchedulesData.offDays = data.off_days || [];
            
            document.getElementById('scheduleCount').textContent = data.schedules.length;
            document.getElementById('offDayCount').textContent = data.off_days.length;
            
            const weekStart = new Date(data.week_start + 'T00:00:00');
            const weekEnd = new Date(data.week_end + 'T00:00:00');
            
            rebuildCalendarGrid(weekStart, weekEnd, allSchedulesData.schedules, allSchedulesData.offDays);
            
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

    function showDetailModal(scheduleId, name, time, dateStr) {
        const date = new Date(dateStr + 'T00:00:00').toLocaleDateString('vi-VN');
        
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
                <p>Đã được phê duyệt</p>
            </div>
        `;
        
        document.getElementById('detailContent').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function showOffDayModal(reason, dateStr) {
        const date = new Date(dateStr + 'T00:00:00').toLocaleDateString('vi-VN');
        
        const html = `
            <div class="detail-item offday">
                <label>📅 Ngày</label>
                <p>${date}</p>
            </div>
            <div class="detail-item offday">
                <label>🏖️ Lý do</label>
                <p>${reason}</p>
            </div>
            <div class="detail-item offday">
                <label>✅ Trạng thái</label>
                <p>Đã được phê duyệt</p>
            </div>
        `;
        
        document.getElementById('detailContent').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const weekStartStr = document.getElementById('weekDisplay').getAttribute('data-week-start');
        document.getElementById('weekPicker').value = weekStartStr;
        document.getElementById('weekPicker').addEventListener('change', function() {
            jumpToWeek(this.value);
        });
        
        // ✅ Load lịch tuần hiện tại
        loadWeek(currentWeekStart);
    });

    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('detailModal')) closeDetailModal();
    });
</script>

@endsection