@extends('layouts.doctor-layout')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Quản lý lịch khám và bệnh nhân của bạn')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #667eea;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12); }
    .stat-label { color: #999; font-size: 13px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; }
    .stat-value { font-size: 32px; font-weight: bold; color: #667eea; }
    .stat-trend { color: #10b981; font-size: 12px; margin-top: 8px; }

    .card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .card h3 { color: #333; font-size: 16px; margin: 0; }
    .card-action { color: #667eea; text-decoration: none; font-size: 14px; font-weight: 600; }
    .card-action:hover { text-decoration: underline; }

    table {
        width: 100%;
        border-collapse: collapse;
    }
    thead { background: #f9fafb; }
    th {
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        border-bottom: 2px solid #e5e7eb;
    }
    td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    tbody tr:hover { background: #f9fafb; }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-pending { background: #dbeafe; color: #0c4a6e; }
    .badge-cancelled { background: #fee2e2; color: #7f1d1d; }

    .quick-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    .quick-actions .btn { flex: 1; min-width: 150px; }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .quick-actions { flex-direction: column; }
        .quick-actions .btn { width: 100%; }
    }
</style>
@endsection

@section('content')
    <!-- Statistics Section -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">📋 Lịch khám hôm nay</div>
            <div class="stat-value">5</div>
            <div class="stat-trend">✓ 3 hoàn thành</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">👥 Bệnh nhân của tôi</div>
            <div class="stat-value">45</div>
            <div class="stat-trend">✓ 12 bệnh nhân mới</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">⭐ Đánh giá</div>
            <div class="stat-value">4.8</div>
            <div class="stat-trend">✓ Xuất sắc</div>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Lịch khám hôm nay</h3>
            <a href="{{ route('doctor.appointments') }}" class="card-action">Xem tất cả →</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Bệnh nhân</th>
                    <th>Dịch vụ</th>
                    <th>Giờ khám</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nguyễn Văn A</td>
                    <td>Lấy cao răng</td>
                    <td>08:00</td>
                    <td><span class="badge badge-success">✓ Hoàn thành</span></td>
                </tr>
                <tr>
                    <td>Phạm Thị C</td>
                    <td>Trám răng</td>
                    <td>10:45</td>
                    <td><span class="badge badge-warning">⏳ Đang thực hiện</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h3>⚡ Thao tác nhanh</h3>
        <div class="quick-actions">
            <a href="{{ route('doctor.appointments.create') }}" class="btn btn-primary">+ Tạo lịch khám</a>
            <a href="{{ route('doctor.patients.create') }}" class="btn btn-primary">+ Thêm bệnh nhân</a>
            <a href="{{ route('doctor.schedule.create') }}" class="btn btn-primary">📅 Quản lý ca làm việc</a>
        </div>
    </div>
@endsection