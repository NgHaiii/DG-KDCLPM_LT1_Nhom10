@extends('layouts.admin-layout')

@section('title', 'Xét duyệt lịch làm việc')
@section('page-title', 'Xét duyệt lịch làm việc')
@section('page-subtitle', 'Phê duyệt, chỉnh sửa ca làm việc và ngày nghỉ')

@section('content')

<style>
    .tabs-container {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 2rem;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .tab-btn {
        flex: 1;
        padding: 1rem;
        border: none;
        background: white;
        cursor: pointer;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-btn:hover { color: #3b82f6; background: #f9fafb; }
    .tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }

    .badge {
        display: inline-block;
        background: #ef4444;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-left: 0.5rem;
    }

    .badge-green {
        background: #10b981;
    }

    .employee-list { display: grid; gap: 1rem; }

    .employee-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .employee-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .employee-info { flex: 1; }
    .employee-name { font-size: 1.1rem; font-weight: 700; color: #1f2937; margin: 0; }
    .employee-meta { font-size: 0.9rem; color: #6b7280; margin: 0.5rem 0 0 0; }

    .request-count {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 0.75rem 1rem;
        border-radius: 4px;
        margin-top: 0.75rem;
        font-size: 0.9rem;
        color: #92400e;
    }

    .request-count.approved {
        background: #d1fae5;
        border-left-color: #10b981;
        color: #047857;
    }

    .btn {
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary { background: #3b82f6; color: white; }
    .btn-primary:hover { background: #2563eb; }
    .btn-success { background: #10b981; color: white; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; }
    .btn-warning { background: #f59e0b; color: white; }
    .btn-warning:hover { background: #d97706; }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 8px;
        color: #9ca3af;
    }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 50;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal.active { display: flex; }

    .modal-content {
        background: white;
        border-radius: 8px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px rgba(0,0,0,0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 { margin: 0; font-size: 1.25rem; }

    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.25rem;
    }

    .modal-close:hover { background: rgba(255,255,255,0.3); }
    .modal-body { padding: 1.5rem; }

    .calendar-section { margin-bottom: 2rem; }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .calendar-header h3 { margin: 0; font-size: 1.1rem; color: #1f2937; }

    .calendar-nav { display: flex; gap: 0.5rem; }
    .calendar-nav button {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 6px;
        cursor: pointer;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        background: #f9fafb;
        padding: 1rem;
        border-radius: 6px;
    }

    .calendar-day {
        aspect-ratio: 1;
        border: 2px solid #e5e7eb;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: 600;
        background: white;
        color: #6b7280;
        transition: all 0.3s ease;
        position: relative;
    }

    .calendar-day:hover { border-color: #3b82f6; background: #f0f9ff; }
    .calendar-day.other-month { color: #d1d5db; background: #fafbfc; cursor: not-allowed; }
    
    /* 🔴 Ngày chờ duyệt - màu vàng */
    .calendar-day.pending { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
    
    /* 🟢 Ngày đã duyệt - màu xanh */
    .calendar-day.approved { background: #d1fae5; border-color: #10b981; color: #047857; }
    
    .calendar-day.selected { background: #3b82f6; border-color: #3b82f6; color: white; }

    .schedule-info { display: none; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin-top: 1rem; }
    .schedule-info.show { display: block; }

    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .info-item { background: white; padding: 0.75rem; border-radius: 6px; border-left: 3px solid #3b82f6; }
    .info-label { font-size: 0.8rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem; }
    .info-value { font-size: 0.95rem; font-weight: 700; color: #1f2937; }

    .form-group { margin-bottom: 1rem; }
    .form-label { display: block; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem; font-size: 0.9rem; }

    .form-control {
        width: 100%;
        padding: 0.6rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 0.9rem;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .form-actions button { flex: 1; min-width: 100px; }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #d1fae5; color: #047857; }

    .legend {
        display: flex;
        gap: 2rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 6px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid;
    }
</style>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card bg-yellow-50 border-l-4 border-yellow-500">
        <p class="text-sm text-gray-600">📋 Đơn chờ duyệt</p>
        <p class="text-3xl font-bold text-yellow-700 mt-2">{{ $stats['total_pending_requests'] ?? 0 }}</p>
    </div>
    <div class="card bg-green-50 border-l-4 border-green-500">
        <p class="text-sm text-gray-600">✅ Đã duyệt</p>
        <p class="text-3xl font-bold text-green-700 mt-2">{{ $stats['total_approved_requests'] ?? 0 }}</p>
    </div>
    <div class="card bg-orange-50 border-l-4 border-orange-500">
        <p class="text-sm text-gray-600">🏖️ Xin nghỉ chờ duyệt</p>
        <p class="text-3xl font-bold text-orange-700 mt-2">{{ $stats['total_pending_offdays'] ?? 0 }}</p>
    </div>
    <div class="card bg-red-50 border-l-4 border-red-500">
        <p class="text-sm text-gray-600">❌ Từ chối</p>
        <p class="text-3xl font-bold text-red-700 mt-2">{{ $stats['total_rejected_requests'] ?? 0 }}</p>
    </div>
</div>

<!-- Tabs - BỎ TAB "Lịch đã duyệt" -->
<div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab('pending-doctors')">
        👨‍⚕️ Bác sĩ chờ duyệt <span class="badge">{{ $pendingDoctorsCount ?? 0 }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('pending-employees')">
        👨‍💼 Nhân viên chờ duyệt <span class="badge">{{ $pendingEmployeesCount ?? 0 }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('offdays')">
        🏖️ Xin nghỉ <span class="badge">{{ $pendingOffDays->count() ?? 0 }}</span>
    </button>
</div>

<!-- Tab 1: Pending Doctors -->
<div id="pending-doctors-tab" class="tab-content active">
    @if(isset($pendingDoctorsList) && $pendingDoctorsList->count() > 0)
        <div class="employee-list">
            @foreach($pendingDoctorsList as $doctor)
            <div class="employee-card">
                <div class="employee-info">
                    <p class="employee-name">👨‍⚕️ {{ $doctor->name }}</p>
                    <p class="employee-meta">Mã: {{ $doctor->code ?? 'N/A' }}</p>
                    <div class="request-count"><span class="status-badge status-pending">Chờ duyệt</span> {{ $doctor->pending_requests_count ?? 0 }} đơn</div>
                </div>
                <button class="btn btn-primary" onclick="openScheduleModal({{ $doctor->id }}, '{{ $doctor->name }}')">Xem & Duyệt</button>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state"><div style="font-size: 2.5rem; margin-bottom: 1rem;">✅</div><p>Không có bác sĩ có đơn chờ duyệt</p></div>
    @endif
</div>

<!-- Tab 2: Pending Employees -->
<div id="pending-employees-tab" class="tab-content">
    @if(isset($pendingEmployeesList) && $pendingEmployeesList->count() > 0)
        <div class="employee-list">
            @foreach($pendingEmployeesList as $employee)
            <div class="employee-card">
                <div class="employee-info">
                    <p class="employee-name">👨‍💼 {{ $employee->name }}</p>
                    <p class="employee-meta">Mã: {{ $employee->code ?? 'N/A' }}</p>
                    <div class="request-count"><span class="status-badge status-pending">Chờ duyệt</span> {{ $employee->pending_requests_count ?? 0 }} đơn</div>
                </div>
                <button class="btn btn-primary" onclick="openScheduleModal({{ $employee->id }}, '{{ $employee->name }}')">Xem & Duyệt</button>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state"><div style="font-size: 2.5rem; margin-bottom: 1rem;">✅</div><p>Không có nhân viên có đơn chờ duyệt</p></div>
    @endif
</div>

<!-- Tab 3: Off Days -->
<div id="offdays-tab" class="tab-content">
    @if($pendingOffDays->count() > 0)
        <div class="employee-list">
            @foreach($pendingOffDays as $offDay)
            <div class="employee-card">
                <div class="employee-info">
                    <p class="employee-name">🏖️ {{ $offDay->employee->name }}</p>
                    <p class="employee-meta">Mã: {{ $offDay->employee->code ?? 'N/A' }}</p>
                    <div class="request-count">📅 {{ $offDay->date->format('d/m/Y') }} | {{ Str::limit($offDay->reason, 50) }}</div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-success" onclick="approveOffDay({{ $offDay->id }})">✅ Duyệt</button>
                    <button class="btn btn-danger" onclick="rejectOffDay({{ $offDay->id }})">❌ Từ chối</button>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state"><div style="font-size: 2.5rem; margin-bottom: 1rem;">✅</div><p>Không có đơn xin nghỉ chờ duyệt</p></div>
    @endif
</div>

<!-- Modal Lịch -->
<div class="modal" id="scheduleModal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>📅 Lịch đăng ký ca</h2>
                <p id="modalEmployeeName" style="margin: 0.25rem 0 0 0; opacity: 0.9; font-size: 0.9rem;"></p>
            </div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>

        <div class="modal-body">
            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #fef3c7; border-color: #f59e0b;"></div>
                    <span>Chờ duyệt</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #d1fae5; border-color: #10b981;"></div>
                    <span>Đã duyệt</span>
                </div>
            </div>

            <!-- Calendar -->
            <div class="calendar-section">
                <div class="calendar-header">
                    <h3>Chọn ngày</h3>
                    <div class="calendar-nav">
                        <button onclick="prevMonth()">← Tháng trước</button>
                        <button onclick="nextMonth()">Tháng sau →</button>
                    </div>
                </div>

                <div style="margin-bottom: 1rem; text-align: center; font-weight: 600; color: #1f2937;" id="monthYear"></div>

                <div class="calendar-grid">
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">T2</div>
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">T3</div>
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">T4</div>
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">T5</div>
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">T6</div>
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">T7</div>
                    <div style="text-align: center; font-weight: 600; color: #6b7280; font-size: 0.85rem;">CN</div>
                </div>
                <div id="calendarDays" class="calendar-grid"></div>
            </div>

            <!-- Schedule Info -->
            <div id="scheduleInfo" class="schedule-info">
                <div class="info-row">
                    <div class="info-item">
                        <p class="info-label">📅 Ngày</p>
                        <p class="info-value" id="infoDate"></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">⏰ Ca</p>
                        <p class="info-value" id="infoShift"></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">🕐 Giờ</p>
                        <p class="info-value" id="infoTime"></p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">📌 Trạng thái</p>
                        <p class="info-value" id="infoStatus"></p>
                    </div>
                </div>

                <!-- Edit Form -->
                <form id="editForm" style="border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                    <input type="hidden" id="scheduleId">
                    <input type="hidden" id="employeeId">
                    <input type="hidden" id="scheduleStatus">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">⏰ Ca làm</label>
                            <select id="editShiftId" class="form-control">
                                <option value="">-- Chọn ca --</option>
                                @foreach($shifts ?? [] as $shift)
                                    <option value="{{ $shift->id }}" 
                                            data-start-hour="{{ $shift->start_hour }}" 
                                            data-start-minute="{{ $shift->start_minute }}"
                                            data-end-hour="{{ $shift->end_hour }}"
                                            data-end-minute="{{ $shift->end_minute }}">
                                        {{ $shift->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">📝 Ghi chú</label>
                            <input type="text" id="editNotes" class="form-control" placeholder="Ghi chú...">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Giờ bắt đầu</label>
                            <select id="editStartHour" class="form-control"></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Phút</label>
                            <select id="editStartMinute" class="form-control"></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Giờ kết thúc</label>
                            <select id="editEndHour" class="form-control"></select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Phút</label>
                            <select id="editEndMinute" class="form-control"></select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-success" id="approveBtn" onclick="approveSchedule()" style="display: none;">✅ Duyệt</button>
                        <button type="button" class="btn btn-danger" id="rejectBtn" onclick="rejectSchedule()" style="display: none;">❌ Từ chối</button>
                        <button type="button" class="btn btn-warning" id="updateBtn" onclick="updateSchedule()" style="display: none;">💾 Cập nhật</button>
                        <button type="button" class="btn" onclick="closeModal()" style="background: #6b7280; color: white;">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let currentScheduleId = null;
    let currentEmployeeId = null;
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let schedulesByDate = {};
    let scheduleCache = {};
    let isLoadingSchedules = false;

    // ===== TAB SWITCHING =====
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    // 🔴 TỐI ƯU: Open modal nhanh hơn
    function openScheduleModal(employeeId, employeeName) {
        currentEmployeeId = employeeId;
        document.getElementById('modalEmployeeName').textContent = employeeName;
        document.getElementById('scheduleModal').classList.add('active');
        
        currentMonth = new Date().getMonth();
        currentYear = new Date().getFullYear();
        
        populateHours();
        loadEmployeeSchedulesOptimized();
    }

    function closeModal() {
        document.getElementById('scheduleModal').classList.remove('active');
        document.getElementById('scheduleInfo').classList.remove('show');
    }

    // 🔴 TỐI ƯU: Load dữ liệu nhanh hơn + cache
    async function loadEmployeeSchedulesOptimized() {
        const cacheKey = `${currentEmployeeId}-all`;
        
        // Nếu đã cache và < 5 phút, dùng cache
        if (scheduleCache[cacheKey] && 
            Date.now() - scheduleCache[cacheKey].timestamp < 5 * 60 * 1000) {
            schedulesByDate = scheduleCache[cacheKey].data;
            renderCalendarOptimized();
            return;
        }

        // Prevent duplicate requests
        if (isLoadingSchedules) return;
        isLoadingSchedules = true;

        // Show loading state
        document.getElementById('calendarDays').innerHTML = 
            '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #6b7280;">⏳ Đang tải dữ liệu...</div>';

        try {
            // 🔴 Load cả pending và approved schedules
            const [pendingRes, approvedRes] = await Promise.all([
                fetch(`/admin/schedule-approval/employee/${currentEmployeeId}/requests`, {
                    signal: AbortSignal.timeout(10000)
                }),
                fetch(`/admin/schedule-approval/employee/${currentEmployeeId}/approved`, {
                    signal: AbortSignal.timeout(10000)
                })
            ]);
            
            if (!pendingRes.ok || !approvedRes.ok) {
                throw new Error('HTTP error');
            }
            
            const pendingData = await pendingRes.json();
            const approvedData = await approvedRes.json();
            
            schedulesByDate = {};
            
            // 🔴 Thêm pending schedules (status = 'pending')
            pendingData.forEach(req => {
                schedulesByDate[req.work_date] = {
                    ...req,
                    approval_status: 'pending'
                };
            });

            // 🔴 Thêm approved schedules (status = 'approved')
            approvedData.forEach(req => {
                schedulesByDate[req.work_date] = {
                    ...req,
                    approval_status: 'approved'
                };
            });

            // Cache kết quả
            scheduleCache[cacheKey] = {
                data: schedulesByDate,
                timestamp: Date.now()
            };

            renderCalendarOptimized();
        } catch (error) {
            console.error('Lỗi load dữ liệu:', error);
            document.getElementById('calendarDays').innerHTML = 
                '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #ef4444;">❌ Lỗi tải dữ liệu. Thử lại.</div>';
        } finally {
            isLoadingSchedules = false;
        }
    }

    // 🔴 TỐI ƯU: Render calendar nhanh hơn
    function renderCalendarOptimized() {
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                          'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        
        document.getElementById('monthYear').textContent = monthNames[currentMonth] + ' - ' + currentYear;
        
        const calendarDays = document.getElementById('calendarDays');
        const fragment = document.createDocumentFragment();

        const prevLastDay = new Date(currentYear, currentMonth, 0).getDate();
        const firstDayOfWeek = firstDay.getDay() || 7;
        
        // Ngày tháng trước
        for (let i = prevLastDay - firstDayOfWeek + 2; i <= prevLastDay; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.textContent = i;
            fragment.appendChild(day);
        }

        // Ngày tháng này
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const day = document.createElement('div');
            const dateStr = formatDate(currentYear, currentMonth, i);
            day.className = 'calendar-day';
            
            const schedule = schedulesByDate[dateStr];
            if (schedule) {
                // 🔴 Highlight dựa trên approval_status
                if (schedule.approval_status === 'approved') {
                    day.classList.add('approved');
                } else if (schedule.approval_status === 'pending') {
                    day.classList.add('pending');
                }
            }
            
            day.textContent = i;
            day.dataset.date = dateStr;
            day.addEventListener('click', () => selectDayOptimized(dateStr, day));
            
            fragment.appendChild(day);
        }

        // Ngày tháng sau
        const nextDaysCount = 42 - (prevLastDay - firstDayOfWeek + 2 + lastDay.getDate());
        for (let i = 1; i <= nextDaysCount; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.textContent = i;
            fragment.appendChild(day);
        }

        // 🔴 Một lần update DOM
        calendarDays.innerHTML = '';
        calendarDays.appendChild(fragment);
    }

    function formatDate(y, m, d) {
        return `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    }

    // 🔴 TỐI ƯU: Select day
    function selectDayOptimized(dateStr, el) {
        const schedule = schedulesByDate[dateStr];
        if (!schedule) return;

        document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
        el.classList.add('selected');

        currentScheduleId = schedule.id;
        
        const date = new Date(dateStr);
        document.getElementById('infoDate').textContent = date.toLocaleDateString('vi-VN');
        document.getElementById('infoShift').textContent = schedule.shift_name || 'N/A';
        document.getElementById('infoTime').textContent = schedule.time_range || 'N/A';
        
        // 🔴 Hiển thị trạng thái: Chờ duyệt hoặc Đã duyệt
        const statusText = schedule.approval_status === 'approved' ? '✅ Đã duyệt' : '⏳ Chờ duyệt';
        const statusColor = schedule.approval_status === 'approved' ? '#047857' : '#92400e';
        document.getElementById('infoStatus').textContent = statusText;
        document.getElementById('infoStatus').style.color = statusColor;
        
        // Update form fields
        document.getElementById('editShiftId').value = schedule.shift_id || '';
        document.getElementById('editNotes').value = schedule.notes || '';
        document.getElementById('editStartHour').value = schedule.start_hour || 0;
        document.getElementById('editStartMinute').value = schedule.start_minute || 0;
        document.getElementById('editEndHour').value = schedule.end_hour || 0;
        document.getElementById('editEndMinute').value = schedule.end_minute || 0;
        document.getElementById('employeeId').value = currentEmployeeId;
        document.getElementById('scheduleStatus').value = schedule.approval_status;

        // 🔴 Show/hide buttons dựa trên approval_status
        const isPending = schedule.approval_status === 'pending';
        document.getElementById('approveBtn').style.display = isPending ? 'block' : 'none';
        document.getElementById('rejectBtn').style.display = isPending ? 'block' : 'none';
        document.getElementById('updateBtn').style.display = isPending ? 'none' : 'block';

        document.getElementById('scheduleInfo').classList.add('show');
    }

    function prevMonth() {
        currentMonth = currentMonth === 0 ? 11 : currentMonth - 1;
        if (currentMonth === 11) currentYear--;
        renderCalendarOptimized();
    }

    function nextMonth() {
        currentMonth = currentMonth === 11 ? 0 : currentMonth + 1;
        if (currentMonth === 0) currentYear++;
        renderCalendarOptimized();
    }

    function populateHours() {
        const hours = [document.getElementById('editStartHour'), document.getElementById('editEndHour')];
        const minutes = [document.getElementById('editStartMinute'), document.getElementById('editEndMinute')];

        hours.forEach(h => {
            h.innerHTML = '';
            for (let i = 0; i <= 23; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = String(i).padStart(2, '0') + 'h';
                h.appendChild(opt);
            }
        });

        minutes.forEach(m => {
            m.innerHTML = '';
            for (let i = 0; i < 60; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = String(i).padStart(2, '0');
                m.appendChild(opt);
            }
        });
    }

    document.getElementById('editShiftId')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            document.getElementById('editStartHour').value = opt.dataset.startHour || 0;
            document.getElementById('editStartMinute').value = opt.dataset.startMinute || 0;
            document.getElementById('editEndHour').value = opt.dataset.endHour || 0;
            document.getElementById('editEndMinute').value = opt.dataset.endMinute || 0;
        }
    });

    async function approveSchedule() {
        if (!currentScheduleId) {
            alert('❌ Chưa chọn ca làm');
            return;
        }

        try {
            const response = await fetch(`{{ route('admin.schedule-approval.approve', ':id') }}`.replace(':id', currentScheduleId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    shift_id: document.getElementById('editShiftId').value,
                    start_hour: document.getElementById('editStartHour').value,
                    start_minute: document.getElementById('editStartMinute').value,
                    end_hour: document.getElementById('editEndHour').value,
                    end_minute: document.getElementById('editEndMinute').value,
                    notes: document.getElementById('editNotes').value
                })
            });
            const data = await response.json();
            alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
            if (data.success) {
                scheduleCache = {};
                loadEmployeeSchedulesOptimized();
                document.getElementById('scheduleInfo').classList.remove('show');
            }
        } catch (error) {
            alert('❌ Lỗi: ' + error.message);
        }
    }

    async function updateSchedule() {
        try {
            const response = await fetch(`{{ route('admin.schedule-approval.approve', ':id') }}`.replace(':id', currentScheduleId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    shift_id: document.getElementById('editShiftId').value,
                    start_hour: document.getElementById('editStartHour').value,
                    start_minute: document.getElementById('editStartMinute').value,
                    end_hour: document.getElementById('editEndHour').value,
                    end_minute: document.getElementById('editEndMinute').value,
                    notes: document.getElementById('editNotes').value
                })
            });
            const data = await response.json();
            alert(data.success ? '✅ Cập nhật thành công!' : '❌ ' + data.message);
            if (data.success) {
                scheduleCache = {};
                loadEmployeeSchedulesOptimized();
                document.getElementById('scheduleInfo').classList.remove('show');
            }
        } catch (error) {
            alert('❌ Lỗi: ' + error.message);
        }
    }

    async function rejectSchedule() {
        const reason = prompt('Lý do từ chối:');
        if (!reason) return;
        try {
            const response = await fetch(`{{ route('admin.schedule-approval.reject', ':id') }}`.replace(':id', currentScheduleId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ notes: reason })
            });
            const data = await response.json();
            alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
            if (data.success) {
                scheduleCache = {};
                loadEmployeeSchedulesOptimized();
                document.getElementById('scheduleInfo').classList.remove('show');
            }
        } catch (error) {
            alert('❌ Lỗi: ' + error.message);
        }
    }

    async function approveOffDay(id) {
        const notes = prompt('Ghi chú (tùy chọn):');
        try {
            const response = await fetch(`{{ route('admin.schedule-approval.off-day.approve', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
                body: JSON.stringify({ notes: notes })
            });
            const data = await response.json();
            alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
            if (data.success) location.reload();
        } catch (error) {
            alert('❌ Lỗi: ' + error.message);
        }
    }

    async function rejectOffDay(id) {
        const reason = prompt('Lý do từ chối:');
        if (!reason) return;
        try {
            const response = await fetch(`{{ route('admin.schedule-approval.off-day.reject', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
                body: JSON.stringify({ notes: reason })
            });
            const data = await response.json();
            alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
            if (data.success) location.reload();
        } catch (error) {
            alert('❌ Lỗi: ' + error.message);
        }
    }
</script>
@endsection