@extends('layouts.admin-layout')

@section('title', 'Giao Ca Trực')
@section('page-title', 'Giao Ca Trực')
@section('page-subtitle', 'Quản lý giao ca trực cho bác sĩ')

@section('content')
<div class="duty-wrapper">
    <!-- Alerts -->
    @if ($message = session('success'))
        <div class="alert-banner alert-success">
            <i class="ri-check-circle-line"></i>
            {!! $message !!}
        </div>
    @endif

    @if ($message = session('error'))
        <div class="alert-banner alert-error">
            <i class="ri-error-warning-line"></i>
            {!! $message !!}
        </div>
    @endif

    <!-- BƯỚC 1: DANH MỤC CHUYÊN KHOA -->
    <div id="step-specialties" class="step-container active">
        <div class="step-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <i class="ri-hospital-line"></i>
                        Quản Lý Giao Ca Trực
                    </h1>
                    <p class="page-desc">Lựa chọn chuyên khoa để quản lý lịch trực của bác sĩ</p>
                </div>
            </div>

            <div class="specialty-grid" id="specialtyGrid">
                @forelse ($specialties as $specialty)
                    <div class="specialty-card" onclick="selectSpecialty('{{ $specialty }}')">
                        <div class="card-icon">👨‍⚕️</div>
                        <h3 class="card-title">{{ $specialty }}</h3>
                        <p class="card-subtitle">{{ $specialtyStats[$specialty] ?? 0 }} bác sĩ</p>
                        <div class="card-action">
                            <span class="action-text">Quản lý</span>
                            <i class="ri-arrow-right-line"></i>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="ri-inbox-line"></i>
                        <p>Không có chuyên khoa nào</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- BƯỚC 2: BẢNG LỊCH THÁNG -->
    <div id="step-calendar" class="step-container" style="display: none;">
        <div class="calendar-header">
            <button class="btn-back" onclick="backToSpecialties()">
                <i class="ri-arrow-left-line"></i>
                Quay Lại
            </button>
            <h2 class="calendar-title" id="calendar-specialty-name"></h2>
            <div class="month-nav">
                <button class="btn-month" id="prevMonth" title="Tháng trước">
                    <i class="ri-arrow-left-s-line"></i>
                </button>
                <span id="month-display" class="month-display"></span>
                <button class="btn-month" id="nextMonth" title="Tháng sau">
                    <i class="ri-arrow-right-s-line"></i>
                </button>
            </div>
        </div>

        <div class="calendar-container">
            <div class="calendar-weekdays">
                <div class="weekday">Thứ 2</div>
                <div class="weekday">Thứ 3</div>
                <div class="weekday">Thứ 4</div>
                <div class="weekday">Thứ 5</div>
                <div class="weekday">Thứ 6</div>
                <div class="weekday">Thứ 7</div>
                <div class="weekday">CN</div>
            </div>
            <div id="calendar-grid" class="calendar-grid">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- LOADING OVERLAY -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p id="loadingText">Đang tải dữ liệu...</p>
        </div>
    </div>

    <!-- BƯỚC 3: MODAL DANH SÁCH BÁC SĨ -->
    <div id="doctorsModal" class="modal-overlay" style="display: none;" onclick="closeDoctorsModal(event)">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title">
                        <i class="ri-team-line"></i>
                        Danh Sách Bác Sĩ
                    </h3>
                    <p class="modal-date" id="modal-date"></p>
                </div>
                <button class="btn-close" onclick="closeDoctorsModal()">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="doctors-list" class="doctors-list">
                    <!-- Populated by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- BƯỚC 4: MODAL GIAO CA TRỰC -->
    <div id="assignModal" class="modal-overlay" style="display: none;" onclick="closeAssignModal(event)">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="ri-edit-line"></i>
                    Giao Ca Trực
                </h3>
                <button class="btn-close" onclick="closeAssignModal()">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <form id="assignForm" method="POST" action="{{ route('admin.duty.store') }}" class="modal-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="form-doctor-id" name="employee_id">
                    <input type="hidden" id="form-work-date" name="work_date">
                    <input type="hidden" name="shift_id" value="1">
                    <input type="hidden" name="assignment_type" value="duty">

                    <div class="form-group">
                        <label><i class="ri-user-3-line"></i> Bác Sĩ</label>
                        <input type="text" class="form-input form-input-static" id="form-doctor-name" disabled>
                    </div>

                    <div class="form-group">
                        <label><i class="ri-calendar-line"></i> Ngày</label>
                        <input type="text" class="form-input form-input-static" id="form-date-display" disabled>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="ri-time-line"></i> Giờ Bắt Đầu</label>
                            <div class="time-group">
                                <select name="start_hour" id="startHour" class="time-input" required>
                                    @for ($h = 0; $h <= 23; $h++)
                                        <option value="{{ $h }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                                <span class="time-separator">:</span>
                                <select name="start_minute" id="startMinute" class="time-input" required>
                                    @for ($m = 0; $m < 60; $m += 5)
                                        <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="ri-time-line"></i> Giờ Kết Thúc</label>
                            <div class="time-group">
                                <select name="end_hour" id="endHour" class="time-input" required>
                                    @for ($h = 0; $h <= 23; $h++)
                                        <option value="{{ $h }}">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                                <span class="time-separator">:</span>
                                <select name="end_minute" id="endMinute" class="time-input" required>
                                    @for ($m = 0; $m < 60; $m += 5)
                                        <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="ri-file-text-line"></i> Ghi Chú</label>
                        <textarea name="notes" class="form-input" rows="3" placeholder="Nhập ghi chú (tùy chọn)..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAssignModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-line"></i>
                        Giao Ca Trực
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // ===== CACHE & PERFORMANCE =====
    const cache = new Map();
    let currentSpecialty = null;
    let currentMonth = new Date().getMonth() + 1;
    let currentYear = new Date().getFullYear();
    let loadingTimeout = null;

    // Debounce function để tránh multiple requests
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Show/Hide Loading
    function showLoading(text = 'Đang tải dữ liệu...') {
        document.getElementById('loadingText').textContent = text;
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // ===== SPECIALTY SELECTION =====
    function selectSpecialty(specialty) {
        currentSpecialty = specialty;
        document.getElementById('calendar-specialty-name').textContent = specialty;
        document.getElementById('step-specialties').style.display = 'none';
        document.getElementById('step-calendar').style.display = 'block';
        currentMonth = new Date().getMonth() + 1;
        currentYear = new Date().getFullYear();
        loadCalendar();
    }

    function backToSpecialties() {
        currentSpecialty = null;
        document.getElementById('step-specialties').style.display = 'block';
        document.getElementById('step-calendar').style.display = 'none';
    }

    // ===== CALENDAR LOADING =====
    function loadCalendar() {
        const cacheKey = `${currentSpecialty}-${currentMonth}-${currentYear}`;
        
        // Return cached data if available
        if (cache.has(cacheKey)) {
            const cachedData = cache.get(cacheKey);
            renderCalendar(cachedData.calendar, cachedData.monthName);
            return;
        }

        showLoading('Đang tải lịch...');

        fetch(`{{ route('admin.duty.api.calendar') }}?specialty=${encodeURIComponent(currentSpecialty)}&month=${currentMonth}&year=${currentYear}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // Cache the data
                    cache.set(cacheKey, data);
                    renderCalendar(data.calendar, data.monthName);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Lỗi khi tải lịch. Vui lòng thử lại!');
            })
            .finally(() => hideLoading());
    }

    // Optimized calendar rendering
    function renderCalendar(calendar, monthName) {
        document.getElementById('month-display').textContent = monthName;
        const grid = document.getElementById('calendar-grid');
        const fragment = document.createDocumentFragment(); // Use fragment for better performance

        const firstDate = Object.keys(calendar).sort()[0];
        const firstDay = new Date(firstDate).getDay();
        const startCol = firstDay === 0 ? 6 : firstDay - 1;

        // Add empty cells
        for (let i = 0; i < startCol; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-day empty';
            fragment.appendChild(emptyCell);
        }

        // Add date cells
        Object.values(calendar).forEach(day => {
            const cell = document.createElement('div');
            cell.className = `calendar-day ${day.hasSchedule ? 'has-schedule' : ''}`;
            cell.innerHTML = `
                <div class="day-number">${day.day}</div>
                ${day.hasSchedule ? `<div class="schedule-badge">${day.scheduleCount}</div>` : ''}
            `;
            cell.onclick = () => showDoctorsByDate(day.date);
            fragment.appendChild(cell);
        });

        grid.innerHTML = '';
        grid.appendChild(fragment);
    }

    // Debounced month navigation
    const debouncedLoadCalendar = debounce(loadCalendar, 300);

    document.getElementById('prevMonth')?.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        debouncedLoadCalendar();
    });

    document.getElementById('nextMonth')?.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        debouncedLoadCalendar();
    });

    // ===== DOCTORS LIST =====
    function showDoctorsByDate(date) {
        const cacheKey = `doctors-${currentSpecialty}-${date}`;
        
        if (cache.has(cacheKey)) {
            const cachedDoctors = cache.get(cacheKey);
            document.getElementById('modal-date').textContent = new Date(date).toLocaleDateString('vi-VN');
            renderDoctorsList(cachedDoctors, date);
            document.getElementById('doctorsModal').style.display = 'flex';
            return;
        }

        showLoading('Đang tải danh sách bác sĩ...');
        document.getElementById('modal-date').textContent = new Date(date).toLocaleDateString('vi-VN');

        fetch(`{{ route('admin.duty.api.doctors-by-date') }}?specialty=${encodeURIComponent(currentSpecialty)}&date=${date}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    cache.set(cacheKey, data.doctors);
                    renderDoctorsList(data.doctors, date);
                    document.getElementById('doctorsModal').style.display = 'flex';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('⚠️ Lỗi khi tải danh sách bác sĩ!');
            })
            .finally(() => hideLoading());
    }

    function renderDoctorsList(doctors, date) {
        const list = document.getElementById('doctors-list');
        
        if (!doctors || doctors.length === 0) {
            list.innerHTML = '<div class="empty-state"><i class="ri-inbox-line"></i><p>Không có bác sĩ nào</p></div>';
            return;
        }

        const fragment = document.createDocumentFragment();
        
        doctors.forEach(doctor => {
            const div = document.createElement('div');
            div.className = 'doctor-item';
            div.innerHTML = `
                <div class="doctor-info">
                    <h4>${doctor.doctor_name}</h4>
                    ${doctor.schedules && doctor.schedules.length > 0 ? `
                        <div class="schedules">
                            ${doctor.schedules.map(s => `<span class="schedule-tag">${s.shift_name}</span>`).join('')}
                        </div>
                    ` : '<p class="no-schedule">Không có ca làm việc</p>'}
                </div>
                <button class="btn-assign-quick" onclick="openAssignModal('${doctor.doctor_id}', '${doctor.doctor_name}', '${date}')">
                    <i class="ri-add-line"></i> Giao
                </button>
            `;
            fragment.appendChild(div);
        });

        list.innerHTML = '';
        list.appendChild(fragment);
    }

    function closeDoctorsModal(event) {
        if (event && event.target !== document.getElementById('doctorsModal')) return;
        document.getElementById('doctorsModal').style.display = 'none';
    }

    // ===== ASSIGN MODAL =====
    function openAssignModal(doctorId, doctorName, date) {
        document.getElementById('form-doctor-id').value = doctorId;
        document.getElementById('form-work-date').value = date;
        document.getElementById('form-doctor-name').value = doctorName;
        document.getElementById('form-date-display').value = new Date(date).toLocaleDateString('vi-VN');

        document.getElementById('startHour').value = '07';
        document.getElementById('startMinute').value = '0';
        document.getElementById('endHour').value = '12';
        document.getElementById('endMinute').value = '0';

        document.getElementById('doctorsModal').style.display = 'none';
        document.getElementById('assignModal').style.display = 'flex';
    }

    function closeAssignModal(event) {
        if (event && event.target !== document.getElementById('assignModal')) return;
        document.getElementById('assignModal').style.display = 'none';
    }

    // Form validation
    document.getElementById('assignForm')?.addEventListener('submit', function(e) {
        const startH = parseInt(document.getElementById('startHour').value);
        const endH = parseInt(document.getElementById('endHour').value);
        if (startH >= endH) {
            e.preventDefault();
            alert('⚠️ Giờ kết thúc phải sau giờ bắt đầu');
        }
    });

    document.getElementById('doctorsModal')?.addEventListener('click', closeDoctorsModal);
    document.getElementById('assignModal')?.addEventListener('click', closeAssignModal);

    // Clear cache periodically (optional)
    setInterval(() => {
        if (cache.size > 20) cache.clear();
    }, 600000); // Clear after 10 minutes
