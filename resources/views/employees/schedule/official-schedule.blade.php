@extends('layouts.employee-layout')

@section('title', 'Lịch làm việc chính thức')

@section('page-title', 'Lịch Làm Việc Chính Thức')
@section('page-subtitle', 'Lịch làm việc & ngày nghỉ được phê duyệt từ quản lý')

@section('content')

<style>
    .official-calendar {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .calendar-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .calendar-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
    }

    .calendar-nav {
        display: flex;
        gap: 1rem;
    }

    .btn-nav {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-nav:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Calendar Grid */
    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .weekday {
        text-align: center;
        font-weight: 700;
        color: #6b7280;
        padding: 0.75rem;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 0.9rem;
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
        position: relative;
        min-height: 100px;
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
        border-color: #f3f4f6;
    }

    .calendar-day.other-month:hover {
        transform: none;
        background: #f9fafb;
        border-color: #f3f4f6;
    }

    .calendar-day.today {
        background: #dbeafe;
        border-color: #3b82f6;
        font-weight: 700;
    }

    /* Approved Schedule */
    .calendar-day.has-schedule {
        background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
        border-color: #10b981;
        font-weight: 600;
    }

    .calendar-day.has-schedule:hover {
        background: linear-gradient(135deg, #c6f6d5 0%, #e6fffa 100%);
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    /* Approved Off-Day */
    .calendar-day.has-offday {
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        border-color: #ec4899;
        font-weight: 600;
    }

    .calendar-day.has-offday:hover {
        background: linear-gradient(135deg, #fbcfe8 0%, #f9a8d4 100%);
        border-color: #be185d;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.2);
    }

    .day-number {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .day-content {
        font-size: 0.7rem;
        font-weight: 600;
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }

    .day-badge {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
    }

    .day-badge.offday {
        background: #ec4899;
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

    .detail-item.offday {
        background: #fce7f3;
        border-left-color: #ec4899;
    }

    .detail-item label {
        font-weight: 600;
        color: #0c4a6e;
        font-size: 0.85rem;
        display: block;
        margin-bottom: 0.25rem;
    }

    .detail-item.offday label {
        color: #9d174d;
    }

    .detail-item p {
        margin: 0;
        color: #0c4a6e;
        font-size: 0.9rem;
    }

    .detail-item.offday p {
        color: #9d174d;
    }

    .legend {
        display: flex;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid;
    }

    .legend-schedule {
        background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
        border-color: #10b981;
    }

    .legend-offday {
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        border-color: #ec4899;
    }

    @media (max-width: 768px) {
        .official-calendar {
            padding: 1rem;
        }

        .calendar-day {
            min-height: 80px;
            padding: 0.75rem;
        }

        .day-number {
            font-size: 1rem;
        }

        .day-content {
            font-size: 0.6rem;
        }

        .calendar-controls {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>

<div class="official-calendar">
    <!-- Legend -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color legend-schedule"></div>
            <span style="font-size: 0.9rem; font-weight: 600; color: #10b981;">📋 Ca làm việc được duyệt</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-offday"></div>
            <span style="font-size: 0.9rem; font-weight: 600; color: #ec4899;">🏖️ Ngày nghỉ được duyệt</span>
        </div>
    </div>

    <!-- Calendar Controls -->
    <div class="calendar-controls">
        <h2 class="calendar-title" id="monthYear">Tháng 5 - 2026</h2>
        <div class="calendar-nav">
            <button class="btn-nav" onclick="previousMonth()">← Tháng trước</button>
            <button class="btn-nav" onclick="nextMonth()">Tháng sau →</button>
        </div>
    </div>

    <!-- Calendar -->
    <div>
        <!-- Weekdays -->
        <div class="calendar-weekdays">
            <div class="weekday">Thứ Hai</div>
            <div class="weekday">Thứ Ba</div>
            <div class="weekday">Thứ Tư</div>
            <div class="weekday">Thứ Năm</div>
            <div class="weekday">Thứ Sáu</div>
            <div class="weekday">Thứ Bảy</div>
            <div class="weekday">Chủ Nhật</div>
        </div>

        <!-- Days -->
        <div class="calendar-days" id="calendarDays"></div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-header">📅 Chi tiết ngày</div>
        <div id="modalContent"></div>
    </div>
</div>

<script>
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    const scheduleData = {
        schedules: {!! json_encode($approvedSchedules->map(function($s) {
            return [
                'date' => $s->work_date->format('Y-m-d'),
                'name' => optional($s->shift)->name ?? 'Ca làm việc',
                'time' => $s->time_range,
            ];
        })) !!},
        offDays: {!! json_encode($approvedOffDays->map(function($o) {
            return [
                'date' => optional($o->date)->format('Y-m-d') ?? $o->date,
                'reason' => $o->reason ?? 'Xin nghỉ',
            ];
        })) !!}
    };

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

        // Ngày tháng trước
        for (let i = prevLastDay - firstDayOfWeek + 2; i <= prevLastDay; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.innerHTML = `<div class="day-number">${i}</div>`;
            calendarDays.appendChild(day);
        }

        // Ngày tháng hiện tại
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const day = document.createElement('div');
            const dateStr = formatDate(currentYear, currentMonth, i);

            day.className = 'calendar-day';

            if (dateStr === todayDate) {
                day.classList.add('today');
            }

            const schedule = scheduleData.schedules.find(s => s.date === dateStr);
            const offDay = scheduleData.offDays.find(o => o.date === dateStr);

            let html = `<div class="day-number">${i}</div><div class="day-content">`;

            if (schedule || offDay) {
                if (schedule) {
                    day.classList.add('has-schedule');
                    html += `<div class="day-badge"></div>`;
                }
                if (offDay) {
                    day.classList.add('has-offday');
                    html += `<div class="day-badge offday"></div>`;
                }
            }

            html += `</div>`;
            day.innerHTML = html;

            day.style.cursor = 'pointer';
            day.onclick = () => {
                if (schedule || offDay) {
                    showModal(dateStr, schedule, offDay);
                }
            };

            calendarDays.appendChild(day);
        }

        // Ngày tháng sau
        const totalCells = calendarDays.children.length;
        const nextDaysCount = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);

        for (let i = 1; i <= nextDaysCount; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.innerHTML = `<div class="day-number">${i}</div>`;
            calendarDays.appendChild(day);
        }
    }

    function formatDate(year, month, day) {
        const m = String(month + 1).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        return `${year}-${m}-${d}`;
    }

    function previousMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    }

    function showModal(dateStr, schedule, offDay) {
        const date = new Date(dateStr + 'T00:00:00').toLocaleDateString('vi-VN');
        let html = `<p style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; color: #111827;">📅 ${date}</p>`;

        if (schedule) {
            html += `
                <div class="detail-item">
                    <label>⏰ Ca làm việc</label>
                    <p>${schedule.name}</p>
                </div>
                <div class="detail-item">
                    <label>🕐 Thời gian</label>
                    <p>${schedule.time}</p>
                </div>
                <div class="detail-item">
                    <label>✅ Trạng thái</label>
                    <p>Đã được phê duyệt</p>
                </div>
            `;
        }

        if (offDay) {
            html += `
                <div class="detail-item offday">
                    <label>🏖️ Ngày nghỉ</label>
                    <p>${offDay.reason}</p>
                </div>
                <div class="detail-item offday">
                    <label>✅ Trạng thái</label>
                    <p>Đã được phê duyệt</p>
                </div>
            `;
        }

        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('detailModal').classList.remove('active');
    }

    window.addEventListener('click', (e) => {
        const modal = document.getElementById('detailModal');
        if (e.target === modal) {
            closeModal();
        }
    });

    renderCalendar();
</script>

@endsection