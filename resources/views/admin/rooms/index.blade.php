@extends('layouts.admin-layout')

@section('title', 'Quản lý phòng khám')
@section('page-title', 'Quản lý phòng khám')
@section('page-subtitle', 'Tạo và quản lý phòng theo từng loại dịch vụ')

@section('header-actions')
<button class="btn btn-primary" onclick="openAddModal()">
    <i class="ri-add-line"></i> Thêm phòng
</button>
@endsection

@section('styles')
<style>
    .table-box {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        text-align: left;
        padding: 14px;
        border-bottom: 1px solid var(--border-color);
    }

    td {
        padding: 14px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .badge {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #e0f2fe;
        color: #0369a1;
    }

    .badge-ok {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-maintenance {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-off {
        background: #fee2e2;
        color: #991b1b;
    }

    .actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .icon-btn {
        width: 34px;
        height: 34px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 8px;
        cursor: pointer;
    }

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 14px;
        padding: 26px;
        width: min(620px, 92vw);
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-title {
        font-family: var(--font-title);
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 18px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .form-group {
        margin-bottom: 14px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
        color: #334155;
    }

    input, select, textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        font-family: inherit;
        font-size: 14px;
    }

    textarea {
        min-height: 80px;
        resize: vertical;
    }

    .check-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .check-row input {
        width: auto;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }

    @media (max-width: 760px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@if($errors->any())
    <div class="alert error">
        <i class="ri-error-warning-line"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã phòng</th>
                <th>Tên phòng</th>
                <th>Loại dịch vụ</th>
                <th>Vị trí</th>
                <th>Sức chứa</th>
                <th>Trạng thái</th>
                <th style="text-align:right;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td><strong>{{ $room->code }}</strong></td>
                    <td>{{ $room->name }}</td>
                    <td><span class="badge">{{ $room->type }}</span></td>
                    <td>{{ trim(($room->floor ? $room->floor . ' - ' : '') . ($room->location ?? '')) ?: 'N/A' }}</td>
                    <td>{{ $room->capacity }}</td>
                    <td>
                        @if(!$room->is_active)
                            <span class="badge badge-off">Ngừng hoạt động</span>
                        @elseif($room->base_status === 'maintenance')
                            <span class="badge badge-maintenance">Bảo trì</span>
                        @else
                            <span class="badge badge-ok">Hoạt động</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <button class="icon-btn" type="button"
                                onclick="openEditModal(
                                    {{ $room->id }},
                                    '{{ addslashes($room->code) }}',
                                    '{{ addslashes($room->name) }}',
                                    '{{ $room->type }}',
                                    '{{ addslashes($room->floor ?? '') }}',
                                    '{{ addslashes($room->location ?? '') }}',
                                    {{ $room->capacity }},
                                    '{{ $room->base_status }}',
                                    {{ $room->is_active ? 'true' : 'false' }},
                                    '{{ addslashes($room->description ?? '') }}'
                                )">
                                <i class="ri-edit-line"></i>
                            </button>

                            <form method="POST" action="{{ route('admin.rooms.destroy', $room->id) }}"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa phòng này không?');">
                                @csrf
                                @method('DELETE')
                                <button class="icon-btn" type="submit">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#64748b;padding:32px;">
                        Chưa có phòng khám nào.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal" id="roomModal">
    <div class="modal-content">
        <div class="modal-title" id="modalTitle">Thêm phòng khám</div>

        <form id="roomForm" method="POST" action="{{ route('admin.rooms.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="form-grid">
                <div class="form-group">
                    <label>Mã phòng *</label>
                    <input type="text" name="code" id="code" placeholder="VD: PK001" required>
                </div>

                <div class="form-group">
                    <label>Tên phòng *</label>
                    <input type="text" name="name" id="name" placeholder="VD: Phòng khám 1" required>
                </div>

                <div class="form-group">
                    <label>Loại dịch vụ *</label>
                    <select name="type" id="type" required>
                        <option value="">-- Chọn loại --</option>
                        <option value="Khám">Khám</option>
                        <option value="Điều trị">Điều trị</option>
                        <option value="Thẩm mỹ">Thẩm mỹ</option>
                        <option value="Phẫu thuật">Phẫu thuật</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sức chứa *</label>
                    <input type="number" name="capacity" id="capacity" min="1" max="10" value="1" required>
                </div>

                <div class="form-group">
                    <label>Tầng</label>
                    <input type="text" name="floor" id="floor" placeholder="VD: Tầng 1">
                </div>

                <div class="form-group">
                    <label>Vị trí</label>
                    <input type="text" name="location" id="location" placeholder="VD: Khu A">
                </div>

                <div class="form-group">
                    <label>Trạng thái nền *</label>
                    <select name="base_status" id="base_status" required>
                        <option value="available">Hoạt động</option>
                        <option value="maintenance">Bảo trì</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Hiển thị</label>
                    <div class="check-row">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <span>Đang sử dụng trong hệ thống</span>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Mô tả</label>
                    <textarea name="description" id="description" placeholder="Ghi chú thiết bị, chức năng phòng..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Thêm phòng</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const roomModal = document.getElementById('roomModal');
    const roomForm = document.getElementById('roomForm');

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Thêm phòng khám';
        document.getElementById('submitBtn').textContent = 'Thêm phòng';
        document.getElementById('formMethod').value = 'POST';

        roomForm.action = '{{ route("admin.rooms.store") }}';
        roomForm.reset();

        document.getElementById('capacity').value = 1;
        document.getElementById('base_status').value = 'available';
        document.getElementById('is_active').checked = true;

        roomModal.classList.add('active');
    }

    function openEditModal(id, code, name, type, floor, location, capacity, baseStatus, isActive, description) {
        document.getElementById('modalTitle').textContent = 'Sửa phòng khám';
        document.getElementById('submitBtn').textContent = 'Cập nhật';
        document.getElementById('formMethod').value = 'PUT';

        roomForm.action = '{{ route("admin.rooms.update", ":id") }}'.replace(':id', id);

        document.getElementById('code').value = code;
        document.getElementById('name').value = name;
        document.getElementById('type').value = type;
        document.getElementById('floor').value = floor;
        document.getElementById('location').value = location;
        document.getElementById('capacity').value = capacity;
        document.getElementById('base_status').value = baseStatus;
        document.getElementById('is_active').checked = isActive;
        document.getElementById('description').value = description;

        roomModal.classList.add('active');
    }

    function closeModal() {
        roomModal.classList.remove('active');
    }

    roomModal.addEventListener('click', function(event) {
        if (event.target === roomModal) {
            closeModal();
        }
    });
</script>
@endsection