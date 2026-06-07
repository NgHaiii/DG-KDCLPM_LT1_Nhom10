@extends('layouts.admin-layout')

@section('title', 'Gán chuyên khoa dịch vụ')

@section('page-title', '⚙️ Gán chuyên khoa dịch vụ')

@section('page-subtitle', 'Liên kết chuyên khoa bác sĩ với danh mục dịch vụ')

@section('content')
<style>
    .spec-table-wrapper {
        background: var(--card-bg);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .spec-table {
        width: 100%;
        border-collapse: collapse;
    }

    .spec-table thead {
        background: var(--primary-gradient);
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .spec-table thead th {
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .spec-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: all 0.2s ease;
    }

    .spec-table tbody tr:hover {
        background: rgba(14, 165, 233, 0.03);
    }

    .spec-table tbody td {
        padding: 16px;
        border: 1px solid var(--border-color);
    }

    .service-name {
        font-weight: 600;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .service-name i {
        color: var(--primary);
        font-size: 16px;
    }

    .service-type {
        display: inline-block;
        background: var(--primary-light);
        color: var(--primary);
        padding: 6px 12px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 500;
    }

    .spec-form {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .spec-select {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-family: var(--font-body);
        font-size: 14px;
        color: var(--text-main);
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .spec-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .spec-select:hover {
        border-color: var(--primary);
    }

    .spec-btn-save {
        padding: 10px 20px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
    }

    .spec-btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.45);
    }

    .spec-btn-save:active {
        transform: translateY(0);
    }

    .spec-btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .spec-loading {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .spec-btn-save.loading {
        pointer-events: none;
    }

    .spec-btn-save.loading .spec-loading {
        display: inline-block;
    }

    .spec-btn-save.loading i {
        display: none;
    }

    .empty-state {
        text-align: center;
        padding: 60px 40px;
    }

    .empty-icon {
        font-size: 64px;
        color: var(--primary-light);
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-text {
        color: var(--text-muted);
        font-size: 16px;
        margin: 0;
    }

    .info-box {
        background: var(--info-light);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: var(--radius-lg);
        padding: 16px;
        margin-top: 28px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .info-icon {
        color: var(--info);
        font-size: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .info-text {
        color: var(--info-dark);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }

    .info-text strong {
        font-weight: 600;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary);
        margin: 0;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 13px;
        margin-top: 8px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .spec-form {
            flex-direction: column;
        }

        .spec-table {
            font-size: 13px;
        }

        .spec-table thead th,
        .spec-table tbody td {
            padding: 12px;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div>
    <div class="stats-container">
        <div class="stat-card">
            <p class="stat-number">{{ $services->count() }}</p>
            <p class="stat-label">📋 Tổng dịch vụ</p>
        </div>
        <div class="stat-card">
            <p class="stat-number" id="assignedCount">{{ $services->whereNotNull('required_specialization')->count() }}</p>
            <p class="stat-label">✅ Đã gán chuyên khoa</p>
        </div>
        <div class="stat-card">
            <p class="stat-number" id="unassignedCount">{{ $services->whereNull('required_specialization')->count() }}</p>
            <p class="stat-label">⚠️ Chưa gán</p>
        </div>
        <div class="stat-card">
            <p class="stat-number">{{ count($specializations) }}</p>
            <p class="stat-label">👨‍⚕️ Chuyên khoa</p>
        </div>
    </div>

    <div class="spec-table-wrapper">
        @if ($services->isEmpty())
            <div class="empty-state">
                <p class="empty-icon">📭</p>
                <p class="empty-text">Không có dịch vụ nào để gán chuyên khoa</p>
            </div>
        @else
            <table class="spec-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">
                            <i class="ri-hospital-line"></i> Tên dịch vụ
                        </th>
                        <th style="width: 20%;">
                            <i class="ri-price-tag-3-line"></i> Loại (Type)
                        </th>
                        <th style="width: 30%;">
                            <i class="ri-stethoscope-line"></i> Chuyên khoa
                        </th>
                        <th style="width: 20%; text-align: center;">
                            <i class="ri-tools-line"></i> Hành động
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr data-assigned="{{ $service->required_specialization ? '1' : '0' }}">
                            <td>
                                <span class="service-name">
                                    <i class="ri-file-list-line"></i>
                                    {{ $service->name }}
                                </span>
                            </td>

                            <td>
                                @if ($service->type)
                                    <span class="service-type">{{ $service->type }}</span>
                                @else
                                    <span class="service-type" style="background: #fee2e2; color: #991b1b;">N/A</span>
                                @endif
                            </td>

                            <td>
                                <form action="{{ route('admin.service-specialization.update', $service->id) }}"
                                      method="POST"
                                      class="spec-form"
                                      data-service-id="{{ $service->id }}">
                                    @csrf
                                    @method('PUT')

                                    <select name="required_specialization"
                                            class="spec-select"
                                            data-service-name="{{ $service->name }}"
                                            required>
                                        <option value="">-- Chọn chuyên khoa --</option>
                                        @foreach ($specializations as $spec)
                                            <option value="{{ $spec }}"
                                                {{ $service->required_specialization === $spec ? 'selected' : '' }}>
                                                {{ $spec }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="spec-btn-save">
                                        <i class="ri-save-3-line"></i>
                                        <span class="btn-text">💾 Lưu</span>
                                        <div class="spec-loading"></div>
                                    </button>
                                </form>
                            </td>

                            <td style="text-align: center;">
                                @if ($service->required_specialization)
                                    <span style="display: inline-block; background: #d1fae5; color: #065f46; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        ✅ Đã gán
                                    </span>
                                @else
                                    <span style="display: inline-block; background: #fee2e2; color: #991b1b; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        ⚠️ Chưa gán
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="info-box">
        <i class="ri-lightbulb-flash-line info-icon"></i>
        <p class="info-text">
            <strong>💡 Hướng dẫn:</strong> Chọn chuyên khoa phù hợp với loại dịch vụ.
            Khi bệnh nhân đặt lịch khám dịch vụ này, hệ thống sẽ tìm và gợi ý bác sĩ có chuyên khoa tương ứng.
            Bác sĩ phải có ca trực và slot trống mới được hiển thị.
        </p>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.querySelectorAll('.spec-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('.spec-btn-save');
            const select = this.querySelector('.spec-select');
            const serviceName = select.dataset.serviceName;
            const selectedSpecialization = select.value;

            if (!selectedSpecialization) {
                select.style.borderColor = '#ef4444';
                showAlert('❌ Vui lòng chọn chuyên khoa trước khi lưu.', 'error');
                return;
            }

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(data.message || 'Lỗi khi cập nhật chuyên khoa');
                    }

                    return data;
                })
                .then(data => {
                    showAlert(data.message || `✅ Dịch vụ "${serviceName}" cập nhật thành công!`, 'success');

                    const row = this.closest('tr');
                    const wasAssigned = row.dataset.assigned === '1';

                    row.dataset.assigned = '1';

                    const statusCell = row.querySelector('td:last-child');
                    statusCell.innerHTML = '<span style="display: inline-block; background: #d1fae5; color: #065f46; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">✅ Đã gán</span>';

                    if (!wasAssigned) {
                        const assignedCount = document.getElementById('assignedCount');
                        const unassignedCount = document.getElementById('unassignedCount');

                        assignedCount.textContent = Number(assignedCount.textContent) + 1;
                        unassignedCount.textContent = Math.max(Number(unassignedCount.textContent) - 1, 0);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert(error.message || '❌ Lỗi khi cập nhật. Vui lòng thử lại.', 'error');
                })
                .finally(() => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                });
        });
    });

    function showAlert(message, type = 'info') {
        const oldAlerts = document.querySelectorAll('.spec-inline-alert');
        oldAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${type} spec-inline-alert`;

        const iconClass = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';

        alertDiv.innerHTML = `
            <i class="${iconClass}" style="font-size:18px;flex-shrink:0;"></i>
            ${message}
        `;

        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 4000);
    }

    document.querySelectorAll('.spec-select').forEach(select => {
        select.addEventListener('change', function() {
            if (this.value === '') {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = 'var(--border-color)';
            }
        });
    });
</script>
@endsection