@extends('layouts.admin-layout')

@section('title', 'Thiết lập Giá Dịch vụ')

@section('page-title', 'Bảng Giá')
@section('page-subtitle', 'Quản lý bảng giá các dịch vụ')

@section('header-actions')
    <button class="btn btn-primary" onclick="openAddModal()">
        <span style="font-size: 16px; margin-right: 5px;">➕</span>Thêm giá mới
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

        .price-value {
            font-weight: bold;
            color: #667eea;
            font-size: 14px;
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
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
            .form-row {
                grid-template-columns: 1fr;
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
        <!-- Search -->
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm giá...">
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table id="pricesTable">
                <thead>
                    <tr>
                        <th>Tên Dịch Vụ</th>
                        <th>Đơn Giá</th>
                        <th>Ngày Áp Dụng</th>
                        <th style="text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prices as $price)
                        <tr class="price-row" data-service="{{ strtolower($price->service->name) }}">
                            <td>{{ $price->service->name }}</td>
                            <td>
                                <span class="price-value">{{ number_format($price->price, 0, ',', '.') }} ₫</span>
                            </td>
                            <td>{{ $price->applied_date ? $price->applied_date->format('d/m/Y') : '---' }}</td>
                            <td>
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="action-btn" type="button" onclick="openEditModal({{ $price->id }}, {{ $price->service_id }}, {{ $price->price }}, '{{ $price->applied_date ? $price->applied_date->format('Y-m-d') : '' }}')">✏️</button>
                                    <button class="action-btn delete" type="button" onclick="confirmDelete({{ $price->id }})">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
                                    <p>Chưa có giá nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm/Sửa Giá -->
    <div class="modal" id="formModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Thêm giá dịch vụ mới</div>
                <div class="modal-subtitle">Thiết lập giá cho dịch vụ</div>
            </div>

            <form id="priceForm" method="POST">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="form-group">
                    <label>Dịch vụ *</label>
                    <select name="service_id" id="service_id" required>
                        <option value="">-- Chọn dịch vụ --</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Giá dịch vụ (VNĐ) *</label>
                    <input type="number" name="price" id="price" placeholder="0" required min="1">
                </div>

                <div class="form-group">
                    <label>Ngày áp dụng</label>
                    <input type="date" name="applied_date" id="applied_date">
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

        searchInput?.addEventListener('input', filterTable);

        function filterTable() {
            const search = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.price-row');

            rows.forEach(row => {
                const service = (row.dataset.service || '').toLowerCase();
                const matchSearch = !search || service.includes(search);
                row.style.display = matchSearch ? '' : 'none';
            });
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm giá dịch vụ mới';
            document.getElementById('priceForm').reset();
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('service_id').disabled = false;
            document.getElementById('submitBtn').textContent = 'Thêm mới';
            document.getElementById('priceForm').action = '{{ route("admin.prices.store") }}';
            document.getElementById('formModal').classList.add('active');
        }

        function openEditModal(priceId, serviceId, price, appliedDate) {
            document.getElementById('modalTitle').textContent = 'Cập nhật giá dịch vụ';
            document.getElementById('service_id').value = serviceId;
            document.getElementById('price').value = price;
            document.getElementById('applied_date').value = appliedDate;
            document.getElementById('formMethod').value = 'PATCH';
            document.getElementById('submitBtn').textContent = 'Cập nhật';
            document.getElementById('priceForm').action = '{{ route("admin.prices.update", ":id") }}'.replace(':id', priceId);
            document.getElementById('formModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('active');
        }

        function confirmDelete(id) {
            if (confirm('Bạn có chắc chắn muốn xóa giá này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.prices.destroy", ":id") }}'.replace(':id', id);
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        }

        document.getElementById('formModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'formModal') closeModal();
        });
    </script>
@endsection