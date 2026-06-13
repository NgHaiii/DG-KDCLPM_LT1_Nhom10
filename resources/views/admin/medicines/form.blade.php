@extends('layouts.admin-layout')

@php
    $isEdit = $medicine->exists;
@endphp

@section('title', $isEdit ? 'Cập nhật thuốc' : 'Thêm thuốc')
@section('page-title', $isEdit ? 'Cập nhật thuốc' : 'Thêm thuốc')
@section('page-subtitle', $isEdit ? 'Cập nhật thông tin thuốc, giá bán và tồn kho' : 'Tạo thuốc mới để sử dụng khi lập hóa đơn khám bệnh')

@section('header-actions')
    <a href="{{ route('admin.medicines.index') }}" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>
@endsection

@section('styles')
<style>
    .medicine-form-shell {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) 360px;
        gap: 22px;
        align-items: start;
    }

    .form-card,
    .side-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .card-head {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 20px;
        font-weight: 850;
        color: #0f172a;
    }

    .card-head i {
        color: #0ea5e9;
        font-size: 24px;
    }

    .card-body {
        padding: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        font-weight: 800;
        color: #334155;
        margin-bottom: 8px;
    }

    .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        min-height: 46px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        padding: 0 14px;
        font-size: 14px;
        color: #0f172a;
        background: #fff;
        outline: none;
        transition: all .2s ease;
    }

    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
    }

    textarea.form-control {
        padding: 13px 14px;
        min-height: 120px;
        resize: vertical;
        line-height: 1.5;
    }

    .hint {
        color: #64748b;
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.45;
    }

    .error-text {
        color: #dc2626;
        font-size: 12px;
        margin-top: 6px;
        font-weight: 650;
    }

    .switch-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
    }

    .switch-info strong {
        display: block;
        color: #0f172a;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .switch-info span {
        color: #64748b;
        font-size: 13px;
    }

    .toggle {
        position: relative;
        width: 54px;
        height: 30px;
        flex-shrink: 0;
    }

    .toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #cbd5e1;
        border-radius: 999px;
        transition: .2s;
    }

    .toggle-slider:before {
        content: "";
        position: absolute;
        width: 24px;
        height: 24px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .2);
    }

    .toggle input:checked + .toggle-slider {
        background: #0ea5e9;
    }

    .toggle input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }

    .form-actions {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .info-list {
        display: grid;
        gap: 14px;
    }

    .info-item {
        padding: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 15px;
        background: #f8fafc;
    }

    .info-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .info-value {
        color: #0f172a;
        font-size: 17px;
        font-weight: 850;
    }

    .notice {
        margin-top: 16px;
        padding: 15px;
        border-radius: 15px;
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        color: #075985;
        font-size: 13px;
        line-height: 1.55;
    }

    @media (max-width: 1100px) {
        .medicine-form-shell {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <form
        method="POST"
        action="{{ $isEdit ? route('admin.medicines.update', $medicine) : route('admin.medicines.store') }}"
        class="medicine-form-shell"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="form-card">
            <div class="card-head">
                <i class="ri-capsule-line"></i>
                Thông tin thuốc
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mã thuốc <span class="required">*</span></label>
                        <input
                            type="text"
                            name="code"
                            class="form-control"
                            value="{{ old('code', $medicine->code) }}"
                            placeholder="VD: MED001"
                            required
                        >
                        @error('code')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Tên thuốc <span class="required">*</span></label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $medicine->name) }}"
                            placeholder="VD: Amoxicillin"
                            required
                        >
                        @error('name')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Hoạt chất / tên generic</label>
                        <input
                            type="text"
                            name="generic_name"
                            class="form-control"
                            value="{{ old('generic_name', $medicine->generic_name) }}"
                            placeholder="VD: Amoxicillin"
                        >
                        @error('generic_name')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Hàm lượng</label>
                        <input
                            type="text"
                            name="strength"
                            class="form-control"
                            value="{{ old('strength', $medicine->strength) }}"
                            placeholder="VD: 500mg"
                        >
                        @error('strength')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Đơn vị tính <span class="required">*</span></label>
                        <input
                            type="text"
                            name="unit"
                            class="form-control"
                            value="{{ old('unit', $medicine->unit) }}"
                            list="unitOptions"
                            placeholder="VD: viên, vỉ, chai, tuýp..."
                            required
                        >
                        <datalist id="unitOptions">
                            <option value="viên">
                            <option value="vỉ">
                            <option value="hộp">
                            <option value="chai">
                            <option value="tuýp">
                            <option value="gói">
                            <option value="ống">
                        </datalist>
                        @error('unit')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nhóm thuốc</label>
                        <input
                            type="text"
                            name="category"
                            class="form-control"
                            value="{{ old('category', $medicine->category) }}"
                            list="categoryOptions"
                            placeholder="VD: Kháng sinh, Giảm đau..."
                        >
                        <datalist id="categoryOptions">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">
                            @endforeach
                            <option value="Kháng sinh">
                            <option value="Giảm đau">
                            <option value="Kháng viêm">
                            <option value="Sát khuẩn">
                            <option value="Vitamin">
                            <option value="Thuốc dùng ngoài">
                        </datalist>
                        @error('category')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Giá bán <span class="required">*</span></label>
                        <input
                            type="number"
                            name="sale_price"
                            class="form-control"
                            value="{{ old('sale_price', $medicine->sale_price) }}"
                            min="0"
                            step="1000"
                            required
                        >
                        <div class="hint">Giá này dùng để tính tiền thuốc trong hóa đơn khám.</div>
                        @error('sale_price')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số lượng tồn <span class="required">*</span></label>
                        <input
                            type="number"
                            name="stock_quantity"
                            class="form-control"
                            value="{{ old('stock_quantity', $medicine->stock_quantity) }}"
                            min="0"
                            step="1"
                            required
                        >
                        @error('stock_quantity')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Tồn kho tối thiểu</label>
                        <input
                            type="number"
                            name="min_stock_quantity"
                            class="form-control"
                            value="{{ old('min_stock_quantity', $medicine->min_stock_quantity) }}"
                            min="0"
                            step="1"
                        >
                        <div class="hint">Khi tồn kho nhỏ hơn hoặc bằng mức này, hệ thống hiển thị cảnh báo sắp hết.</div>
                        @error('min_stock_quantity')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label>Mô tả / ghi chú</label>
                        <textarea
                            name="description"
                            class="form-control"
                            placeholder="Ghi chú cách dùng, lưu ý khi kê thuốc hoặc thông tin quản lý..."
                        >{{ old('description', $medicine->description) }}</textarea>
                        @error('description')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <div class="switch-row">
                            <div class="switch-info">
                                <strong>Trạng thái sử dụng</strong>
                                <span>Thuốc đang hoạt động mới được chọn khi lập hóa đơn.</span>
                            </div>

                            <label class="toggle">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    @checked(old('is_active', $medicine->is_active ?? true))
                                >
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-secondary">
                        Hủy
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i>
                        {{ $isEdit ? 'Lưu thay đổi' : 'Thêm thuốc' }}
                    </button>
                </div>
            </div>
        </div>

        <aside class="side-card">
            <div class="card-head">
                <i class="ri-information-line"></i>
                Tóm tắt
            </div>

            <div class="card-body">
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">Trạng thái</div>
                        <div class="info-value">
                            {{ old('is_active', $medicine->is_active ?? true) ? 'Đang sử dụng' : 'Ngừng sử dụng' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tồn kho hiện tại</div>
                        <div class="info-value">
                            {{ number_format((int) old('stock_quantity', $medicine->stock_quantity ?? 0)) }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Giá bán</div>
                        <div class="info-value">
                            {{ number_format((float) old('sale_price', $medicine->sale_price ?? 0), 0, ',', '.') }} đ
                        </div>
                    </div>

                    @if($isEdit)
                        <div class="info-item">
                            <div class="info-label">Cập nhật gần nhất</div>
                            <div class="info-value" style="font-size:15px;">
                                {{ optional($medicine->updated_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="notice">
                    <strong>Lưu ý:</strong> Không nên xóa cứng thuốc đã từng xuất hiện trong hóa đơn.
                    Khi không còn dùng, hãy chuyển thuốc sang trạng thái ngừng sử dụng để giữ lịch sử thanh toán chính xác.
                </div>
            </div>
        </aside>
    </form>
@endsection