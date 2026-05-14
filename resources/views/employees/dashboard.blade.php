@extends('layouts.employee-layout')

@section('title', 'Dashboard Nhân viên')
@section('page-title', 'Chào mừng, ' . auth()->user()->name)
@section('page-subtitle', 'Tổng quan hoạt động hôm nay')

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
        border-left: 4px solid #4f46e5;
    }
    
    .stat-label {
        color: #999;
        font-size: 13px;
        margin-bottom: 8px;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: #f9fafb;
    }
    
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

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }
</style>
@endsection

@section('content')
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">📥 Bệnh nhân tiếp nhận hôm nay</div>
            <div class="stat-value">12</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">📅 Lịch khám đặt được</div>
            <div class="stat-value">8</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">💰 Doanh thu hôm nay</div>
            <div class="stat-value">8.5M</div>
        </div>
    </div>

    <!-- Today's Queue -->
    <div class="card">
        <h3>📋 Danh sách chờ hôm nay</h3>
        <table>
            <thead>
                <tr>
                    <th>Bệnh nhân</th>
                    <th>Số điện thoại</th>
                    <th>Dịch vụ</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nguyễn Văn A</td>
                    <td>0901234567</td>
                    <td>Lấy cao răng</td>
                    <td><span class="badge badge-warning">Chờ</span></td>
                </tr>
                <tr>
                    <td>Phạm Thị C</td>
                    <td>0987654321</td>
                    <td>Trám răng</td>
                    <td><span class="badge badge-success">Gọi vào</span></td>
                </tr>
                <tr>
                    <td>Trần Minh D</td>
                    <td>0912345678</td>
                    <td>Nhổ răng</td>
                    <td><span class="badge badge-danger">Hoãn</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Recent Tasks -->
    <div class="card">
        <h3>✅ Nhiệm vụ gần đây</h3>
        <table>
            <thead>
                <tr>
                    <th>Nhiệm vụ</th>
                    <th>Ngày giao</th>
                    <th>Hạn chót</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Chuẩn bị dụng cụ phòng khám</td>
                    <td>14/05/2026</td>
                    <td>14/05/2026</td>
                    <td><span class="badge badge-success">Hoàn thành</span></td>
                </tr>
                <tr>
                    <td>Ghi chép hồ sơ bệnh nhân</td>
                    <td>14/05/2026</td>
                    <td>15/05/2026</td>
                    <td><span class="badge badge-warning">Đang thực hiện</span></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection