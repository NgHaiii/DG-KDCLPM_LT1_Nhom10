<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - DentalCare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .container { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            overflow-y: auto;
        }
        .sidebar h2 { font-size: 20px; margin-bottom: 30px; }
        .nav-menu { list-style: none; }
        .nav-item {
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-item:hover { background: rgba(255, 255, 255, 0.2); }
        .nav-item.active { background: rgba(255, 255, 255, 0.3); }

        /* Main content */
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 { font-size: 28px; color: #333; }
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
        }
        .stat-card.blue { border-left-color: #667eea; }
        .stat-card.green { border-left-color: #10b981; }
        .stat-card.amber { border-left-color: #f59e0b; }
        .stat-card.red { border-left-color: #ef4444; }
        .stat-label { color: #999; font-size: 13px; margin-bottom: 8px; }
        .stat-value { font-size: 32px; font-weight: bold; color: #333; }
        .stat-trend { color: #10b981; font-size: 12px; margin-top: 8px; }

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
        .chart-card h3 { margin-bottom: 20px; color: #333; font-size: 16px; }

        /* Table */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
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
        tr:hover { background: #f9fafb; }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #7f1d1d; }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4); }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .stats-grid { grid-template-columns: 1fr; }
            .main { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>🦷 DentalCare</h2>
            <ul class="nav-menu">
                <li class="nav-item active">📊 Dashboard</li>
                <li class="nav-item">👥 Quản lý người dùng</li>
                <li class="nav-item">🩺 Quản lý bác sĩ</li>
                <li class="nav-item">👨‍💼 Quản lý nhân viên</li>
                <li class="nav-item">🤝 Quản lý bệnh nhân</li>
                <li class="nav-item">❤️ Dịch vụ nha khoa</li>
                <li class="nav-item">💰 Bảng giá</li>
                <li class="nav-item">📅 Lịch khám</li>
                <li class="nav-item">📈 Báo cáo thống kê</li>
                <li class="nav-item">⚙️ Cài đặt hệ thống</li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Tổng quan hệ thống</h1>
                    <p style="color: #999; margin-top: 5px;">Theo dõi toàn bộ hoạt động phòng khám</p>
                </div>
                <div class="user-info">
                    <div style="text-align: right;">
                        <p style="font-weight: 600;">{{ auth()->user()->name }}</p>
                        <p style="font-size: 12px; color: #999;">Quản trị viên</p>
                    </div>
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">Đăng xuất</button>
                    </form>
                </div>
            </div>

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
                    <div style="height: 280px; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                        [Biểu đồ doanh thu]
                    </div>
                </div>
                <div class="chart-card">
                    <h3>📅 Lịch khám trong tuần</h3>
                    <div style="height: 280px; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                        [Biểu đồ lịch khám]
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="table-card">
                <h3 style="margin-bottom: 20px;">📋 Lịch khám hôm nay</h3>
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
            </div>
        </div>
    </div>
</body>
</html>