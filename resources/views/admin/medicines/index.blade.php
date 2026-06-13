@extends('layouts.admin-layout')

@section('title', 'Quản lý thuốc')
@section('page-title', 'Quản lý thuốc')
@section('page-subtitle', 'Quản lý danh mục thuốc, giá bán và tồn kho cơ bản')

@section('header-actions')
    <a href="{{ route('admin.medicines.create') }}" class="btn btn-primary">
        <i class="ri-add-line"></i>
        Thêm thuốc
    </a>
@endsection

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.blue { background: #e0f2fe; color: #0284c7; }
    .stat-icon.green { background: #d1fae5; color: #059669; }
    .stat-icon.amber { background: #fef3c7; color: #d97706; }
    .stat-icon.red { background: #fee2e2; color: #dc2626; }

    .stat-label {
        color: #64748b;
        font-weight: 650;
        font-size: 13px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 850;
        color: #0f172a;
        line-height: 1;
    }

    .filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 22px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .filter-form {
        display: grid;
        grid-template-columns: 1.6fr 0.9fr 0.9fr 0.9fr auto auto;
        gap: 12px;
        align-items: end;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 750;
        color: #334155;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        height: 44px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #0f172a;
        outline: none;
        background: #fff;
        transition: all .2s ease;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
    }

    .quick-search-note {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        min-height: 18px;
    }

    .medicine-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .medicine-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .medicine-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 20px;
        font-weight: 850;
        color: #0f172a;
    }

    .medicine-count {
        background: #e0f2fe;
        color: #0284c7;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 850;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .medicine-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1080px;
    }

    .medicine-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .medicine-table td {
        padding: 15px 16px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        color: #0f172a;
        font-size: 14px;
    }

    .medicine-table tr:hover td {
        background: #f8fbff;
    }

    .medicine-name {
        font-weight: 850;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .medicine-meta {
        color: #64748b;
        font-size: 13px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .badge-active { background: #dcfce7; color: #166534; }
    .badge-inactive { background: #f1f5f9; color: #64748b; }
    .badge-stock-ok { background: #dcfce7; color: #166534; }
    .badge-stock-low { background: #fef3c7; color: #92400e; }
    .badge-stock-out { background: #fee2e2; color: #991b1b; }

    .price {
        font-weight: 850;
        color: #0369a1;
    }

    .stock-box {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .stock-main {
        font-weight: 850;
        color: #0f172a;
    }

    .stock-min {
        color: #64748b;
        font-size: 12px;
    }

    .actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        font-size: 18px;
        transition: all .2s ease;
    }

    .icon-btn:hover {
        border-color: #0ea5e9;
        color: #0284c7;
        background: #f0f9ff;
    }

    .icon-btn.warning:hover {
        border-color: #f59e0b;
        color: #d97706;
        background: #fffbeb;
    }

    .icon-btn.danger:hover {
        border-color: #ef4444;
        color: #dc2626;
        background: #fef2f2;
    }

    .empty-state {
        padding: 52px 20px;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        font-size: 54px;
        color: #bae6fd;
        display: block;
        margin-bottom: 10px;
    }

    .pagination-wrap {
        padding: 18px 22px;
        border-top: 1px solid #e2e8f0;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-box {
        width: min(520px, 100%);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.3);
        overflow: hidden;
    }

    .modal-head {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 19px;
        font-weight: 850;
        color: #0f172a;
    }

    .modal-body {
        padding: 22px;
    }

    .modal-actions {
        padding: 16px 22px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .textarea-control {
        min-height: 92px;
        padding: 12px 14px;
        resize: vertical;
    }

    @media (max-width: 1180px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-form {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .stats-grid,
        .filter-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="ri-capsule-line"></i></div>
            <div>
                <div class="stat-label">Tổng thuốc</div>
                <div class="stat-value">{{ $totalMedicines }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green"><i class="ri-checkbox-circle-line"></i></div>
            <div>
                <div class="stat-label">Đang sử dụng</div>
                <div class="stat-value">{{ $activeMedicines }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amber"><i class="ri-error-warning-line"></i></div>
            <div>
                <div class="stat-label">Sắp hết</div>
                <div class="stat-value">{{ $lowStockMedicines }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red"><i class="ri-close-circle-line"></i></div>
            <div>
                <div class="stat-label">Hết hàng</div>
                <div class="stat-value">{{ $outOfStockMedicines }}</div>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.medicines.index') }}" class="filter-form">
            <div class="form-group">
                <label>Tìm kiếm nhanh</label>
                <input
                    type="text"
                    id="medicineQuickSearch"
                    class="form-control"
                    value=""
                    placeholder="Nhập mã thuốc, tên thuốc, hoạt chất..."
                    autocomplete="off"
                >
               
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="active" @selected($status === 'active')>Đang sử dụng</option>
                    <option value="inactive" @selected($status === 'inactive')>Ngừng sử dụng</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tồn kho</label>
                <select name="stock" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="low" @selected($stock === 'low')>Sắp hết</option>
                    <option value="out" @selected($stock === 'out')>Hết hàng</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nhóm thuốc</label>
                <select name="category" class="form-control">
                    <option value="">Tất cả</option>
                    @foreach($categories as $item)
                        <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="ri-filter-3-line"></i>
                Lọc
            </button>

            <a href="{{ route('admin.medicines.index') }}" class="btn btn-secondary">
                <i class="ri-close-line"></i>
                Xóa lọc
            </a>
        </form>
    </div>

    <div class="medicine-card">
        <div class="medicine-header">
            <div class="medicine-title">
                <i class="ri-medicine-bottle-line" style="color:#0ea5e9;"></i>
                Danh sách thuốc
            </div>
            <div class="medicine-count js-medicine-count">{{ $medicines->count() }} thuốc đang hiển thị</div>
        </div>

        <div class="table-wrap">
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th>Thuốc</th>
                        <th>Nhóm</th>
                        <th>Đơn vị</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái kho</th>
                        <th>Hoạt động</th>
                        <th style="text-align:right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="js-medicine-tbody">
                    @forelse($medicines as $medicine)
                        @php
                            $stockClass = 'badge-stock-ok';
                            $stockLabel = 'Đủ hàng';

                            if ((int) $medicine->stock_quantity <= 0) {
                                $stockClass = 'badge-stock-out';
                                $stockLabel = 'Hết hàng';
                            } elseif ((int) $medicine->stock_quantity <= (int) $medicine->min_stock_quantity) {
                                $stockClass = 'badge-stock-low';
                                $stockLabel = 'Sắp hết';
                            }

                            $searchText = implode(' ', array_filter([
                                $medicine->code,
                                $medicine->name,
                                $medicine->generic_name,
                                $medicine->strength,
                                $medicine->unit,
                                $medicine->category,
                                $medicine->is_active ? 'đang sử dụng active' : 'ngừng sử dụng inactive',
                                $stockLabel,
                            ]));
                        @endphp

                        <tr class="medicine-row" data-search="{{ e($searchText) }}">
                            <td>
                                <div class="medicine-name">{{ $medicine->name }}</div>
                                <div class="medicine-meta">
                                    <span>Mã: {{ $medicine->code }}</span>
                                    @if($medicine->generic_name)
                                        <span>• {{ $medicine->generic_name }}</span>
                                    @endif
                                    @if($medicine->strength)
                                        <span>• {{ $medicine->strength }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>{{ $medicine->category ?: 'Chưa phân nhóm' }}</td>

                            <td>{{ $medicine->unit }}</td>

                            <td>
                                <span class="price">
                                    {{ number_format($medicine->sale_price, 0, ',', '.') }} đ
                                </span>
                            </td>

                            <td>
                                <div class="stock-box">
                                    <div class="stock-main">{{ number_format($medicine->stock_quantity) }} {{ $medicine->unit }}</div>
                                    <div class="stock-min">Tối thiểu: {{ number_format($medicine->min_stock_quantity) }}</div>
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $stockClass }}">
                                    <i class="ri-archive-line"></i>
                                    {{ $stockLabel }}
                                </span>
                            </td>

                            <td>
                                @if($medicine->is_active)
                                    <span class="badge badge-active">
                                        <i class="ri-checkbox-circle-line"></i>
                                        Đang dùng
                                    </span>
                                @else
                                    <span class="badge badge-inactive">
                                        <i class="ri-pause-circle-line"></i>
                                        Ngừng dùng
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="actions">
                                    <button
                                        type="button"
                                        class="icon-btn warning js-stock-btn"
                                        title="Cập nhật tồn kho"
                                        data-url="{{ route('admin.medicines.stock.adjust', $medicine) }}"
                                        data-name="{{ $medicine->name }}"
                                        data-stock="{{ $medicine->stock_quantity }}"
                                        data-unit="{{ $medicine->unit }}"
                                    >
                                        <i class="ri-archive-stack-line"></i>
                                    </button>

                                    <a href="{{ route('admin.medicines.edit', $medicine) }}" class="icon-btn" title="Sửa thuốc">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.medicines.destroy', $medicine) }}" onsubmit="return confirm('Ngừng sử dụng thuốc này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn danger" title="Ngừng sử dụng">
                                            <i class="ri-stop-circle-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="ri-capsule-line"></i>
                                    <strong>Chưa có thuốc nào</strong>
                                    <div>Hãy thêm thuốc đầu tiên để sử dụng cho hóa đơn khám bệnh.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="clientEmptyRow" style="display:none;">
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="ri-search-line"></i>
                                <strong>Không tìm thấy thuốc phù hợp</strong>
                                <div>Thử nhập mã thuốc, tên thuốc, hoạt chất hoặc nhóm thuốc khác.</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($medicines->hasPages())
            <div class="pagination-wrap">
                {{ $medicines->links() }}
            </div>
        @endif
    </div>

    <div class="modal-backdrop" id="stockModal">
        <div class="modal-box">
            <form method="POST" id="stockForm">
                @csrf
                @method('PATCH')

                <div class="modal-head">
                    <div>
                        <div class="modal-title">Cập nhật tồn kho</div>
                        <div style="color:#64748b;font-size:13px;margin-top:4px;" id="stockMedicineName"></div>
                    </div>

                    <button type="button" class="icon-btn" id="closeStockModal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px;margin-bottom:16px;">
                        Tồn hiện tại:
                        <strong id="currentStockText"></strong>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Kiểu cập nhật</label>
                        <select name="adjustment_type" class="form-control" required>
                            <option value="set">Đặt lại số tồn</option>
                            <option value="increase">Nhập thêm</option>
                            <option value="decrease">Giảm tồn</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label>Số lượng</label>
                        <input type="number" name="quantity" class="form-control" min="0" value="0" required>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="description" class="form-control textarea-control" placeholder="Ghi chú cập nhật tồn kho nếu cần..."></textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelStockModal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i>
                        Lưu tồn kho
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const stockModal = document.getElementById('stockModal');
    const stockForm = document.getElementById('stockForm');
    const stockMedicineName = document.getElementById('stockMedicineName');
    const currentStockText = document.getElementById('currentStockText');

    const searchInput = document.getElementById('medicineQuickSearch');
    const medicineRows = Array.from(document.querySelectorAll('.medicine-row'));
    const medicineCount = document.querySelector('.js-medicine-count');
    const quickSearchStatus = document.getElementById('quickSearchStatus');
    const clientEmptyRow = document.getElementById('clientEmptyRow');

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .trim();
    }

    function bindStockButtons() {
        document.querySelectorAll('.js-stock-btn').forEach((button) => {
            button.onclick = () => {
                stockForm.action = button.dataset.url;
                stockMedicineName.textContent = button.dataset.name;
                currentStockText.textContent = `${button.dataset.stock} ${button.dataset.unit}`;
                stockModal.classList.add('show');
            };
        });
    }

    function closeStockModal() {
        stockModal.classList.remove('show');
        stockForm.reset();
    }

    function setSearchStatus(message) {
        if (!quickSearchStatus) return;

        quickSearchStatus.innerHTML = `<i class="ri-flashlight-line"></i> ${message}`;
    }

    function filterMedicinesInHtml() {
        const keyword = normalizeText(searchInput.value);
        let visibleCount = 0;

        medicineRows.forEach((row) => {
            const searchText = normalizeText(row.dataset.search + ' ' + row.innerText);
            const matched = keyword === '' || searchText.includes(keyword);

            row.style.display = matched ? '' : 'none';

            if (matched) {
                visibleCount++;
            }
        });

        if (clientEmptyRow) {
            clientEmptyRow.style.display = visibleCount === 0 && medicineRows.length > 0 ? '' : 'none';
        }

        if (medicineCount) {
            medicineCount.textContent = `${visibleCount} thuốc đang hiển thị`;
        }

        if (keyword === '') {
            setSearchStatus('Tìm nhanh theo dữ liệu đang hiển thị, không tải lại trang.');
        } else {
            setSearchStatus(`Đang hiển thị ${visibleCount} kết quả phù hợp.`);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterMedicinesInHtml);
    }

    bindStockButtons();

    document.getElementById('closeStockModal').addEventListener('click', closeStockModal);
    document.getElementById('cancelStockModal').addEventListener('click', closeStockModal);

    stockModal.addEventListener('click', (event) => {
        if (event.target === stockModal) {
            closeStockModal();
        }
    });
</script>
@endsection