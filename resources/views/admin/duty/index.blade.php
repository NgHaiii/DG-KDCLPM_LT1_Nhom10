@extends('layouts.admin-layout')

@section('title', 'Giao Ca Trực')
@section('page-title', 'Giao Ca Trực')
@section('page-subtitle', 'Quản lý giao ca trực cho bác sĩ')

@section('header-actions')
    <div style="display: flex; gap: 12px; align-items: center;">
        <button class="btn-nav-week" id="prevWeekBtn" title="Tuần trước">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18l-6-6 6-6"></polyline>
            </svg>
            Tuần Trước
        </button>
        <div class="week-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span id="week-display">01/06/2026 - 07/06/2026</span>
        </div>
        <button class="btn-nav-week" id="nextWeekBtn" title="Tuần sau">
            Tuần Sau
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18l6-6-6-6"></polyline>
            </svg>
        </button>
    </div>
@endsection

@section('content')
<div class="duty-container">
    <!-- Alerts -->
    @if ($message = session('success'))
        <div class="alert alert-success alert-custom" role="alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>{!! $message !!}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if ($message = session('error'))
        <div class="alert alert-danger alert-custom" role="alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span>{!! $message !!}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    <div class="duty-layout">
        <!-- CỘT TRÁI: SIDEBAR -->
        <aside class="duty-sidebar">
            <!-- Danh mục chuyên khoa -->
            <div class="sidebar-section">
                <div class="section-header">
                    <h3>📋 Danh Mục Chuyên Khoa</h3>
                </div>
                <div class="specialty-list">
                    @if ($specialties->count() > 0)
                        @foreach ($specialties as $specialty)
                            <a href="{{ route('admin.duty.index', ['specialty' => $specialty]) }}" 
                               class="specialty-item {{ $selectedSpecialty === $specialty ? 'active' : '' }}">
                                <span class="specialty-icon">🏥</span>
                                <span class="specialty-name">{{ $specialty ?? 'Chuyên Khoa' }}</span>
                                <span class="specialty-badge">
                                    {{ App\Models\Employee::where('is_doctor', true)->where('specialization', $specialty)->count() }}
                                </span>
                            </a>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <p>Không có chuyên khoa nào</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Form giao ca trực -->
            <div class="sidebar-section form-section">
                <div class="section-header">
                    <h3>✏️ Giao Ca Trực Nhanh</h3>
                </div>
                <form id="quickAssignForm" method="POST" action="{{ route('admin.duty.store') }}" class="quick-assign-form">
                    @csrf
                    
                    <!-- Bác sĩ -->
                    <div class="form-group">
                        <label>👨‍⚕️ Bác Sĩ</label>
                        <select id="employeeSelect" name="employee_id" class="form-input" required>
                            <option value="">-- Chọn Bác Sĩ --</option>
                        </select>
                    </div>

                    <!-- Ngày làm việc -->
                    <div class="form-group">
                        <label>📅 Ngày Làm Việc</label>
                        <input type="date" id="workDateInput" name="work_date" class="form-input" required>
                    </div>

                    <!-- Thời gian từ -->
                    <div class="form-group">
                        <label>⏱️ Từ Giờ</label>
                        <div class="time-inputs">
                            <select name="start_hour" id="startHour" class="form-input time-select" required>
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ $h }}" {{ $h == 7 ? 'selected' : '' }}>
                                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <span class="time-divider">:</span>
                            <select name="start_minute" id="startMinute" class="form-input time-select">
                                @for ($m = 0; $m < 60; $m += 5)
                                    <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Thời gian đến -->
                    <div class="form-group">
                        <label>⏱️ Đến Giờ</label>
                        <div class="time-inputs">
                            <select name="end_hour" id="endHour" class="form-input time-select" required>
                                @for ($h = 0; $h <= 23; $h++)
                                    <option value="{{ $h }}" {{ $h == 12 ? 'selected' : '' }}>
                                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <span class="time-divider">:</span>
                            <select name="end_minute" id="endMinute" class="form-input time-select">
                                @for ($m = 0; $m < 60; $m += 5)
                                    <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="form-group">
                        <label>📝 Ghi Chú</label>
                        <textarea name="notes" class="form-input textarea" rows="2" placeholder="Nhập ghi chú (tuỳ chọn)..."></textarea>
                    </div>

                    <!-- Nút submit -->
                    <button type="submit" class="btn-submit-duty">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Giao Ca Trực
                    </button>
                </form>
            </div>
        </aside>

        <!-- CỘT PHẢI: BẢNG LỊCH -->
        <main class="duty-main">
            <div class="schedule-card">
                <div class="schedule-header">
                    <div class="header-content">
                        <h2>📅 Lịch Làm Việc</h2>
                        <p class="header-subtitle">{{ $selectedSpecialty ?? 'Chọn Chuyên Khoa' }}</p>
                    </div>
                </div>

                <div class="schedule-body">
                    <div id="loading" class="loading-state" style="display: none;">
                        <div class="spinner"></div>
                        <p>Đang tải dữ liệu...</p>
                    </div>

                    <div class="table-responsive" id="schedule-grid-container">
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th class="col-doctor">👨‍⚕️ Bác Sĩ</th>
                                    <th colspan="7" class="text-center">Lịch Làm Trong Tuần</th>
                                </tr>
                            </thead>
                            <tbody id="schedule-tbody">
                                <tr>
                                    <td colspan="8" class="text-center empty-message">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="empty-icon">
                                            <path d="M12 9v2m0 4v2m6.364-1.636l-1.414-1.414M7.05 7.05L5.636 5.636m8.728 0l1.414 1.414M7.05 16.95l-1.414 1.414"></path>
                                        </svg>
                                        Vui lòng chọn một chuyên khoa để xem lịch
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const selectedSpecialty = "{{ $selectedSpecialty }}";
    const startDate = "{{ $startDate->format('Y-m-d') }}";
    let currentWeekStart = startDate;

    // Load dữ liệu bác sĩ
    function loadDoctorsBySpecialty() {
        if (!selectedSpecialty) {
            document.getElementById('employeeSelect').innerHTML = '<option value="">-- Chọn Bác Sĩ --</option>';
            return;
        }

        fetch(`{{ route('admin.duty.api.schedule-grid') }}?specialty=${selectedSpecialty}&week_start=${currentWeekStart}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('employeeSelect');
                    let options = '<option value="">-- Chọn Bác Sĩ --</option>';
                    
                    data.data.forEach(doc => {
                        options += `<option value="${doc.doctor_id}">${doc.doctor_name}</option>`;
                    });
                    
                    select.innerHTML = options;
                }
            })
            .catch(err => console.error('Error:', err));
    }

    // Load bảng lịch
    function loadScheduleGrid(weekStart) {
        if (!selectedSpecialty) {
            document.getElementById('schedule-tbody').innerHTML = 
                `<tr><td colspan="8" class="text-center empty-message">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="empty-icon">
                        <path d="M12 9v2m0 4v2m6.364-1.636l-1.414-1.414M7.05 7.05L5.636 5.636m8.728 0l1.414 1.414M7.05 16.95l-1.414 1.414"></path>
                    </svg>
                    Vui lòng chọn một chuyên khoa
                </td></tr>`;
            return;
        }

        document.getElementById('loading').style.display = 'flex';
        document.getElementById('schedule-tbody').innerHTML = '';

        fetch(`{{ route('admin.duty.api.schedule-grid') }}?specialty=${selectedSpecialty}&week_start=${weekStart}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                if (!data.success) {
                    alert('Lỗi: ' + data.error);
                    return;
                }
                renderScheduleTable(data.data, data.week_start, data.week_end);
            })
            .catch(err => {
                document.getElementById('loading').style.display = 'none';
                console.error('Error:', err);
            });
    }

    function renderScheduleTable(doctors, weekStart, weekEnd) {
        const tbody = document.getElementById('schedule-tbody');
        tbody.innerHTML = '';

        if (doctors.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center empty-message">Không có bác sĩ nào trong chuyên khoa này</td></tr>`;
            return;
        }

        const dayNames = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'];
        
        // Header row
        const headerRow = document.createElement('tr');
        headerRow.className = 'header-row';
        headerRow.innerHTML = '<th class="col-doctor">👨‍⚕️ Bác Sĩ</th>';
        
        doctors[0].days.forEach((day, i) => {
            headerRow.innerHTML += `
                <th class="col-day">
                    <div class="day-name">${dayNames[i]}</div>
                    <div class="day-date">${day.day_num}</div>
                </th>
            `;
        });
        tbody.appendChild(headerRow);

        // Data rows
        doctors.forEach(doctor => {
            const row = document.createElement('tr');
            row.className = 'data-row';
            row.innerHTML = `
                <td class="col-doctor">
                    <div class="doctor-name">${doctor.doctor_name}</div>
                </td>
            `;

            doctor.days.forEach(day => {
                const cell = document.createElement('td');
                cell.className = 'col-schedule';

                if (day.schedules.length === 0) {
                    cell.innerHTML = '<div class="schedule-empty">Không có lịch</div>';
                } else {
                    let html = '';
                    day.schedules.forEach(schedule => {
                        html += `
                            <div class="schedule-item">
                                <div class="schedule-name">${schedule.shift_name}</div>
                                <div class="schedule-time">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    ${schedule.time}
                                </div>
                            </div>
                        `;
                    });
                    cell.innerHTML = html;
                }
                row.appendChild(cell);
            });

            tbody.appendChild(row);
        });

        document.getElementById('week-display').textContent = `${weekStart} - ${weekEnd}`;
    }

    // Events
    document.addEventListener('DOMContentLoaded', function() {
        if (selectedSpecialty) {
            loadDoctorsBySpecialty();
            loadScheduleGrid(currentWeekStart);
        }

        document.getElementById('prevWeekBtn').addEventListener('click', function() {
            const date = new Date(currentWeekStart);
            date.setDate(date.getDate() - 7);
            currentWeekStart = date.toISOString().split('T')[0];
            loadScheduleGrid(currentWeekStart);
            loadDoctorsBySpecialty();
        });

        document.getElementById('nextWeekBtn').addEventListener('click', function() {
            const date = new Date(currentWeekStart);
            date.setDate(date.getDate() + 7);
            currentWeekStart = date.toISOString().split('T')[0];
            loadScheduleGrid(currentWeekStart);
            loadDoctorsBySpecialty();
        });

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('workDateInput').value = today;

        document.getElementById('quickAssignForm').addEventListener('submit', function(e) {
            const startH = parseInt(document.getElementById('startHour').value);
            const endH = parseInt(document.getElementById('endHour').value);
            if (startH >= endH) {
                e.preventDefault();
                alert('⚠️ Giờ kết thúc phải sau giờ bắt đầu');
            }
        });
    });
