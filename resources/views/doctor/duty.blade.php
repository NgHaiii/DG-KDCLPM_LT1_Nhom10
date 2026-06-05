@extends('layouts.doctor-layout')

@section('title', 'Lịch Trực')

@section('page-title', 'Lịch Trực')
@section('page-subtitle', 'Xem các ca trực được giao cho tôi')

@section('styles')
<style>
    /* ===== STATS SECTION ===== */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-left: 5px solid;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .stat-card.total { border-left-color: #0ea5e9; }
    .stat-card.approved { border-left-color: #10b981; }
    .stat-card.pending { border-left-color: #f59e0b; }

    .stat-card p:first-child {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-card p:last-child {
        font-size: 2.25rem;
        font-weight: 700;
    }

    .stat-card.total p:last-child { color: #0ea5e9; }
    .stat-card.approved p:last-child { color: #10b981; }
    .stat-card.pending p:last-child { color: #f59e0b; }

    /* ===== FILTER & SEARCH ===== */
    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .filter-group label {
        font-weight: 600;
        color: #4b5563;
        font-size: 0.9rem;
    }

    .filter-group select,
    .filter-group input {
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    /* ===== DUTY LIST ===== */
    .duties-container {
        display: grid;
        gap: 1rem;
    }

    .duty-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .duty-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-color: #0ea5e9;
    }

    .duty-card.approved {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-color: #10b981;
    }

    .duty-card.approved:hover {
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.2);
    }

    .duty-card.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-color: #f59e0b;
    }

    .duty-card.pending:hover {
        box-shadow: 0 8px 16px rgba(245, 158, 11, 0.2);
    }

    .duty-card.rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-color: #ef4444;
    }

    .duty-card.rejected:hover {
        box-shadow: 0 8px 16px rgba(239, 68, 68, 0.2);
    }

    .duty-info {
        flex: 1;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 1.5rem;
        align-items: center;
    }

    .duty-date-box {
        background: rgba(14, 165, 233, 0.1);
        border: 2px solid #0ea5e9;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        text-align: center;
        min-width: 100px;
    }

    .duty-date-box .day {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0ea5e9;
    }

    .duty-date-box .month {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .duty-details {
        display: grid;
        gap: 0.5rem;
    }

    .duty-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .duty-title i {
        color: #0ea5e9;
        font-size: 1.25rem;
    }

    .duty-time {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #374151;
        font-weight: 600;
    }

    .duty-time i {
        color: #f59e0b;
        font-size: 1rem;
    }

    .duty-shift {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .duty-shift i {
        color: #8b5cf6;
    }

    .duty-notes {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        padding: 0.5rem 0;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .duty-notes i {
        color: #ec4899;
    }

    .duty-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge.approved {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        background: white;
        border-radius: 12px;
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .empty-state i {
        font-size: 3.5rem;
        color: #d1d5db;
        margin-bottom: 1rem;
        display: block;
    }

    .empty-state p {
        color: #9ca3af;
        font-size: 1rem;
        margin: 0;
    }

    /* ===== LOADING ===== */
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 3rem;
        color: #6b7280;
    }

    .spinner {
        border: 4px solid #f3f4f6;
        border-top: 4px solid #0ea5e9;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin-right: 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .duty-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .duty-info {
            grid-template-columns: 1fr;
            width: 100%;
        }

        .duty-actions {
            width: 100%;
            justify-content: space-between;
        }

        .filter-section {
            flex-direction: column;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Stats Section -->
<div class="stats-container">
    <div class="stat-card total">
        <p>Tổng Ca Trực</p>
        <p id="totalDuties">0</p>
    </div>
    <div class="stat-card approved">
        <p>Đã Duyệt</p>
        <p id="approvedDuties">0</p>
    </div>
    <div class="stat-card pending">
        <p>Chờ Duyệt</p>
        <p id="pendingDuties">0</p>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="filter-group">
        <label>Trạng Thái:</label>
        <select id="filterStatus">
            <option value="">Tất cả</option>
            <option value="approved">Đã duyệt</option>
            <option value="pending">Chờ duyệt</option>
            <option value="rejected">Bị từ chối</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Sắp Xếp:</label>
        <select id="sortBy">
            <option value="date-desc">Mới nhất trước</option>
            <option value="date-asc">Sớm nhất trước</option>
        </select>
    </div>
</div>

<!-- Duties List -->
<div class="duties-container" id="dutiesList">
    <div class="loading">
        <div class="spinner"></div>
        Đang tải dữ liệu...
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allDuties = [];

    // Fetch duties từ API
    function loadDuties() {
        fetch('{{ route("doctor.duty.get-duties") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        })
        .then(response => response.json())
        .then(data => {
            allDuties = data;
            renderDuties(allDuties);
            updateStats(allDuties);
        })
        .catch(error => {
            console.error('Lỗi:', error);
            document.getElementById('dutiesList').innerHTML = `
                <div class="empty-state">
                    <i class="ri-error-warning-line"></i>
                    <p>Có lỗi khi tải dữ liệu. Vui lòng thử lại!</p>
                </div>
            `;
        });
    }

    // Render duties
    function renderDuties(duties) {
        const container = document.getElementById('dutiesList');

        if (duties.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="ri-calendar-blank-line"></i>
                    <p>Bạn chưa có ca trực nào</p>
                </div>
            `;
            return;
        }

        container.innerHTML = duties.map(duty => {
            const workDate = new Date(duty.work_date);
            const day = workDate.getDate();
            const month = (workDate.getMonth() + 1).toString().padStart(2, '0');
            const year = workDate.getFullYear();
            const monthName = workDate.toLocaleDateString('vi-VN', { month: 'short' });

            return `
                <div class="duty-card ${duty.status}">
                    <div class="duty-info">
                        <div class="duty-date-box">
                            <div class="day">${day}</div>
                            <div class="month">${monthName}</div>
                        </div>
                        <div class="duty-details">
                            <div class="duty-title">
                                <i class="ri-calendar-event-line"></i>
                                ${day}/${month}/${year}
                            </div>
                            <div class="duty-time">
                                <i class="ri-time-line"></i>
                                ${duty.time_range}
                            </div>
                            <div class="duty-shift">
                                <i class="ri-alarm-line"></i>
                                ${duty.shift_name}
                            </div>
                            ${duty.notes ? `
                                <div class="duty-notes">
                                    <i class="ri-file-text-line"></i>
                                    ${duty.notes}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="duty-actions">
                        <span class="status-badge ${duty.status}">
                            ${duty.status_label}
                        </span>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Update stats
    function updateStats(duties) {
        document.getElementById('totalDuties').textContent = duties.length;
        document.getElementById('approvedDuties').textContent = duties.filter(d => d.status === 'approved').length;
        document.getElementById('pendingDuties').textContent = duties.filter(d => d.status === 'pending').length;
    }

    // Filter handler
    document.getElementById('filterStatus').addEventListener('change', function() {
        const status = this.value;
        const filtered = status ? allDuties.filter(d => d.status === status) : allDuties;
        renderDuties(applySorting(filtered));
    });

    // Sort handler
    document.getElementById('sortBy').addEventListener('change', function() {
        const filtered = document.getElementById('filterStatus').value 
            ? allDuties.filter(d => d.status === document.getElementById('filterStatus').value) 
            : allDuties;
        renderDuties(applySorting(filtered));
    });

    // Apply sorting
    function applySorting(duties) {
        const sortBy = document.getElementById('sortBy').value;
        return duties.sort((a, b) => {
            const dateA = new Date(a.work_date);
            const dateB = new Date(b.work_date);
            return sortBy === 'date-asc' ? dateA - dateB : dateB - dateA;
        });
    }

    // Load duties on page load
    loadDuties();
});
</script>
@endsection