@extends('layouts.admin-layout')

@section('title', 'Quản lý Dịch vụ')

@section('page-title', 'Danh mục dịch vụ')
@section('page-subtitle', 'Quản lý dịch vụ nha khoa, thời lượng khám và phòng thực hiện dịch vụ')

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
            min-width: 190px;
            cursor: pointer;
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
            white-space: nowrap;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .service-cell {
            display: flex;
            align-items: center;
            min-width: 190px;
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
            flex-shrink: 0;
        }

        .service-name {
            font-weight: 600;
            color: #111827;
        }

        .service-description {
            font-size: 13px;
            color: #6b7280;
            max-width: 230px;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            white-space: nowrap;
        }

        .badge-type {
            background: #eef2ff;
            color: #4338ca;
            border-color: #c7d2fe;
        }

        .badge-slots {
            background: #dbeafe;
            color: #2563eb;
            border-color: #93c5fd;
        }

        .room-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 8px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 12px;
            font-weight: 600;
            max-width: 210px;
        }

        .room-badge span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .room-missing {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 8px;
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            color: #059669;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-inactive {
            color: #6b7280;
            font-weight: 600;
            white-space: nowrap;
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
            transition: all 0.2s;
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
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 28px;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 5px;
        }

        .modal-subtitle {
            font-size: 14px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.16);
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

        .help-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            line-height: 1.4;
        }

        .help-text.warning {
            color: #c2410c;
        }

        .preview-box {
            background: #f0f9ff;
            border: 1px solid #7dd3fc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .preview-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            color: #1e40af;
        }

        .preview-item:last-child {
            margin-bottom: 0;
            border-top: 1px solid #7dd3fc;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 700;
            color: #0284c7;
            font-size: 14px;
        }

        .preview-item strong {
            color: #2563eb;
            font-weight: 700;
            text-align: right;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
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
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #f3f4f6;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
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

        @media (max-width: 1100px) {
            .hidden-tablet {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .search-filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                width: 100%;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .hidden-mobile {
                display: none;
            }

            .modal-content {
                padding: 22px;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $servicesData = $services->mapWithKeys(function ($service) {
            return [
                $service->id => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'type' => $service->type,
                    'room_id' => $service->room_id,
                    'is_active' => (bool) $service->is_active,
                    'slots_required' => $service->slots_required,
                    'duration_minutes' => $service->duration_minutes,
                ],
            ];
        });
    @endphp

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Có lỗi xảy ra:</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm dịch vụ...">
            </div>

            <select class="filter-select" id="typeFilter">
                <option value="">Tất cả loại dịch vụ</option>
                <option value="khám">Khám</option>
                <option value="điều trị">Điều trị</option>
                <option value="thẩm mỹ">Thẩm mỹ</option>
                <option value="phẫu thuật">Phẫu thuật</option>
            </select>

            <select class="filter-select" id="roomFilter">
                <option value="">Tất cả phòng</option>
                <option value="assigned">Đã gán phòng</option>
                <option value="missing">Chưa gán phòng</option>
            </select>
        </div>

        <div class="table-container">
            <table id="servicesTable">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Dịch vụ</th>
                        <th>Loại</th>
                        <th>Phòng khám</th>
                        <th class="hidden-tablet">Mô tả</th>
                        <th>Slots</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr class="service-row"
                            data-name="{{ strtolower($service->name) }}"
                            data-type="{{ strtolower($service->type ?? '') }}"
                            data-room="{{ $service->room_id ? 'assigned' : 'missing' }}">
                            <td>
                                <span style="font-family: monospace; font-size: 12px; color: #6b7280;">
                                    DV{{ str_pad($service->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
                                <div class="service-cell">
                                    <div class="service-icon">🦷</div>
                                    <span class="service-name">{{ $service->name }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-type">{{ $service->type ?? 'Chưa phân loại' }}</span>
                            </td>

                            <td>
                                @if($service->room)
                                    <div class="room-badge" title="{{ $service->room->name }}">
                                        <span>🏥 {{ $service->room->name }}</span>
                                    </div>
                                    <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                        {{ $service->room->code ?? 'Không có mã phòng' }}
                                    </div>
                                @else
                                    <span class="room-missing">⚠ Chưa gán phòng</span>
                                @endif
                            </td>

                            <td class="hidden-tablet">
                                <div class="service-description">
                                    {{ \Illuminate\Support\Str::limit($service->description ?? 'Không có mô tả', 60) }}
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-slots">
                                    {{ $service->slots_required ?? 1 }} slot{{ ($service->slots_required ?? 1) > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td>
                                <span style="color: #2563eb; font-weight: 700;">
                                    {{ $service->actual_duration ?? (($service->slots_required ?? 1) * ($service->duration_minutes ?? 30)) }}p
                                </span>
                            </td>

                            <td>
                                @if($service->is_active)
                                    <span class="status-active">● Đang cung cấp</span>
                                @else
                                    <span class="status-inactive">● Tạm dừng</span>
                                @endif
                            </td>

                            <td>
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="action-btn" type="button" onclick="openEditModal({{ $service->id }})" title="Sửa dịch vụ">✏️</button>
                                    <button class="action-btn delete" type="button" onclick="confirmDelete({{ $service->id }})" title="Xóa dịch vụ">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📭</div>
                                    <p>Chưa có dịch vụ nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal" id="formModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Thêm dịch vụ mới</div>
                <div class="modal-subtitle">
                    Gán phòng theo đúng loại dịch vụ để khi bác sĩ xác nhận lịch online, hệ thống có thể trả phòng khám cho bệnh nhân.
                </div>
            </div>

            <div id="modalErrors" style="display: none;">
                <div class="alert alert-error" id="errorList"></div>
            </div>

            <form id="serviceForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="form-row">
                    <div class="form-group">
                        <label>Mã dịch vụ</label>
                        <input type="text" id="serviceId" placeholder="Tự động" disabled>
                    </div>

                    <div class="form-group">
                        <label>Loại dịch vụ <span style="color: #dc2626;">*</span></label>
                        <select name="type" id="type" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="Khám">Khám</option>
                            <option value="Điều trị">Điều trị</option>
                            <option value="Thẩm mỹ">Thẩm mỹ</option>
                            <option value="Phẫu thuật">Phẫu thuật</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tên dịch vụ <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="name" id="name" required placeholder="VD: Khám chân răng">
                </div>

                <div class="form-group">
                    <label>Phòng khám thực hiện dịch vụ</label>
                    <select name="room_id" id="room_id">
                        <option value="">-- Chọn loại dịch vụ trước --</option>
                        @foreach(($rooms ?? []) as $room)
                            <option value="{{ $room->id }}" data-type="{{ $room->type }}">
                                {{ $room->name }}{{ $room->code ? ' - ' . $room->code : '' }} | {{ $room->type }}
                            </option>
                        @endforeach
                    </select>
                    <span class="help-text" id="roomHelp">
                        Chỉ hiển thị phòng đang hoạt động và có cùng loại với dịch vụ.
                    </span>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" id="description" placeholder="Mô tả chi tiết về dịch vụ..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số slot <span style="color: #dc2626;">*</span></label>
                        <select name="slots_required" id="slots_required" required>
                            <option value="">-- Chọn số slot --</option>
                            <option value="1">1 slot</option>
                            <option value="2">2 slots</option>
                            <option value="3">3 slots</option>
                            <option value="4">4 slots</option>
                            <option value="5">5 slots</option>
                            <option value="6">6 slots</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Thời gian/slot, phút <span style="color: #dc2626;">*</span></label>
                        <input type="number"
                               name="duration_minutes"
                               id="duration_minutes"
                               min="30"
                               max="300"
                               step="1"
                               value="30"
                               required
                               placeholder="VD: 30, 45, 60, 90">
                        <span class="help-text">Tổng thời gian = số slot × thời gian mỗi slot.</span>
                    </div>
                </div>

                <div id="previewBox" class="preview-box" style="display: none;">
                    <div class="preview-item">
                        <span>Số slot:</span>
                        <strong id="previewSlots">-</strong>
                    </div>
                    <div class="preview-item">
                        <span>Thời gian/slot:</span>
                        <strong><span id="previewDuration">-</span> phút</strong>
                    </div>
                    <div class="preview-item">
                        <span>Tổng thời gian:</span>
                        <strong id="previewTotal">-</strong>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_active" id="is_active" value="1">
                        <label for="is_active">Đang cung cấp dịch vụ</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const servicesData = @json($servicesData);

        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const roomFilter = document.getElementById('roomFilter');
        const serviceForm = document.getElementById('serviceForm');
        const typeSelect = document.getElementById('type');
        const roomSelect = document.getElementById('room_id');
        const roomHelp = document.getElementById('roomHelp');
        const slotsSelect = document.getElementById('slots_required');
        const durationInput = document.getElementById('duration_minutes');

        searchInput?.addEventListener('input', filterTable);
        typeFilter?.addEventListener('change', filterTable);
        roomFilter?.addEventListener('change', filterTable);

        typeSelect?.addEventListener('change', function () {
            filterRoomsByType();
            roomSelect.value = '';
        });

        slotsSelect?.addEventListener('change', updatePreview);
        durationInput?.addEventListener('input', updatePreview);

        function filterTable() {
            const search = searchInput.value.toLowerCase().trim();
            const type = typeFilter.value.toLowerCase().trim();
            const roomStatus = roomFilter.value.trim();
            const rows = document.querySelectorAll('.service-row');

            rows.forEach(row => {
                const name = (row.dataset.name || '').toLowerCase();
                const rowType = (row.dataset.type || '').toLowerCase().trim();
                const rowRoom = row.dataset.room || '';

                const matchSearch = !search || name.includes(search);
                const matchType = !type || rowType === type;
                const matchRoom = !roomStatus || rowRoom === roomStatus;

                row.style.display = (matchSearch && matchType && matchRoom) ? '' : 'none';
            });
        }

        function filterRoomsByType(selectedRoomId = '') {
            const selectedType = typeSelect.value;
            let visibleCount = 0;

            Array.from(roomSelect.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    option.textContent = selectedType
                        ? '-- Chọn phòng cho loại ' + selectedType + ' --'
                        : '-- Chọn loại dịch vụ trước --';
                    return;
                }

                const roomType = option.dataset.type || '';
                const isMatch = selectedType && roomType === selectedType;

                option.hidden = !isMatch;
                option.disabled = !isMatch;

                if (isMatch) {
                    visibleCount++;
                }
            });

            if (selectedRoomId) {
                roomSelect.value = String(selectedRoomId);
            }

            if (!selectedType) {
                roomHelp.textContent = 'Chọn loại dịch vụ trước, hệ thống sẽ lọc phòng cùng loại.';
                roomHelp.classList.remove('warning');
                return;
            }

            if (visibleCount === 0) {
                roomSelect.value = '';
                roomHelp.textContent = 'Chưa có phòng hoạt động cho loại dịch vụ này. Hãy tạo phòng ở mục Quản lý phòng khám trước.';
                roomHelp.classList.add('warning');
                return;
            }

            roomHelp.textContent = 'Đang hiển thị ' + visibleCount + ' phòng phù hợp với loại dịch vụ ' + selectedType + '.';
            roomHelp.classList.remove('warning');
        }

        function updatePreview() {
            const slots = slotsSelect.value;
            const duration = durationInput.value;
            const previewBox = document.getElementById('previewBox');

            if (slots && duration) {
                const slotsInt = parseInt(slots);
                const durationInt = parseInt(duration);
                const actualDuration = slotsInt * durationInt;

                document.getElementById('previewSlots').textContent = slotsInt;
                document.getElementById('previewDuration').textContent = durationInt;
                document.getElementById('previewTotal').textContent =
                    slotsInt + ' slot' + (slotsInt > 1 ? 's' : '') + ' × ' + durationInt + 'p = ' + actualDuration + 'p';

                previewBox.style.display = 'block';
            } else {
                previewBox.style.display = 'none';
            }
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm dịch vụ mới';
            serviceForm.reset();

            document.getElementById('formMethod').value = 'POST';
            document.getElementById('serviceId').value = '';
            document.getElementById('type').value = '';
            document.getElementById('room_id').value = '';
            document.getElementById('slots_required').value = '';
            document.getElementById('duration_minutes').value = '30';
            document.getElementById('is_active').checked = true;
            document.getElementById('submitBtn').textContent = 'Thêm mới';
            document.getElementById('previewBox').style.display = 'none';
            document.getElementById('modalErrors').style.display = 'none';

            serviceForm.action = '{{ route("admin.services.store") }}';

            filterRoomsByType();

            document.getElementById('formModal').classList.add('active');
        }

        function openEditModal(id) {
            const service = servicesData[id];

            if (!service) {
                showModalError('Không tìm thấy dữ liệu dịch vụ.');
                return;
            }

            document.getElementById('modalTitle').textContent = 'Sửa dịch vụ';
            document.getElementById('serviceId').value = 'DV' + String(service.id).padStart(3, '0');
            document.getElementById('name').value = service.name || '';
            document.getElementById('description').value = service.description || '';
            document.getElementById('type').value = service.type || '';
            document.getElementById('slots_required').value = service.slots_required || '';
            document.getElementById('duration_minutes').value = service.duration_minutes || 30;
            document.getElementById('is_active').checked = !!service.is_active;
            document.getElementById('formMethod').value = 'PATCH';
            document.getElementById('submitBtn').textContent = 'Cập nhật';
            document.getElementById('modalErrors').style.display = 'none';

            serviceForm.action = '{{ route("admin.services.update", ":id") }}'.replace(':id', id);

            filterRoomsByType(service.room_id || '');

            document.getElementById('formModal').classList.add('active');
            updatePreview();
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('active');
        }

        function confirmDelete(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.services.destroy", ":id") }}'.replace(':id', id);
            form.innerHTML = '@csrf @method("DELETE")';

            document.body.appendChild(form);
            form.submit();
        }

        serviceForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const type = document.getElementById('type').value.trim();
            const slotsRequired = document.getElementById('slots_required').value.trim();
            const durationMinutes = document.getElementById('duration_minutes').value.trim();

            if (!name || !type || !slotsRequired || !durationMinutes) {
                showModalError('Vui lòng điền đủ thông tin bắt buộc.');
                return;
            }

            const selectedRoom = roomSelect.value;
            if (selectedRoom) {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const roomType = selectedOption.dataset.type || '';

                if (roomType !== type) {
                    showModalError('Phòng khám phải cùng loại với dịch vụ.');
                    return;
                }
            }

            const duration = parseInt(durationMinutes);
            if (isNaN(duration) || duration < 30 || duration > 300) {
                showModalError('Thời gian mỗi slot phải từ 30 đến 300 phút.');
                return;
            }

            this.submit();
        });

        function showModalError(message) {
            const errorDiv = document.getElementById('modalErrors');
            const errorList = document.getElementById('errorList');

            errorList.innerHTML = message;
            errorDiv.style.display = 'block';
        }

        document.getElementById('formModal')?.addEventListener('click', function (e) {
            if (e.target.id === 'formModal') {
                closeModal();
            }
        });
    </script>
@endsection