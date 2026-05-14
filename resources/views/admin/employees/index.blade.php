@extends('layouts.admin-layout')

@section('title', 'Quản lý nhân viên')

@section('page-title', 'Nhân viên')
@section('page-subtitle', 'Quản lý thông tin nhân viên phòng khám')

@section('header-actions')
    <button class="btn btn-primary" onclick="openAddModal()">➕ Thêm nhân viên</button>
@endsection

@section('styles')
    <style>
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .select-group {
            display: flex;
            gap: 10px;
        }

        select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            min-width: 200px;
        }

        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .employee-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .employee-card:hover {
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
            border-color: #3b82f6;
        }

        .card-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .card-title-group {
            flex: 1;
        }

        .card-title {
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }

        .card-code {
            font-size: 12px;
            color: #999;
            font-family: monospace;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            font-size: 11px;
            color: #1e40af;
            margin-top: 5px;
            font-weight: 600;
        }

        .card-info {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
            margin-top: 12px;
        }

        .info-item {
            margin: 5px 0;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .status {
            font-size: 12px;
            font-weight: 600;
        }

        .status.active {
            color: #22c55e;
        }

        .status.inactive {
            color: #f59e0b;
        }

        .card-actions {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border: none;
            background: #f0f0f0;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon:hover {
            background: #e0e0e0;
        }

        .btn-icon.delete:hover {
            background: #ef4444;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            width: 90%;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .modal-subtitle {
            font-size: 13px;
            color: #999;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 600;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 5px rgba(59, 130, 246, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* View Modal */
        .view-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .view-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 32px;
        }

        .view-info {
            flex: 1;
        }

        .view-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .view-code {
            font-size: 12px;
            color: #999;
            font-family: monospace;
            margin: 5px 0;
        }

        .view-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .view-item {
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .view-label {
            font-size: 12px;
            color: #999;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .view-value {
            font-size: 14px;
            color: #333;
        }

        .alert-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .alert-dialog.active {
            display: flex;
        }

        .alert-dialog-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 400px;
        }

        .alert-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .alert-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
            }

            .select-group {
                flex-direction: column;
            }

            select {
                min-width: 100%;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .view-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 20px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="filters">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm theo tên nhân viên..." onkeyup="filterEmployees()">
            </div>
            <div class="select-group">
                <select id="positionFilter" onchange="filterEmployees()">
                    <option value="">Tất cả vị trí</option>
                    <option value="Lễ tân">Lễ tân</option>
                    <option value="Điều dưỡng">Điều dưỡng</option>
                    <option value="Kỹ thuật viên">Kỹ thuật viên</option>
                    <option value="Vệ sinh">Vệ sinh</option>
                    <option value="Hành chính">Hành chính</option>
                </select>
                <select id="statusFilter" onchange="filterEmployees()">
                    <option value="">Tất cả</option>
                    <option value="Hoạt động">Hoạt động</option>
                    <option value="Tạm nghỉ">Tạm nghỉ</option>
                </select>
            </div>
        </div>

        <div class="grid" id="employeeGrid">
            @forelse($employees ?? [] as $emp)
                <div class="employee-card" data-name="{{ $emp->name }}" data-position="{{ $emp->position ?? '' }}" data-status="{{ $emp->status ?? 'Hoạt động' }}">
                    <div class="card-header">
                        <div class="avatar">{{ strtoupper(substr(explode(' ', $emp->name)[count(explode(' ', $emp->name))-1], 0, 1)) }}</div>
                        <div class="card-title-group">
                            <div class="card-title">{{ $emp->name }}</div>
                            <div class="card-code">{{ $emp->code }}</div>
                            <span class="badge">{{ $emp->position ?? 'Nhân viên' }}</span>
                        </div>
                    </div>

                    <div class="card-info">
                        <div class="info-item">📞 {{ $emp->phone ?? '---' }}</div>
                        <div class="info-item">✉ {{ $emp->email ?? '---' }}</div>
                        <div class="info-item">🏢 {{ $emp->workplace ?? '---' }}</div>
                    </div>

                    <div class="card-footer">
                        <span class="status {{ $emp->status === 'Hoạt động' ? 'active' : 'inactive' }}">● {{ $emp->status ?? 'Hoạt động' }}</span>
                        <div class="card-actions">
                            <button class="btn-icon" onclick="openViewModal({{ $emp->id }})">👁</button>
                            <button class="btn-icon" onclick="openEditModal({{ $emp->id }})">✏️</button>
                            <button class="btn-icon delete" onclick="openDeleteModal({{ $emp->id }}, '{{ $emp->name }}')">🗑️</button>
                        </div>
                    </div>
                    <form id="deleteForm-{{ $emp->id }}" method="POST" action="{{ route('admin.employee.destroy', $emp->id) }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @empty
                <div style="grid-column: 1/-1;">
                    <div class="empty-state">
                        <p style="font-size: 16px; margin-bottom: 10px;">📭 Chưa có nhân viên nào</p>
                        <p>Hãy thêm nhân viên mới để bắt đầu</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div class="modal" id="formModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Thêm nhân viên mới</div>
                <div class="modal-subtitle">Hồ sơ liên kết với tài khoản người dùng</div>
            </div>

            <form id="employeeForm" method="POST" action="{{ route('admin.employee.store') }}">
                @csrf
                <input type="hidden" name="is_doctor" value="0">
                <input type="hidden" id="formMethod" name="_method" value="POST">
                <input type="hidden" id="editId" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label>Họ tên *</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="form-group">
                        <label>Vị trí công việc *</label>
                        <select name="position" id="position" required>
                            <option value="">-- Chọn vị trí --</option>
                            <option value="Lễ tân">Lễ tân</option>
                            <option value="Điều dưỡng">Điều dưỡng</option>
                            <option value="Kỹ thuật viên">Kỹ thuật viên</option>
                            <option value="Vệ sinh">Vệ sinh</option>
                            <option value="Hành chính">Hành chính</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="dob" id="dob">
                    </div>
                    <div class="form-group">
                        <label>Giới tính</label>
                        <select name="gender" id="gender">
                            <option value="">-- Chọn --</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" id="phone" placeholder="0123456789">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="email" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Địa chỉ thường trú</label>
                        <input type="text" name="address" id="address" placeholder="Địa chỉ">
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Nơi công tác</label>
                        <input type="text" name="workplace" id="workplace" placeholder="Phòng khám chi nhánh...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" id="status">
                            <option value="Hoạt động">Hoạt động</option>
                            <option value="Tạm nghỉ">Tạm nghỉ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tài khoản liên kết</label>
                        <input type="text" name="linkedUser" id="linkedUser" placeholder="-- Tự sinh --" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xem Chi Tiết -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Chi tiết nhân viên</div>
            </div>
            <div id="viewContent"></div>
        </div>
    </div>

    <!-- Alert Dialog Xóa -->
    <div class="alert-dialog" id="deleteDialog">
        <div class="alert-dialog-content">
            <div class="alert-title">Xóa nhân viên?</div>
            <div class="alert-description">Hành động không thể hoàn tác. Xóa <b id="deleteName"></b>?</div>
            <div class="alert-footer">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Hủy</button>
                <button class="btn btn-primary" style="background: #ef4444; border-color: #ef4444;" onclick="confirmDelete()">Xóa</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let currentDeleteId = null;
        let employeesData = {};

        // Load dữ liệu employees
        document.addEventListener('DOMContentLoaded', () => {
            const employees = @json($employees ?? []);
            employees.forEach(emp => {
                employeesData[emp.id] = emp;
            });
        });

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm nhân viên mới';
            document.getElementById('submitBtn').textContent = 'Thêm mới';
            document.getElementById('employeeForm').reset();
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('editId').value = '';
            document.getElementById('employeeForm').action = '{{ route("admin.employee.store") }}';
            document.getElementById('formModal').classList.add('active');
        }

        function openEditModal(id) {
            const emp = employeesData[id];
            if (!emp) {
                alert('Không tìm thấy thông tin nhân viên');
                return;
            }

            document.getElementById('modalTitle').textContent = 'Sửa thông tin nhân viên';
            document.getElementById('submitBtn').textContent = 'Cập nhật';
            document.getElementById('editId').value = id;
            document.getElementById('formMethod').value = 'PATCH';
            
            document.getElementById('name').value = emp.name;
            document.getElementById('position').value = emp.position || '';
            document.getElementById('phone').value = emp.phone || '';
            document.getElementById('email').value = emp.email || '';
            document.getElementById('address').value = emp.address || '';
            document.getElementById('dob').value = emp.dob || '';
            document.getElementById('gender').value = emp.gender || '';
            document.getElementById('workplace').value = emp.workplace || '';
            document.getElementById('status').value = emp.status || 'Hoạt động';
            document.getElementById('linkedUser').value = emp.linkedUser || '';

            document.getElementById('employeeForm').action = `{{ route('admin.employee.update', ':id') }}`.replace(':id', id);
            document.getElementById('formModal').classList.add('active');
        }

        function openViewModal(id) {
            const emp = employeesData[id];
            if (!emp) {
                alert('Không tìm thấy thông tin nhân viên');
                return;
            }

            const lastChar = emp.name.split(' ').pop().charAt(0).toUpperCase();
            const html = `
                <div class="view-header">
                    <div class="view-avatar">${lastChar}</div>
                    <div class="view-info">
                        <div class="view-name">${emp.name}</div>
                        <div class="view-code">${emp.code}</div>
                        <span class="badge">${emp.position || 'Nhân viên'}</span>
                    </div>
                </div>
                <div class="view-grid">
                    <div class="view-item">
                        <div class="view-label">Ngày sinh</div>
                        <div class="view-value">${emp.dob || '---'}</div>
                    </div>
                    <div class="view-item">
                        <div class="view-label">Giới tính</div>
                        <div class="view-value">${emp.gender || '---'}</div>
                    </div>
                    <div class="view-item">
                        <div class="view-label">Điện thoại</div>
                        <div class="view-value">${emp.phone || '---'}</div>
                    </div>
                    <div class="view-item">
                        <div class="view-label">Email</div>
                        <div class="view-value">${emp.email || '---'}</div>
                    </div>
                    <div class="view-item" style="grid-column: 1/-1;">
                        <div class="view-label">Địa chỉ</div>
                        <div class="view-value">${emp.address || '---'}</div>
                    </div>
                    <div class="view-item" style="grid-column: 1/-1;">
                        <div class="view-label">Nơi công tác</div>
                        <div class="view-value">${emp.workplace || '---'}</div>
                    </div>
                    <div class="view-item">
                        <div class="view-label">Trạng thái</div>
                        <div class="view-value">${emp.status || 'Hoạt động'}</div>
                    </div>
                </div>
            `;
            document.getElementById('viewContent').innerHTML = html;
            document.getElementById('viewModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('active');
        }

        function openDeleteModal(id, name) {
            currentDeleteId = id;
            document.getElementById('deleteName').textContent = name;
            document.getElementById('deleteDialog').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteDialog').classList.remove('active');
        }

        function confirmDelete() {
            if (currentDeleteId) {
                document.getElementById('deleteForm-' + currentDeleteId).submit();
            }
        }

        function filterEmployees() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const position = document.getElementById('positionFilter').value;
            const status = document.getElementById('statusFilter').value;
            const cards = document.querySelectorAll('.employee-card');

            cards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const cardPosition = card.dataset.position;
                const cardStatus = card.dataset.status;
                const match = name.includes(search) 
                    && (!position || cardPosition.includes(position))
                    && (!status || cardStatus.includes(status));
                card.style.display = match ? '' : 'none';
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.getElementById('formModal').classList.remove('active');
                document.getElementById('viewModal').classList.remove('active');
                document.getElementById('deleteDialog').classList.remove('active');
            }
        });

        document.getElementById('formModal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) closeModal();
        });

        document.getElementById('viewModal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) document.getElementById('viewModal').classList.remove('active');
        });

        document.getElementById('deleteDialog')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) closeDeleteModal();
        });
    </script>
@endsection