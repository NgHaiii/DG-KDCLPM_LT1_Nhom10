@extends('layouts.admin-layout')

@section('title', 'Quản Lý Ca Làm Việc')
@section('page-title', 'Quản Lý Ca Làm Việc')
@section('page-subtitle', 'Tạo, chỉnh sửa và xóa các ca làm việc')

@section('header-actions')
    <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="ri-add-line"></i> Thêm Ca Mới
    </button>
@endsection

@section('styles')
<style>
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(14,165,233,0.07);
        border: 1px solid #e0f2fe;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-icon.blue   { background: #e0f2fe; color: #0ea5e9; }
    .stat-icon.indigo { background: #e0e7ff; color: #6366f1; }
    .stat-icon.teal   { background: #ccfbf1; color: #14b8a6; }

    .stat-value { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; }
    .stat-label { font-size: 13px; color: #64748b; font-weight: 500; margin-top: 4px; }

    /* Table */
    .table-wrapper {
        background: white;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(14,165,233,0.07);
        border: 1px solid #e0f2fe;
        overflow: hidden;
    }

    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-toolbar-title {
        font-weight: 700;
        font-size: 15px;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-toolbar-title i { color: #0ea5e9; font-size: 18px; }

    table { width: 100%; border-collapse: collapse; }

    thead tr { background: #f8fafc; }

    thead th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 1px solid #e2e8f0;
    }

    tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fafc; }
    tbody td { padding: 14px 16px; font-size: 14px; color: #334155; vertical-align: middle; }

    .shift-name {
        font-weight: 700;
        color: #0ea5e9;
        font-size: 14px;
    }

    .time-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        padding: 5px 11px;
        border-radius: 8px;
        color: #0284c7;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        font-size: 13px;
    }

    .time-chip i { font-size: 13px; }

    .tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .tag-doctor   { background: #dbeafe; color: #1d4ed8; }
    .tag-employee { background: #ede9fe; color: #7c3aed; }
    .tag-active   { background: #dcfce7; color: #15803d; }
    .tag-inactive { background: #f1f5f9; color: #64748b; }

    .actions { display: flex; gap: 8px; }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-icon.edit   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .btn-icon.delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .btn-icon.edit:hover   { background: #dbeafe; }
    .btn-icon.delete:hover { background: #fee2e2; }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 64px 32px;
        color: #94a3b8;
    }

    .empty-state i { font-size: 52px; color: #bae6fd; margin-bottom: 16px; display: block; }
    .empty-state p { font-size: 15px; margin-bottom: 8px; color: #64748b; }
    .empty-state small { font-size: 13px; color: #94a3b8; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.4);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(2px);
    }

    .modal.show { display: flex; }

    .modal-box {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        animation: modal-in 0.25s ease;
    }

    @keyframes modal-in {
        from { transform: translateY(-16px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }

    .modal-head {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border-radius: 16px 16px 0 0;
    }

    .modal-head h3 { margin: 0; font-size: 17px; font-weight: 700; color: white; }

    .modal-close-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,0.2);
        color: white;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close-btn:hover { background: rgba(255,255,255,0.35); }

    .modal-body { padding: 24px; }

    .form-group { margin-bottom: 18px; }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-label .req { color: #ef4444; }

    .form-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        transition: all 0.2s;
        background: #fafafa;
        box-sizing: border-box;
    }

    .form-input:focus {
        outline: none;
        border-color: #38bdf8;
        background: white;
        box-shadow: 0 0 0 3px rgba(56,189,248,0.12);
    }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .check-group { display: flex; gap: 20px; margin-top: 6px; }

    .check-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        flex: 1;
        transition: all 0.2s;
    }

    .check-item:has(input:checked) { border-color: #38bdf8; background: #f0f9ff; }
    .check-item input { width: 16px; height: 16px; cursor: pointer; accent-color: #0ea5e9; }
    .check-item span { font-size: 13px; font-weight: 600; color: #374151; }

    .modal-foot {
        padding: 16px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-cancel-modal {
        padding: 9px 20px;
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-cancel-modal:hover { background: #e2e8f0; }

    .btn-save {
        padding: 9px 24px;
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(14,165,233,0.3);
        transition: all 0.2s;
    }

    .btn-save:hover { box-shadow: 0 6px 16px rgba(14,165,233,0.45); transform: translateY(-1px); }

    .err-msg { color: #dc2626; font-size: 12px; margin-top: 4px; }

    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
        .actions { flex-direction: column; }
    }
</style>
@endsection

@section('content')

<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ri-time-line"></i></div>
        <div>
            <div class="stat-value">{{ $shifts->count() }}</div>
            <div class="stat-label">Tổng ca làm việc</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon indigo"><i class="ri-stethoscope-line"></i></div>
        <div>
            <div class="stat-value">{{ $shifts->where('is_for_doctor', true)->count() }}</div>
            <div class="stat-label">Áp dụng bác sĩ</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="ri-user-settings-line"></i></div>
        <div>
            <div class="stat-value">{{ $shifts->where('is_for_employee', true)->count() }}</div>
            <div class="stat-label">Áp dụng nhân viên</div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="table-wrapper">
    <div class="table-toolbar">
        <div class="table-toolbar-title">
            <i class="ri-list-check-2"></i> Danh sách ca làm việc
        </div>
    </div>

    @if($shifts->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Tên ca</th>
                <th>Giờ làm việc</th>
                <th>Áp dụng cho</th>
                <th>Mô tả</th>
                <th>Trạng thái</th>
                <th style="text-align:center;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr>
                <td><span class="shift-name">{{ $shift->name }}</span></td>
                <td>
                    <span class="time-chip">
                        <i class="ri-time-line"></i>
                        {{ str_pad($shift->start_hour,2,'0',STR_PAD_LEFT) }}:{{ str_pad($shift->start_minute,2,'0',STR_PAD_LEFT) }}
                        –
                        {{ str_pad($shift->end_hour,2,'0',STR_PAD_LEFT) }}:{{ str_pad($shift->end_minute,2,'0',STR_PAD_LEFT) }}
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @if($shift->is_for_doctor)
                            <span class="tag tag-doctor"><i class="ri-stethoscope-line"></i> Bác sĩ</span>
                        @endif
                        @if($shift->is_for_employee)
                            <span class="tag tag-employee"><i class="ri-user-settings-line"></i> Nhân viên</span>
                        @endif
                    </div>
                </td>
                <td style="color:#64748b;max-width:220px;">
                    {{ Str::limit($shift->description ?? 'Không có mô tả', 45) }}
                </td>
                <td>
                    @if($shift->is_active)
                        <span class="tag tag-active"><i class="ri-checkbox-circle-line"></i> Hoạt động</span>
                    @else
                        <span class="tag tag-inactive"><i class="ri-close-circle-line"></i> Vô hiệu</span>
                    @endif
                </td>
                <td>
                    <div class="actions" style="justify-content:center;">
                        <button class="btn-icon edit"
                            onclick="openEditModal({{ $shift->id }},'{{ $shift->name }}',{{ $shift->start_hour }},{{ $shift->start_minute }},{{ $shift->end_hour }},{{ $shift->end_minute }},'{{ $shift->description ?? '' }}',{{ $shift->is_for_doctor?'true':'false' }},{{ $shift->is_for_employee?'true':'false' }})">
                            <i class="ri-edit-line"></i> Sửa
                        </button>
                        <form method="POST" action="{{ route('admin.shifts.destroy',$shift->id) }}" style="display:inline;"
                            onsubmit="return confirm('Xóa ca làm việc này?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon delete">
                                <i class="ri-delete-bin-line"></i> Xóa
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <i class="ri-time-line"></i>
        <p>Chưa có ca làm việc nào</p>
        <small>Tạo ca làm việc đầu tiên để bắt đầu quản lý lịch trình</small>
        <div style="margin-top:20px;">
            <button class="btn btn-primary" onclick="openCreateModal()">
                <i class="ri-add-line"></i> Tạo Ca Mới
            </button>
        </div>
    </div>
    @endif
</div>

<!-- Modal -->
<div id="shiftModal" class="modal">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalTitle">Tạo Ca Làm Việc Mới</h3>
            <button class="modal-close-btn" onclick="closeModal()"><i class="ri-close-line"></i></button>
        </div>

        <form id="shiftForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên ca <span class="req">*</span></label>
                    <input type="text" name="name" id="name" class="form-input" required placeholder="VD: Ca Sáng, Ca Chiều...">
                    <div class="err-msg" id="nameError"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giờ bắt đầu <span class="req">*</span></label>
                        <input type="number" name="start_hour" id="start_hour" class="form-input" min="0" max="23" required placeholder="0–23">
                        <div class="err-msg" id="start_hourError"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phút bắt đầu <span class="req">*</span></label>
                        <input type="number" name="start_minute" id="start_minute" class="form-input" min="0" max="59" required placeholder="0–59">
                        <div class="err-msg" id="start_minuteError"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giờ kết thúc <span class="req">*</span></label>
                        <input type="number" name="end_hour" id="end_hour" class="form-input" min="0" max="23" required placeholder="0–23">
                        <div class="err-msg" id="end_hourError"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phút kết thúc <span class="req">*</span></label>
                        <input type="number" name="end_minute" id="end_minute" class="form-input" min="0" max="59" required placeholder="0–59">
                        <div class="err-msg" id="end_minuteError"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" id="description" class="form-input" rows="2" placeholder="Mô tả ca làm việc..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Áp dụng cho <span class="req">*</span></label>
                    <div class="check-group">
                        <label class="check-item">
                            <input type="checkbox" name="is_for_doctor" id="is_for_doctor" value="1">
                            <i class="ri-stethoscope-line" style="color:#6366f1;font-size:16px;"></i>
                            <span>Bác sĩ</span>
                        </label>
                        <label class="check-item">
                            <input type="checkbox" name="is_for_employee" id="is_for_employee" value="1">
                            <i class="ri-user-settings-line" style="color:#14b8a6;font-size:16px;"></i>
                            <span>Nhân viên</span>
                        </label>
                    </div>
                    <div class="err-msg" id="applyError"></div>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-cancel-modal" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn-save"><i class="ri-save-line"></i> Lưu ca làm việc</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Tạo Ca Làm Việc Mới';
        document.getElementById('shiftForm').reset();
        document.getElementById('shiftForm').action = '{{ route("admin.shifts.store") }}';
        document.querySelector('input[name="_method"]')?.remove();
        document.getElementById('shiftModal').classList.add('show');
    }

    function openEditModal(id, name, startHour, startMin, endHour, endMin, desc, isDoctor, isEmployee) {
        document.getElementById('modalTitle').textContent = 'Sửa Ca Làm Việc';
        document.getElementById('name').value = name;
        document.getElementById('start_hour').value = startHour;
        document.getElementById('start_minute').value = startMin;
        document.getElementById('end_hour').value = endHour;
        document.getElementById('end_minute').value = endMin;
        document.getElementById('description').value = desc;
        document.getElementById('is_for_doctor').checked = isDoctor;
        document.getElementById('is_for_employee').checked = isEmployee;

        const form = document.getElementById('shiftForm');
        form.action = `/admin/shifts/${id}`;
        document.querySelector('input[name="_method"]')?.remove();
        const m = document.createElement('input');
        m.type = 'hidden'; m.name = '_method'; m.value = 'PUT';
        form.appendChild(m);

        document.getElementById('shiftModal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('shiftModal').classList.remove('show');
    }

    document.getElementById('shiftModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const alert = document.querySelector('.alert.success');
        if (alert) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => alert.style.display = 'none', 300);
            }, 5000);
        }
    });
</script>
@endsection
