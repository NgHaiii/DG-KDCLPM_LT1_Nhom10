<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bác sĩ - DentalCare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .container { display: flex; min-height: 100vh; }
        
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
        }
        .nav-item:hover { background: rgba(255, 255, 255, 0.2); }
        .nav-item.active { background: rgba(255, 255, 255, 0.3); }

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
        .stat-label { color: #999; font-size: 13px; margin-bottom: 8px; }
        .stat-value { font-size: 32px; font-weight: bold; color: #333; }
        .stat-trend { color: #10b981; font-size: 12px; margin-top: 8px; }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
        .card h3 { margin-bottom: 20px; color: #333; font-size: 16px; }

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

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>🦷 DentalCare</h2>
            <ul class="nav-menu">
                <li class="nav-item active">📊 Dashboard</li>
                <li class="nav-item">📅 Ca làm việc</li>
                <li class="nav-item">🗓️ Lịch khám</li>
                <li class="nav-item">🤝 Bệnh nhân của tôi</li>
                <li class="nav-item">📝 Hồ sơ khám</li>
                <li class="nav-item">⚙️ Cài đặt cá nhân</li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <div>
                    <h1>Chào mừng, Bác sĩ {{ auth()->user()->name }}</h1>
                    <p style="color: #999; margin-top: 5px;">Quản lý lịch khám và bệnh nhân của bạn</p>
                </div>
                <div class="user-info">
                    <div style="text-align: right;">
                        <p style="font-weight: 600;">{{ auth()->user()->name }}</p>
                        <p style="font-size: 12px; color: #999;">Bác sĩ</p>
                    </div>
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">Đăng xuất</button>
                    </form>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Lịch khám hôm nay</div>
                    <div class="stat-value">5</div>
                    <div class="stat-trend">✓ 3 hoàn thành</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Bệnh nhân của tôi</div>
                    <div class="stat-value">45</div>
                    <div class="stat-trend">✓ 12 bệnh nhân mới</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Đánh giá trung bình</div>
                    <div class="stat-value">4.8⭐</div>
                    <div class="stat-trend">✓ Rất tốt</div>
                </div>
            </div>

            <div class="card">
                <h3>📋 Lịch khám hôm nay</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Bệnh nhân</th>
                            <th>Dịch vụ</th>
                            <th>Giờ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Nguyễn Văn A</td>
                            <td>Lấy cao răng</td>
                            <td>08:00</td>
                            <td><span class="badge badge-success">Hoàn thành</span></td>
                        </tr>
                        <tr>
                            <td>Phạm Thị C</td>
                            <td>Trám răng</td>
                            <td>10:45</td>
                            <td><span class="badge badge-warning">Đang thực hiện</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>