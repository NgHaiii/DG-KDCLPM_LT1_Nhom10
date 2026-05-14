@extends('layouts.admin-layout')

@section('title', 'Quản lý Dịch vụ')

@section('page-title', 'Danh mục dịch vụ')
@section('page-subtitle', 'Quản lý dịch vụ nha khoa cung cấp')

@section('header-actions')
    <button class="btn btn-primary" onclick="openAddModal()">
        <span style="font-size: 16px; margin-right: 5px;">➕</span>Thêm dịch vụ
    </button>
@endsection

@section('styles')
    <style>
        .search-filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-box::before {
            content: "🔍";
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            min-width: 200px;
            cursor: pointer;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .service-icon {
            width: 36px;
            height: 36px;
            background: #dbeafe;
            color: #2563eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 10px;
        }

        .service-cell {
            display: flex;
            align-items: center;
        }

        .service-name {
            font-weight: 500;
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .status-active {
            color: #059669;
        }

        .status-inactive {
            color: #999;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #f3f4f6;
        }

        .action-btn.delete:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Modal Styles */
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
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .modal-subtitle {
            font-size: 14px;
            color: #666;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: #f3f4f6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left-color: #10b981;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-left-color: #ef4444;
        }

        @media (max-width: 768px) {
            .search-filter-container {
                flex-direction: column;
            }

            .filter-select {
                width: 100%;
            }

            .hidden-mobile {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <p>❌ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {!! session('success') !!}
        </div>
    @endif

    <div class="card">
        <!-- Search & Filter -->
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm dịch vụ...">
            </div>
            <select class="filter-select" id="typeFilter">
                <option value="">Tất cả loại</option>
                <option value="khám">Khám</option>
                <option value="điều trị">Điều trị</option>
                <option value="thẩm mỹ">Thẩm mỹ</option>
                <option value="phẫu thuật">Phẫu thuật</option>
                <option value="chỉnh nha">Chỉnh nha</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table id="servicesTable">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Dịch vụ</th>
                        <th>Loại</th>
                        <th class="hidden-mobile">Mô tả</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr class="service-row" data-name="{{ strtolower($service->name) }}" data-type="{{ strtolower($service->type ?? '') }}">
                            <td><span style="font-family: monospace; font-size: 12px; color: #666;">{{ $service->id ?? 'SV' . str_pad($service->id, 3, '0', STR_PAD_LEFT) }}</span></td>
                            <td>
                                <div class="service-cell">
                                    <div class="service-icon">🦷</div>
                                    <span class="service-name">{{ $service->name }}</span>
                                </div>
                            </td>
                            <td><span class="badge">{{ $service->type ?? 'Khác' }}</span></td>
                            <td class="hidden-mobile" style="font-size: 13px; color: #666;">{{ Str::limit($service->description ?? 'Không có mô tả', 50) }}</td>
                            <td>
                                <span class="status-active">● Đang cung cấp</span>
                            </td>
                            <td>
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="action-btn" type="button" onclick="openEditModal({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ addslashes($service->description ?? '') }}', '{{ $service->type ?? '' }}')">✏️</button>
                                    <button class="action-btn delete" type="button" onclick="confirmDelete({{ $service->id }})">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📭</div>
                                    <p>Không có dữ liệu</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div class="modal" id="formModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Thêm dịch vụ mới</div>
                <div class="modal-subtitle">Thông tin dịch vụ hiển thị trong bảng giá</div>
            </div>

            <form id="serviceForm" method="POST">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="form-row">
                    <div class="form-group">
                        <label>Mã dịch vụ</label>
                        <input type="text" id="serviceId" placeholder="DV001" disabled>
                    </div>
                    <div class="form-group">
                        <label>Loại dịch vụ</label>
                        <select name="type" id="type" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="Khám">Khám</option>
                            <option value="Điều trị">Điều trị</option>
                            <option value="Thẩm mỹ">Thẩm mỹ</option>
                            <option value="Phẫu thuật">Phẫu thuật</option>
                            <option value="Chỉnh nha">Chỉnh nha</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tên dịch vụ</label>
                    <input type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" id="description"></textarea>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status" id="status">
                        <option value="active">Đang cung cấp</option>
                        <option value="inactive">Tạm dừng</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');

        // Thêm event listeners
        searchInput?.addEventListener('input', filterTable);
        typeFilter?.addEventListener('change', filterTable);

        function filterTable() {
    const search = searchInput.value.toLowerCase().trim();
    const type = typeFilter.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.service-row');

    console.log('Search:', search, 'Type:', type); // Debug

    rows.forEach(row => {
        const name = (row.dataset.name || '').toLowerCase();
        const rowType = (row.dataset.type || '').toLowerCase().trim();
        
        const matchSearch = !search || name.includes(search);
        const matchType = !type || rowType.includes(type);

        console.log('Row:', name, 'RowType:', rowType, 'Match:', matchSearch && matchType);

        row.style.display = (matchSearch && matchType) ? '' : 'none';
    });
}

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm dịch vụ mới';
            document.getElementById('serviceForm').reset();
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('type').value = '';
            document.getElementById('serviceId').disabled = true;
            document.getElementById('submitBtn').textContent = 'Thêm mới';
            document.getElementById('serviceForm').action = '{{ route("admin.services.store") }}';
            document.getElementById('formModal').classList.add('active');
        }

        function openEditModal(id, name, desc, type) {
            document.getElementById('modalTitle').textContent = 'Sửa dịch vụ';
            document.getElementById('serviceId').value = 'DV' + String(id).padStart(3, '0');
            document.getElementById('name').value = name;
            document.getElementById('description').value = desc;
            document.getElementById('type').value = type;
            document.getElementById('formMethod').value = 'PATCH';
            document.getElementById('submitBtn').textContent = 'Cập nhật';
            document.getElementById('serviceForm').action = '{{ route("admin.services.update", ":id") }}'.replace(':id', id);
            document.getElementById('formModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('active');
        }

        function confirmDelete(id) {
            if (confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.services.destroy", ":id") }}'.replace(':id', id);
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Đóng modal khi click bên ngoài
        document.getElementById('formModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'formModal') closeModal();
        });
    </script>
@endsection