@extends('layouts.admin-layout')

@section('title', 'Dashboard')

@section('page-title', 'Tổng quan hệ thống')
@section('page-subtitle', 'Theo dõi toàn bộ hoạt động phòng khám')

@section('header-actions')
    <div class="user-info">
        <div style="text-align: right;">
            <p style="font-weight: 600; margin: 0;">{{ auth()->user()->name }}</p>
            <p style="font-size: 12px; color: #999; margin: 0;">Quản trị viên</p>
        </div>
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
            @csrf
            <button type="submit" class="logout-btn">Đăng xuất</button>
        </form>
    </div>
@endsection

@section('styles')
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Stats Cards */
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
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card.blue {
            border-left-color: #667eea;
        }

        .stat-card.green {
            border-left-color: #10b981;
        }

        .stat-card.amber {
            border-left-color: #f59e0b;
        }

        .stat-card.red {
            border-left-color: #ef4444;
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
            margin: 10px 0;
        }

        .stat-trend {
            color: #10b981;
            font-size: 12px;
            margin-top: 8px;
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .chart-card h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 16px;
        }

        /* Chart Placeholder */
        .chart-placeholder {
            height: 280px;
            background: #f9fafb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
            border: 1px dashed #e5e7eb;
        }

        /* Table Card */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .table-card h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 16px;
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

        tbody tr:hover {
            background: #f9fafb;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
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
            color: #7f1d1d;
        }

        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .user-info {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .user-info > div:first-child {
                text-align: center;
                width: 100%;
            }

            .logout-btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Tổng bệnh nhân</div>
            <div class="stat-value">1.248</div>
            <div class="stat-trend">✓ +8.2% so với tháng trước</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Bác sĩ</div>
            <div class="stat-value">12</div>
            <div class="stat-trend">✓ Hoạt động bình thường</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-label">Lịch khám tháng</div>
            <div class="stat-value">86</div>
            <div class="stat-trend">✓ 25 lịch hôm nay</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Doanh thu tháng</div>
            <div class="stat-value">245.8M</div>
            <div class="stat-trend">✓ +12.4% tăng trưởng</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>📊 Doanh thu 12 tháng (triệu VND)</h3>
            <div class="chart-placeholder">
                [Biểu đồ doanh thu]
            </div>
        </div>
        <div class="chart-card">
            <h3>📅 Lịch khám trong tuần</h3>
            <div class="chart-placeholder">
                [Biểu đồ lịch khám]
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="table-card">
        <h3>📋 Lịch khám hôm nay</h3>
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Bệnh nhân</th>
                    <th>Bác sĩ</th>
                    <th>Dịch vụ</th>
                    <th>Giờ</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>AP001</td>
                    <td>Nguyễn Văn A</td>
                    <td>Dr. Trần B</td>
                    <td>Lấy cao răng</td>
                    <td>08:00</td>
                    <td><span class="badge badge-success">Hoàn thành</span></td>
                </tr>
                <tr>
                    <td>AP002</td>
                    <td>Phạm Thị C</td>
                    <td>Dr. Lê D</td>
                    <td>Nhổ răng</td>
                    <td>09:30</td>
                    <td><span class="badge badge-warning">Đang thực hiện</span></td>
                </tr>
                <tr>
                    <td>AP003</td>
                    <td>Hoàng Văn E</td>
                    <td>Dr. Trần B</td>
                    <td>Trám răng</td>
                    <td>10:45</td>
                    <td><span class="badge badge-warning">Chờ</span></td>
                </tr>
            </tbody>
        </table>
    </div>@endsection