</script>

<style>
    .duty-container {
        padding: 0;
    }

    .alert-custom {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .btn-close-alert {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: inherit;
        opacity: 0.7;
        transition: opacity 0.2s;
        margin-left: auto;
    }

    .btn-close-alert:hover {
        opacity: 1;
    }

    .btn-nav-week {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-nav-week:hover {
        background: #f5f5f5;
        border-color: #667eea;
        color: #667eea;
    }

    .week-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        min-width: 240px;
        text-align: center;
    }

    .duty-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
        padding: 0;
    }

    .duty-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-section {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .sidebar-section:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .section-header {
        padding: 16px 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .section-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .specialty-list {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .specialty-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: #f8f9fa;
        border: 2px solid transparent;
        border-radius: 8px;
        color: #333;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .specialty-item:hover {
        background: #f0f0f0;
        border-color: #667eea;
        transform: translateX(4px);
    }

    .specialty-item.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .specialty-icon {
        font-size: 18px;
    }

    .specialty-name {
        flex: 1;
    }

    .specialty-badge {
        display: inline-block;
        padding: 4px 8px;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .specialty-item.active .specialty-badge {
        background: rgba(255, 255, 255, 0.3);
    }

    .form-section {
        background: linear-gradient(135deg, #fff5f7 0%, #f5f0ff 100%);
        border: 1px solid #f0e6ff;
    }

    .form-section .section-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .quick-assign-form {
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        letter-spacing: 0.3px;
    }

    .form-input {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.2s ease;
        background: white;
        color: #333;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .textarea {
        resize: vertical;
        min-height: 60px;
    }

    .time-inputs {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .time-select {
        flex: 1;
        text-align: center;
    }

    .time-divider {
        font-size: 16px;
        font-weight: 600;
        color: #999;
    }

    .btn-submit-duty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 8px;
    }

    .btn-submit-duty:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-submit-duty:active {
        transform: translateY(0);
    }

    .duty-main {
        min-height: 600px;
    }

    .schedule-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .schedule-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .schedule-header h2 {
        margin: 0 0 4px 0;
        font-size: 18px;
        font-weight: 700;
    }

    .header-subtitle {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
    }

    .schedule-body {
        padding: 24px;
        overflow-x: auto;
    }

    .loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        gap: 16px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f0f0f0;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .schedule-table thead tr {
        background: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
    }

    .schedule-table th {
        padding: 14px 12px;
        text-align: left;
        font-weight: 600;
        color: #666;
        letter-spacing: 0.3px;
    }

    .schedule-table th.text-center {
        text-align: center;
    }

    .col-doctor {
        width: 140px;
        position: sticky;
        left: 0;
        background: white;
        z-index: 10;
    }

    .col-day {
        min-width: 130px;
        text-align: center;
    }

    .day-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }

    .day-date {
        font-size: 12px;
        color: #999;
        font-weight: normal;
    }

    .schedule-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .schedule-table tbody tr:hover {
        background: #fafafa;
    }

    .schedule-table td {
        padding: 14px 12px;
        vertical-align: top;
    }

    .col-schedule {
        text-align: center;
        min-width: 130px;
    }

    .doctor-name {
        font-weight: 600;
        color: #667eea;
    }

    .schedule-item {
        padding: 8px;
        background: linear-gradient(135deg, #e7f3ff 0%, #f0f7ff 100%);
        border-left: 3px solid #667eea;
        border-radius: 6px;
        margin-bottom: 6px;
        text-align: left;
        font-size: 12px;
    }

    .schedule-name {
        font-weight: 600;
        color: #667eea;
        margin-bottom: 4px;
    }

    .schedule-time {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #666;
    }

    .schedule-empty {
        padding: 12px 8px;
        color: #999;
        font-size: 12px;
        background: #f5f5f5;
        border-radius: 6px;
    }

    .empty-message {
        padding: 40px 20px;
        color: #999;
        text-align: center;
    }

    .empty-icon {
        margin-bottom: 12px;
        color: #ddd;
    }

    .empty-state {
        padding: 24px 18px;
        text-align: center;
        color: #999;
        background: #fafafa;
        border-radius: 8px;
    }

    .text-center {
        text-align: center;
    }

    @media (max-width: 1024px) {
        .duty-layout {
            grid-template-columns: 1fr;
        }

        .duty-sidebar {
            order: 2;
        }

        .duty-main {
            order: 1;
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection