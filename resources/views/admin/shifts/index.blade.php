@extends('layouts.admin-layout')

@section('title', 'Quản Lý Ca Làm Việc')

@section('page-title', 'Quản Lý Ca Làm Việc')

@section('page-subtitle', 'Tạo, chỉnh sửa và xóa các ca làm việc cho bác sĩ và nhân viên')

@section('header-actions')
    <button class="btn btn-primary" onclick="openCreateModal()">
        ➕ Thêm Ca Mới
    </button>
@endsection

@section('styles')
<style>
    .table-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    thead th {
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.2s ease;
    }

    tbody tr:hover {
        background: #f9fafb;
    }

    tbody td {
        padding: 16px;
        font-size: 14px;
        color: #333;
    }

    .shift-name {
        font-weight: 600;
        color: #667eea;
    }

    .time-range {
        background: #f0f4ff;
        padding: 6px 12px;
        border-radius: 6px;
        color: #667eea;
        font-family: 'Courier New', monospace;
        font-weight: 500;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-doctor {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-employee {
        background: #e9d5ff;
        color: #6b21a8;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .badge-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-small {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-edit:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 60px 30px;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .empty-state-text {
        color: #6b7280;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 8px;
    }

    .stat-label {
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
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
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h2 {
        font-size: 20px;
        color: #333;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        color: #333;
    }

    .modal-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .checkbox-group {
        display: flex;
        gap: 16px;
        margin-top: 8px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-item label {
        cursor: pointer;
        font-size: 14px;
        color: #333;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn-submit {
        padding: 10px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-cancel {
        padding: 10px 24px;
        background: #e5e7eb;
        color: #333;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
    }

    .btn-cancel:hover {
        background: #d1d5db;
    }

    .error-message {
        color: #dc2626;
        font-size: 13px;
        margin-top: 6px;
    }

    @media (max-width: 768px) {
        table {
            font-size: 12px;
        }

        thead th,
        tbody td {
            padding: 12px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-small {
            width: 100%;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .modal-content {
            width: 95%;
        }
    }
</style>
@endsection

@section('content')
<div class="card">
    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-value">{{ $shifts->count() }}</div>
            <div class="stat-label">Tổng Ca Làm Việc</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $shifts->where('is_for_doctor', true)->count() }}</div>
            <div class="stat-label">Áp Dụng Bác Sĩ</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $shifts->where('is_for_employee', true)->count() }}</div>
            <div class="stat-label">Áp Dụng Nhân Viên</div>
        </div>
    </div>

    <!-- Table -->
    @if($shifts->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Tên Ca</th>
                        <th style="width: 15%;">Giờ Làm Việc</th>
                        <th style="width: 20%;">Áp Dụng Cho</th>
                        <th style="width: 30%;">Mô Tả</th>
                        <th style="width: 10%;">Trạng Thái</th>
                        <th style="width: 10%; text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                    <tr>
                        <td>
                            <span class="shift-name">{{ $shift->name }}</span>
                        </td>
                        <td>
                            <span class="time-range">
                                {{ str_pad($shift->start_hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($shift->start_minute, 2, '0', STR_PAD_LEFT) }} 
                                - 
                                {{ str_pad($shift->end_hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($shift->end_minute, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if($shift->is_for_doctor)
                                    <span class="badge badge-doctor">🩺 Bác Sĩ</span>
                                @endif
                                @if($shift->is_for_employee)
                                    <span class="badge badge-employee">👨‍💼 Nhân Viên</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span title="{{ $shift->description ?? 'Không có mô tả' }}" style="color: #6b7280;">
                                {{ Str::limit($shift->description ?? 'Không có mô tả', 40) }}
                            </span>
                        </td>
                        <td>
                            @if($shift->is_active)
                                <span class="badge badge-active">✓ Hoạt Động</span>
                            @else
                                <span class="badge badge-inactive">✕ Vô Hiệu</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-small btn-edit" onclick="openEditModal({{ $shift->id }}, '{{ $shift->name }}', {{ $shift->start_hour }}, {{ $shift->start_minute }}, {{ $shift->end_hour }}, {{ $shift->end_minute }}, '{{ $shift->description ?? '' }}', {{ $shift->is_for_doctor ? 'true' : 'false' }}, {{ $shift->is_for_employee ? 'true' : 'false' }})">
                                    ✎ Sửa
                                </button>
                                <form method="POST" action="{{ route('admin.shifts.destroy', $shift->id) }}" style="display: inline;" onsubmit="return confirm('⚠️ Xóa ca làm việc này? Hành động này không thể hoàn tác.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-small btn-delete">
                                        🗑️ Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">⏰</div>
            <p class="empty-state-text">Chưa có ca làm việc nào</p>
            <p style="color: #999; font-size: 14px; margin-bottom: 20px;">Tạo ca làm việc đầu tiên để bắt đầu quản lý lịch trình</p>
            <button class="btn btn-primary" onclick="openCreateModal()">
                ➕ Tạo Ca Làm Việc Mới
            </button>
        </div>
    @endif
</div>

<!-- Modal Tạo/Sửa Ca Làm Việc -->
<div id="shiftModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tạo Ca Làm Việc Mới</h2>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>

        <form id="shiftForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Ca <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required placeholder="VD: Sáng, Chiều, Tối...">
                    <div class="error-message" id="nameError"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giờ Bắt Đầu <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="start_hour" id="start_hour" class="form-control" min="0" max="23" required placeholder="0-23">
                        <div class="error-message" id="start_hourError"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phút Bắt Đầu <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="start_minute" id="start_minute" class="form-control" min="0" max="59" required placeholder="0-59">
                        <div class="error-message" id="start_minuteError"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giờ Kết Thúc <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="end_hour" id="end_hour" class="form-control" min="0" max="23" required placeholder="0-23">
                        <div class="error-message" id="end_hourError"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phút Kết Thúc <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="end_minute" id="end_minute" class="form-control" min="0" max="59" required placeholder="0-59">
                        <div class="error-message" id="end_minuteError"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô Tả</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Nhập mô tả..."></textarea>
                </div>

                <div class="form-group">
    <label class="form-label">Áp Dụng Cho <span style="color: #ef4444;">*</span></label>
    <div class="checkbox-group">
        <div class="checkbox-item">
            <input type="checkbox" name="is_for_doctor" id="is_for_doctor" value="1">
            <label for="is_for_doctor">🩺 Bác Sĩ</label>
        </div>
        <div class="checkbox-item">
            <input type="checkbox" name="is_for_employee" id="is_for_employee" value="1">
            <label for="is_for_employee">👨‍💼 Nhân Viên</label>
        </div>
    </div>
    <div class="error-message" id="applyError"></div>
</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn-submit">Lưu Ca Làm Việc</button>
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
        document.getElementById('shiftForm').method = 'POST';
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
        
        // Xóa method cũ nếu có
        document.querySelector('input[name="_method"]')?.remove();
        
        // Thêm method PUT
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);
        
        document.getElementById('shiftModal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('shiftModal').classList.remove('show');
    }

    // Đóng modal khi click ngoài
    document.getElementById('shiftModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Auto-hide success message
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.querySelector('.alert.success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    successAlert.style.display = 'none';
                }, 300);
            }, 5000);
        }
    });
</script>
@endsection