</script>

<style>
    :root {
        --primary: #0ea5e9;
        --primary-dark: #0284c7;
        --primary-light: #e0f2fe;
        --primary-gradient: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
        --success: #10b981;
        --error: #ef4444;
        --bg-light: #f8fafc;
        --border: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        --shadow-lg: 0 10px 30px rgba(15, 23, 42, 0.12);
    }

    .duty-wrapper {
        padding: 0;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        font-size: 32px;
        font-weight: 700;
        color: var(--text-main);
    }

    .page-title i {
        font-size: 36px;
        color: var(--primary);
    }

    .page-desc {
        margin: 8px 0 0 0;
        font-size: 15px;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999;
        backdrop-filter: blur(2px);
    }

    .loading-spinner {
        background: white;
        padding: 32px;
        border-radius: 16px;
        text-align: center;
        box-shadow: var(--shadow-lg);
        animation: slideUp 0.3s ease;
    }

    .spinner {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        border: 4px solid var(--primary-light);
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    #loadingText {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
    }

    /* ===== ALERTS ===== */
    .alert-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        border-radius: 12px;
        font-weight: 500;
        animation: slideDown 0.3s ease;
        border-left: 4px solid;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left-color: var(--success);
    }

    .alert-error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left-color: var(--error);
    }

    .alert-banner i {
        font-size: 20px;
        flex-shrink: 0;
    }

    /* ===== SPECIALTY GRID ===== */
    .specialty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 24px;
    }

    .specialty-card {
        background: white;
        border: 2px solid var(--border);
        border-radius: 16px;
        padding: 28px 24px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .specialty-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--primary-gradient);
        opacity: 0;
        transition: all 0.3s;
        z-index: -1;
    }

    .specialty-card:hover {
        border-color: var(--primary);
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(14, 165, 233, 0.15);
    }

    .specialty-card:hover::before {
        opacity: 0.05;
        left: 0;
    }

    .card-icon {
        font-size: 48px;
        margin-bottom: 14px;
        display: block;
    }

    .card-title {
        margin: 0 0 6px 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }

    .card-subtitle {
        margin: 0 0 16px 0;
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .card-action {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--primary);
        font-weight: 600;
        opacity: 0;
        transition: all 0.3s;
    }

    .specialty-card:hover .card-action {
        opacity: 1;
        gap: 12px;
    }

    /* ===== CALENDAR ===== */
    .calendar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-back:hover {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary);
    }

    .calendar-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
        flex: 1;
    }

    .month-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
    }

    .btn-month {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 18px;
    }

    .btn-month:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .month-display {
        min-width: 160px;
        text-align: center;
    }

    .calendar-container {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
        margin-bottom: 12px;
    }

    .weekday {
        text-align: center;
        font-weight: 700;
        font-size: 13px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 0;
        border-bottom: 2px solid var(--primary-light);
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: var(--bg-light);
        border: 2px solid var(--border);
        border-radius: 12px;
        font-weight: 700;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        color: var(--text-main);
    }

    .calendar-day.empty {
        background: transparent;
        border: none;
        cursor: default;
    }

    .calendar-day:hover:not(.empty) {
        border-color: var(--primary);
        background: var(--primary-light);
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
    }

    .calendar-day.has-schedule {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-color: var(--primary);
    }

    .day-number {
        font-size: 20px;
    }

    .schedule-badge {
        position: absolute;
        bottom: 4px;
        background: var(--primary-gradient);
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 10px;
    }

    /* ===== MODAL ===== */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: var(--shadow-lg);
        animation: modalSlideUp 0.3s ease;
    }

    .modal-lg {
        max-width: 700px;
    }

    .modal-header {
        padding: 28px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .modal-title {
        margin: 0 0 6px 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-title i {
        font-size: 22px;
        color: var(--primary);
    }

    .modal-date {
        margin: 0;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border-radius: 8px;
    }

    .btn-close:hover {
        color: var(--text-main);
        background: var(--bg-light);
    }

    .modal-body {
        padding: 28px;
    }

    .modal-footer {
        padding: 20px 28px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    /* ===== DOCTORS LIST ===== */
    .doctors-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .doctor-item {
        padding: 16px;
        background: var(--bg-light);
        border: 1px solid var(--border);
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
        animation: fadeIn 0.3s ease;
    }

    .doctor-item:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.1);
    }

    .doctor-info {
        flex: 1;
    }

    .doctor-info h4 {
        margin: 0 0 6px 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    .schedules {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .schedule-tag {
        display: inline-block;
        padding: 4px 10px;
        background: var(--primary);
        color: white;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .no-schedule {
        margin: 0;
        font-size: 13px;
        color: var(--text-muted);
    }

    .btn-assign-quick {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-assign-quick:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    }

    /* ===== FORM ===== */
    .modal-form {
        display: flex;
        flex-direction: column;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-main);
    }

    .form-group i {
        color: var(--primary);
        font-size: 16px;
    }

    .form-input,
    .form-input-static {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.2s;
        background: white;
        color: var(--text-main);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    .form-input-static {
        background: var(--bg-light);
        color: var(--text-muted);
        cursor: not-allowed;
    }

    .time-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .time-input {
        flex: 1;
        padding: 11px 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 14px;
        text-align: center;
        font-weight: 600;
        transition: all 0.2s;
    }

    .time-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    .time-separator {
        color: var(--text-muted);
        font-weight: 700;
    }

    textarea.form-input {
        resize: vertical;
    }

    /* ===== BUTTONS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 20px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
    }

    .btn-secondary {
        background: var(--bg-light);
        color: var(--text-main);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: white;
        border-color: var(--primary);
        color: var(--primary);
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-muted);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .empty-state i {
        font-size: 48px;
        opacity: 0.4;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
        font-weight: 500;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes modalSlideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @media (max-width: 768px) {
        .specialty-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .calendar-header {
            flex-direction: column;
        }

        .month-nav {
            width: 100%;
            justify-content: space-between;
        }

        .modal-content {
            width: 95%;
            max-height: 90vh;
        }
    }
</style>
@endsection