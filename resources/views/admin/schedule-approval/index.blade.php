@extends('layouts.admin-layout')

@section('title', 'Xét duyệt lịch làm việc')
@section('page-title', 'Xét duyệt lịch làm việc')
@section('page-subtitle', 'Phê duyệt, chỉnh sửa ca làm việc và ngày nghỉ')

@section('content')

<style>
    /* ===== Tabs ===== */
    .tabs-container {
        display: flex;
        background: white;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(14,165,233,0.07);
        border: 1px solid #e0f2fe;
        margin-bottom: 1.5rem;
    }

    .tab-btn {
        flex: 1;
        padding: 14px 10px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: #64748b;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .tab-btn:hover { color: #0ea5e9; background: #f0f9ff; }
    .tab-btn.active { color: #0ea5e9; border-bottom-color: #0ea5e9; background: #f0f9ff; }

    .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        background: #ef4444;
        color: white;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .tab-count.green { background: #10b981; }

    /* ===== Employee List ===== */
    .employee-list { display: grid; gap: 12px; }

    .employee-card {
        background: white;
        border: 1px solid #e0f2fe;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
        box-shadow: 0 1px 6px rgba(14,165,233,0.05);
    }

    .employee-card:hover {
        box-shadow: 0 6px 20px rgba(14,165,233,0.12);
        border-color: #bae6fd;
        transform: translateY(-1px);
    }

    .emp-avatar {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: white;
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .emp-avatar.purple { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }

    .employee-info { flex: 1; min-width: 0; }
    .employee-name { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0; }
    .employee-meta { font-size: 12px; color: #94a3b8; margin: 3px 0 0 0; }

    .req-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
    }

    .req-chip.pending  { background: #fef9c3; color: #92400e; border: 1px solid #fde68a; }
    .req-chip.approved { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }

    /* ===== Buttons ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        flex-shrink: 0;
    }

    .btn-primary { background: linear-gradient(135deg,#38bdf8,#0ea5e9); color:white; box-shadow:0 3px 10px rgba(14,165,233,0.3); }
    .btn-primary:hover { box-shadow:0 5px 16px rgba(14,165,233,0.45); transform:translateY(-1px); }
    .btn-success { background: #10b981; color: white; }
    .btn-success:hover { background: #059669; }
    .btn-danger  { background: #ef4444; color: white; }
    .btn-danger:hover  { background: #dc2626; }
    .btn-warning { background: #f59e0b; color: white; }
    .btn-warning:hover { background: #d97706; }
    .btn-neutral { background: #f1f5f9; color: #475569; }
    .btn-neutral:hover { background: #e2e8f0; }

    /* ===== Empty State ===== */
    .empty-state {
        text-align: center;
        padding: 48px 24px;
        background: white;
        border-radius: 14px;
        border: 1px solid #e0f2fe;
    }

    .empty-state i { font-size: 44px; color: #bae6fd; display: block; margin-bottom: 12px; }
    .empty-state p { color: #64748b; font-size: 15px; margin: 0; }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* ===== Section heading ===== */
    .section-heading {
        font-size: 14px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin: 20px 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-heading i { font-size: 16px; color: #0ea5e9; }

    /* ===== Modal ===== */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.45);
        z-index: 50;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(2px);
    }

    .modal.active { display: flex; }

    .modal-content {
        background: white;
        border-radius: 16px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        animation: modal-in 0.25s ease;
    }

    @keyframes modal-in {
        from { transform: translateY(-14px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        background: linear-gradient(135deg, #38bdf8, #0284c7);
        color: white;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 16px 16px 0 0;
    }

    .modal-header h2 { margin: 0; font-size: 17px; font-weight: 700; }
    .modal-header p  { margin: 4px 0 0; font-size: 13px; opacity: 0.85; }

    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover { background: rgba(255,255,255,0.35); }
    .modal-body { padding: 20px 24px; }

    /* Calendar */
    .calendar-section { margin-bottom: 1.5rem; }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .calendar-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; }
    .month-label { text-align: center; font-weight: 700; color: #0ea5e9; margin-bottom: 10px; font-size: 14px; }

    .calendar-nav { display: flex; gap: 6px; }
    .calendar-nav button {
        padding: 6px 14px;
        font-size: 13px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        color: #475569;
        transition: all 0.2s;
    }

    .calendar-nav button:hover { border-color: #38bdf8; color: #0ea5e9; background: #f0f9ff; }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }

    .cal-weekday {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        padding: 6px 0;
        text-transform: uppercase;
    }

    .calendar-day {
        aspect-ratio: 1;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        background: white;
        color: #475569;
        transition: all 0.15s;
    }

    .calendar-day:hover { border-color: #38bdf8; background: #f0f9ff; color: #0ea5e9; }
    .calendar-day.other-month { color: #d1d5db; background: #fafafa; cursor: default; pointer-events: none; }
    .calendar-day.registered { background: #fef9c3; border-color: #fbbf24; color: #92400e; }
    .calendar-day.approved   { background: #dcfce7; border-color: #4ade80; color: #15803d; }
    .calendar-day.selected   { background: #0ea5e9; border-color: #0ea5e9; color: white; }

    .schedule-info { display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-top: 12px; }
    .schedule-info.show { display: block; }

    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .info-item {
        background: white;
        padding: 10px 14px;
        border-radius: 8px;
        border-left: 3px solid #0ea5e9;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }

    .info-label { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 3px; }
    .info-value { font-size: 14px; font-weight: 700; color: #0f172a; }

    .form-group { margin-bottom: 12px; }
    .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 5px; font-size: 13px; }

    .form-control {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        background: #fafafa;
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #38bdf8;
        background: white;
        box-shadow: 0 0 0 3px rgba(56,189,248,0.12);
    }

    .form-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        flex-wrap: wrap;
        border-top: 1px solid #f1f5f9;
        padding-top: 14px;
    }

    .form-actions .btn { flex: 1; min-width: 90px; justify-content: center; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-pending  { background: #fef9c3; color: #92400e; }
    .status-approved { background: #dcfce7; color: #15803d; }
</style>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:18px 20px;border-left:4px solid #f59e0b;">
        <div style="width:44px;height:44px;border-radius:11px;background:#fef9c3;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="ri-time-line" style="color:#d97706;"></i>
        </div>
        <div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;">{{ $stats['total_pending_requests'] ?? 0 }}</div>
            <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:2px;">Đơn chờ duyệt</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:18px 20px;border-left:4px solid #10b981;">
        <div style="width:44px;height:44px;border-radius:11px;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="ri-checkbox-circle-line" style="color:#10b981;"></i>
        </div>
        <div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;">{{ $stats['total_approved_requests'] ?? 0 }}</div>
            <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:2px;">Đã duyệt</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:18px 20px;border-left:4px solid #f97316;">
        <div style="width:44px;height:44px;border-radius:11px;background:#ffedd5;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="ri-calendar-close-line" style="color:#ea580c;"></i>
        </div>
        <div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;">{{ $stats['total_pending_offdays'] ?? 0 }}</div>
            <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:2px;">Xin nghỉ chờ duyệt</div>
        </div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:14px;padding:18px 20px;border-left:4px solid #ef4444;">
        <div style="width:44px;height:44px;border-radius:11px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="ri-close-circle-line" style="color:#dc2626;"></i>
        </div>
        <div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;">{{ $stats['total_rejected_requests'] ?? 0 }}</div>
            <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:2px;">Từ chối</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab('pending-doctors')">
        <i class="ri-stethoscope-line"></i> Bác sĩ chờ duyệt
        <span class="tab-count">{{ $pendingDoctorsCount ?? 0 }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('pending-employees')">
        <i class="ri-user-settings-line"></i> Nhân viên chờ duyệt
        <span class="tab-count">{{ $pendingEmployeesCount ?? 0 }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('approved-schedules')">
        <i class="ri-calendar-check-line"></i> Lịch đã duyệt
        <span class="tab-count green">{{ $approvedSchedulesCount ?? 0 }}</span>
    </button>
    <button class="tab-btn" onclick="switchTab('offdays')">
        <i class="ri-rest-time-line"></i> Xin nghỉ
        <span class="tab-count">{{ $pendingOffDays->count() ?? 0 }}</span>
    </button>
</div>

<!-- Tab 1: Pending Doctors -->
<div id="pending-doctors-tab" class="tab-content active">
    @if(isset($pendingDoctorsList) && $pendingDoctorsList->count() > 0)
        <div class="employee-list">
            @foreach($pendingDoctorsList as $doctor)
            <div class="employee-card">
                <div class="emp-avatar">{{ strtoupper(substr($doctor->name,0,1)) }}</div>
                <div class="employee-info">
                    <p class="employee-name">{{ $doctor->name }}</p>
                    <p class="employee-meta"><i class="ri-id-card-line"></i> Mã: {{ $doctor->code ?? 'N/A' }}</p>
                    <span class="req-chip pending">
                        <i class="ri-time-line"></i>
                        {{ $doctor->pending_requests_count ?? 0 }} đơn chờ duyệt
                    </span>
                </div>
                <button class="btn btn-primary" onclick="openScheduleModal({{ $doctor->id }},'{{ $doctor->name }}','pending')">
                    <i class="ri-eye-line"></i> Xem & Duyệt
                </button>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="ri-checkbox-circle-line"></i>
            <p>Không có bác sĩ có đơn chờ duyệt</p>
        </div>
    @endif
</div>

<!-- Tab 2: Pending Employees -->
<div id="pending-employees-tab" class="tab-content">
    @if(isset($pendingEmployeesList) && $pendingEmployeesList->count() > 0)
        <div class="employee-list">
            @foreach($pendingEmployeesList as $employee)
            <div class="employee-card">
                <div class="emp-avatar purple">{{ strtoupper(substr($employee->name,0,1)) }}</div>
                <div class="employee-info">
                    <p class="employee-name">{{ $employee->name }}</p>
                    <p class="employee-meta"><i class="ri-id-card-line"></i> Mã: {{ $employee->code ?? 'N/A' }}</p>
                    <span class="req-chip pending">
                        <i class="ri-time-line"></i>
                        {{ $employee->pending_requests_count ?? 0 }} đơn chờ duyệt
                    </span>
                </div>
                <button class="btn btn-primary" onclick="openScheduleModal({{ $employee->id }},'{{ $employee->name }}','pending')">
                    <i class="ri-eye-line"></i> Xem & Duyệt
                </button>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="ri-checkbox-circle-line"></i>
            <p>Không có nhân viên có đơn chờ duyệt</p>
        </div>
    @endif
</div>

<!-- Tab 3: Approved Schedules -->
<div id="approved-schedules-tab" class="tab-content">
    @if(isset($approvedDoctorsList) && $approvedDoctorsList->count() > 0)
        <div class="section-heading"><i class="ri-stethoscope-line"></i> Bác sĩ</div>
        <div class="employee-list">
            @foreach($approvedDoctorsList as $doctor)
            <div class="employee-card">
                <div class="emp-avatar">{{ strtoupper(substr($doctor->name,0,1)) }}</div>
                <div class="employee-info">
                    <p class="employee-name">{{ $doctor->name }}</p>
                    <p class="employee-meta"><i class="ri-id-card-line"></i> Mã: {{ $doctor->code ?? 'N/A' }}</p>
                    <span class="req-chip approved">
                        <i class="ri-checkbox-circle-line"></i>
                        {{ $doctor->approved_requests_count ?? 0 }} lịch đã duyệt
                    </span>
                </div>
                <button class="btn btn-warning" onclick="openScheduleModal({{ $doctor->id }},'{{ $doctor->name }}','approved')">
                    <i class="ri-edit-line"></i> Chỉnh sửa
                </button>
            </div>
            @endforeach
        </div>
    @endif

    @if(isset($approvedEmployeesList) && $approvedEmployeesList->count() > 0)
        <div class="section-heading" style="margin-top:20px;"><i class="ri-user-settings-line"></i> Nhân viên</div>
        <div class="employee-list">
            @foreach($approvedEmployeesList as $employee)
            <div class="employee-card">
                <div class="emp-avatar purple">{{ strtoupper(substr($employee->name,0,1)) }}</div>
                <div class="employee-info">
                    <p class="employee-name">{{ $employee->name }}</p>
                    <p class="employee-meta"><i class="ri-id-card-line"></i> Mã: {{ $employee->code ?? 'N/A' }}</p>
                    <span class="req-chip approved">
                        <i class="ri-checkbox-circle-line"></i>
                        {{ $employee->approved_requests_count ?? 0 }} lịch đã duyệt
                    </span>
                </div>
                <button class="btn btn-warning" onclick="openScheduleModal({{ $employee->id }},'{{ $employee->name }}','approved')">
                    <i class="ri-edit-line"></i> Chỉnh sửa
                </button>
            </div>
            @endforeach
        </div>
    @endif

    @if((!isset($approvedDoctorsList)||$approvedDoctorsList->count()==0)&&(!isset($approvedEmployeesList)||$approvedEmployeesList->count()==0))
        <div class="empty-state"><i class="ri-inbox-line"></i><p>Chưa có lịch đã duyệt</p></div>
    @endif
</div>

<!-- Tab 4: Off Days -->
<div id="offdays-tab" class="tab-content">
    @if($pendingOffDays->count() > 0)
        <div class="employee-list">
            @foreach($pendingOffDays as $offDay)
            <div class="employee-card">
                <div class="emp-avatar" style="background:linear-gradient(135deg,#fb923c,#f97316);">
                    {{ strtoupper(substr($offDay->employee->name,0,1)) }}
                </div>
                <div class="employee-info">
                    <p class="employee-name">{{ $offDay->employee->name }}</p>
                    <p class="employee-meta"><i class="ri-id-card-line"></i> Mã: {{ $offDay->employee->code ?? 'N/A' }}</p>
                    <span class="req-chip pending">
                        <i class="ri-calendar-line"></i>
                        {{ $offDay->date->format('d/m/Y') }} — {{ Str::limit($offDay->reason, 40) }}
                    </span>
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <button class="btn btn-success" onclick="approveOffDay({{ $offDay->id }})">
                        <i class="ri-check-line"></i> Duyệt
                    </button>
                    <button class="btn btn-danger" onclick="rejectOffDay({{ $offDay->id }})">
                        <i class="ri-close-line"></i> Từ chối
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="empty-state"><i class="ri-checkbox-circle-line"></i><p>Không có đơn xin nghỉ chờ duyệt</p></div>
    @endif
</div>

<!-- Modal Lịch -->
<div class="modal" id="scheduleModal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2><i class="ri-calendar-line"></i> Lịch đăng ký ca</h2>
                <p id="modalEmployeeName"></p>
            </div>
            <button class="modal-close" onclick="closeModal()"><i class="ri-close-line"></i></button>
        </div>

        <div class="modal-body">
            <!-- Calendar -->
            <div class="calendar-section">
                <div class="calendar-header">
                    <h3>Chọn ngày</h3>
                    <div class="calendar-nav">
                        <button onclick="prevMonth()">← Tháng trước</button>
                        <button onclick="nextMonth()">Tháng sau →</button>
                    </div>
                </div>

                <div class="month-label" id="monthYear"></div>

                <div class="calendar-grid">
                    <div class="cal-weekday">T2</div>
                    <div class="cal-weekday">T3</div>
                    <div class="cal-weekday">T4</div>
                    <div class="cal-weekday">T5</div>
                    <div class="cal-weekday">T6</div>
                    <div class="cal-weekday">T7</div>
                    <div class="cal-weekday">CN</div>
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
    let currentScheduleStatus = null;
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let schedulesByDate = {};

    // ===== TAB SWITCHING =====
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    function openScheduleModal(employeeId, employeeName, status) {
        currentEmployeeId = employeeId;
        currentScheduleStatus = status;
        document.getElementById('modalEmployeeName').textContent = employeeName;
        document.getElementById('scheduleModal').classList.add('active');
        document.getElementById('scheduleStatus').value = status;
        
        // Show/hide buttons based on status
        document.getElementById('approveBtn').style.display = status === 'pending' ? 'block' : 'none';
        document.getElementById('rejectBtn').style.display = status === 'pending' ? 'block' : 'none';
        document.getElementById('updateBtn').style.display = status === 'approved' ? 'block' : 'none';
        
        currentMonth = new Date().getMonth();
        currentYear = new Date().getFullYear();
        
        populateHours();
        loadEmployeeSchedules();
    }

    function closeModal() {
        document.getElementById('scheduleModal').classList.remove('active');
        document.getElementById('scheduleInfo').classList.remove('show');
    }

    function loadEmployeeSchedules() {
        const endpoint = currentScheduleStatus === 'pending' 
            ? `/admin/schedule-approval/employee/${currentEmployeeId}/requests`
            : `/admin/schedule-approval/employee/${currentEmployeeId}/approved`;
        
        fetch(endpoint)
            .then(r => r.json())
            .then(data => {
                schedulesByDate = {};
                data.forEach(req => {
                    schedulesByDate[req.work_date] = req;
                });
                renderCalendar();
            })
            .catch(e => console.error('Lỗi:', e));
    }

    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                          'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        
        document.getElementById('monthYear').textContent = monthNames[currentMonth] + ' - ' + currentYear;
        
        const calendarDays = document.getElementById('calendarDays');
        calendarDays.innerHTML = '';

        const prevLastDay = new Date(currentYear, currentMonth, 0).getDate();
        const firstDayOfWeek = firstDay.getDay() || 7;
        
        for (let i = prevLastDay - firstDayOfWeek + 2; i <= prevLastDay; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.textContent = i;
            calendarDays.appendChild(day);
        }

        for (let i = 1; i <= lastDay.getDate(); i++) {
            const day = document.createElement('div');
            const dateStr = formatDate(currentYear, currentMonth, i);
            day.className = 'calendar-day';
            day.textContent = i;

            if (schedulesByDate[dateStr]) {
                day.classList.add(currentScheduleStatus === 'pending' ? 'registered' : 'approved');
            }

            day.onclick = () => selectDay(dateStr, day);
            calendarDays.appendChild(day);
        }

        const nextDaysCount = (calendarDays.children.length % 7) ? 7 - (calendarDays.children.length % 7) : 0;
        for (let i = 1; i <= nextDaysCount; i++) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.textContent = i;
            calendarDays.appendChild(day);
        }
    }

    function formatDate(y, m, d) {
        return `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    }

    function selectDay(dateStr, el) {
        const schedule = schedulesByDate[dateStr];
        if (!schedule) return;

        document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
        el.classList.add('selected');

        currentScheduleId = schedule.id;
        
        document.getElementById('infoDate').textContent = new Date(dateStr).toLocaleDateString('vi-VN');
        document.getElementById('infoShift').textContent = schedule.shift_name;
        document.getElementById('infoTime').textContent = schedule.time_range;
        
        document.getElementById('editShiftId').value = schedule.shift_id;
        document.getElementById('editNotes').value = schedule.notes || '';
        document.getElementById('editStartHour').value = schedule.start_hour;
        document.getElementById('editStartMinute').value = schedule.start_minute;
        document.getElementById('editEndHour').value = schedule.end_hour;
        document.getElementById('editEndMinute').value = schedule.end_minute;
        document.getElementById('employeeId').value = currentEmployeeId;

        document.getElementById('scheduleInfo').classList.add('show');
    }

    function prevMonth() {
        currentMonth = currentMonth === 0 ? 11 : currentMonth - 1;
        if (currentMonth === 11) currentYear--;
        renderCalendar();
    }

    function nextMonth() {
        currentMonth = currentMonth === 11 ? 0 : currentMonth + 1;
        if (currentMonth === 0) currentYear++;
        renderCalendar();
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
            document.getElementById('editStartHour').value = opt.dataset.startHour;
            document.getElementById('editStartMinute').value = opt.dataset.startMinute;
            document.getElementById('editEndHour').value = opt.dataset.endHour;
            document.getElementById('editEndMinute').value = opt.dataset.endMinute;
        }
    });

    async function approveSchedule() {
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
            if (data.success) location.reload();
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
                loadEmployeeSchedules();
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
            if (data.success) location.reload();
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