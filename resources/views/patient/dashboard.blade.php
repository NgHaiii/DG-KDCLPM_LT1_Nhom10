@extends('layouts.patient-layout')

@section('title', 'Dashboard - DentalCare')

@section('page-title', 'Chào mừng, ' . Auth::user()->name)
@section('page-subtitle', 'Quản lý lịch khám và hồ sơ sức khỏe của bạn')

@section('styles')
<style>
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        padding: 28px;
        border-radius: 16px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .stat-card:hover {
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    .stat-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .stat-card-content {
        flex: 1;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 6px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
    }

    /* Table Styles */
    .table-container {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
    }

    .table-header {
        padding: 24px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }

    .table-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-header i {
        color: #0ea5e9;
        font-size: 18px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: #f8fafc;
    }

    th {
        padding: 14px 24px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(226, 232, 240, 0.8);
    }

    td {
        padding: 16px 24px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        font-size: 14px;
        color: #1e293b;
    }

    tbody tr:hover {
        background: #f8fafc;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .badge-confirmed {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-completed {
        background: rgba(59, 130, 246, 0.1);
        color: #1e3a8a;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    /* Action Section */
    .action-section {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
    }

    .action-section h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .action-section p {
        color: #64748b;
        margin-bottom: 20px;
        font-size: 14px;
    }

    /* Grid Layout */
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 24px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .stat-card {
            flex-direction: column;
            text-align: center;
        }

        .table-container {
            overflow-x: auto;
        }

        th, td {
            padding: 12px 16px;
            font-size: 13px;
        }

        .action-section {
            padding: 24px;
        }
    }
</style>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon">📅</div>
        <div class="stat-card-content">
            <div class="stat-label">Lịch khám sắp tới</div>
            <div class="stat-value">2</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">📊</div>
        <div class="stat-card-content">
            <div class="stat-label">Tổng lịch khám</div>
            <div class="stat-value">12</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">👨‍⚕️</div>
        <div class="stat-card-content">
            <div class="stat-label">Bác sĩ của tôi</div>
            <div class="stat-value">3</div>
        </div>
    </div>
</div>

<!-- Upcoming Appointments -->
<div class="table-container">
    <div class="table-header">
        <h3>
            <i class="ri-calendar-check-line"></i>
            Lịch khám sắp tới
        </h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ngày khám</th>
                <th>Giờ khám</th>
                <th>Bác sĩ</th>
                <th>Dịch vụ</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>10/05/2026</td>
                <td>14:00</td>
                <td>Dr. Trần B</td>
                <td>Lấy cao răng</td>
                <td><span class="badge badge-pending">Chờ xác nhận</span></td>
                <td>
                    <a href="{{ route('patient.appointment.show', 1) }}" class="btn btn-sm" style="background: var(--primary-gradient); text-decoration: none;">Chi tiết</a>
                </td>
            </tr>
            <tr>
                <td>15/05/2026</td>
                <td>09:30</td>
                <td>Dr. Lê D</td>
                <td>Trám răng</td>
                <td><span class="badge badge-confirmed">Đã xác nhận</span></td>
                <td>
                    <a href="{{ route('patient.appointment.show', 2) }}" class="btn btn-sm" style="background: var(--primary-gradient); text-decoration: none;">Chi tiết</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Action Sections -->
<div class="grid-2">
    <div class="action-section">
        <h3>
            <i class="ri-add-circle-line" style="color: var(--primary); margin-right: 8px;"></i>
            Đặt lịch khám mới
        </h3>
        <p>Lên lịch với bác sĩ của bạn một cách nhanh chóng và tiện lợi</p>
        <a href="{{ route('patient.appointment.create') }}" class="btn btn-primary" style="text-decoration: none;">
            <i class="ri-add-line"></i> Đặt lịch ngay
        </a>
    </div>

    <div class="action-section">
        <h3>
            <i class="ri-file-medical-line" style="color: var(--primary); margin-right: 8px;"></i>
            Xem lịch sử khám
        </h3>
        <p>Xem lại các cuộc khám bệnh trước đây và hồ sơ sức khỏe</p>
        <a href="{{ route('patient.medical-records') }}" class="btn btn-secondary" style="text-decoration: none;">
            <i class="ri-file-list-line"></i> Xem chi tiết
        </a>
    </div>
</div>

<!-- Recent Activities -->
<div class="table-container" style="margin-top: 24px;">
    <div class="table-header">
        <h3>
            <i class="ri-history-line"></i>
            Hoạt động gần đây
        </h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ngày</th>
                <th>Loại hoạt động</th>
                <th>Chi tiết</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>05/06/2026</td>
                <td>Đặt lịch khám</td>
                <td>Khám nha chu với Dr. Trần B</td>
                <td><span class="badge badge-pending">Chờ xác nhận</span></td>
            </tr>
            <tr>
                <td>02/06/2026</td>
                <td>Khám xong</td>
                <td>Trám răng số 26</td>
                <td><span class="badge badge-completed">Hoàn thành</span></td>
            </tr>
            <tr>
                <td>28/05/2026</td>
                <td>Thanh toán</td>
                <td>Thanh toán hóa đơn khám ngày 25/05</td>
                <td><span class="badge badge-confirmed">Đã thanh toán</span></td>
            </tr>
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Patient Dashboard loaded');
    });
</script>
@endsection