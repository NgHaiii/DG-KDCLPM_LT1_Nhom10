@extends('layouts.admin-layout')

@section('title', 'Dashboard')

@section('page-title', 'Tổng quan hệ thống')
@section('page-subtitle', 'Theo dõi toàn bộ hoạt động phòng khám')

@section('styles')
<style>
    /* ===== Stat Cards ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 22px 24px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 18px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.09);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-icon.blue  { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
    .stat-icon.green { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669; }
    .stat-icon.amber { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
    .stat-icon.violet{ background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed; }

    .stat-body { flex: 1; min-width: 0; }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    .stat-trend {
        font-size: 12px;
        font-weight: 600;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-trend.up   { color: #10b981; }
    .stat-trend.info { color: #3b82f6; }

    /* ===== Charts ===== */
    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 28px;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
    }

    .chart-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .chart-card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-card-title i {
        color: #8b5cf6;
        font-size: 18px;
    }

    .chart-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 100px;
        background: #f1f5f9;
        color: #64748b;
    }

    /* ===== Table ===== */
    .table-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
    }

    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 12px;
    }

    .table-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-title i { color: #8b5cf6; font-size: 18px; }

    .table-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .table-search {
        position: relative;
    }

    .table-search input {
        height: 36px;
        padding: 0 12px 0 34px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        color: #334155;
        background: #f8fafc;
        outline: none;
        transition: all 0.2s;
        width: 200px;
    }

    .table-search input:focus {
        border-color: #8b5cf6;
        background: white;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .table-search i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #94a3b8;
    }

    .view-all-link {
        font-size: 13px;
        font-weight: 600;
        color: #8b5cf6;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s;
    }

    .view-all-link:hover { color: #7c3aed; }

    table { width: 100%; border-collapse: collapse; }

    thead { background: #f8fafc; }

    th {
        padding: 10px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        border-bottom: 1px solid #f1f5f9;
    }

    td {
        padding: 13px 14px;
        border-bottom: 1px solid #f8fafc;
        font-size: 13.5px;
        color: #334155;
        vertical-align: middle;
    }

    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #fafafa; }

    .patient-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .row-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: white;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-info    { background: #dbeafe; color: #1e40af; }
    .badge-danger  { background: #fee2e2; color: #7f1d1d; }

    .action-btns { display: flex; gap: 6px; }

    .action-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1px solid #e2e8f0;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #64748b;
        transition: all 0.2s;
    }

    .action-btn:hover { border-color: #8b5cf6; color: #8b5cf6; background: #faf5ff; }
    .action-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: #fff1f1; }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 900px) {
        .charts-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr; }
        .table-actions { display: none; }
    }
</style>
@endsection

@section('content')

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="ri-user-heart-line"></i></div>
            <div class="stat-body">
                <div class="stat-label">Tổng bệnh nhân</div>
                <div class="stat-value">1.248</div>
                <div class="stat-trend up">
                    <i class="ri-arrow-up-line"></i> +8.2% so với tháng trước
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green"><i class="ri-stethoscope-line"></i></div>
            <div class="stat-body">
                <div class="stat-label">Bác sĩ</div>
                <div class="stat-value">12</div>
                <div class="stat-trend info">
                    <i class="ri-checkbox-circle-line"></i> Hoạt động bình thường
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon amber"><i class="ri-calendar-line"></i></div>
            <div class="stat-body">
                <div class="stat-label">Lịch khám tháng</div>
                <div class="stat-value">86</div>
                <div class="stat-trend info">
                    <i class="ri-time-line"></i> 25 lịch hôm nay
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon violet"><i class="ri-money-dollar-circle-line"></i></div>
            <div class="stat-body">
                <div class="stat-label">Doanh thu tháng</div>
                <div class="stat-value">245.8M</div>
                <div class="stat-trend up">
                    <i class="ri-arrow-up-line"></i> +12.4% tăng trưởng
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-card-title">
                    <i class="ri-bar-chart-line"></i> Doanh thu 12 tháng
                </div>
                <span class="chart-badge">Triệu VND</span>
            </div>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-card-title">
                    <i class="ri-pie-chart-line"></i> Phân bổ dịch vụ
                </div>
                <span class="chart-badge">Tháng này</span>
            </div>
            <canvas id="serviceChart" height="180"></canvas>
        </div>
    </div>

    <!-- Today's Appointments Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class="ri-calendar-todo-line"></i> Lịch khám hôm nay
            </div>
            <div class="table-actions">
                <div class="table-search">
                    <i class="ri-search-line"></i>
                    <input type="text" placeholder="Tìm bệnh nhân...">
                </div>
                <a href="#" class="view-all-link">
                    Xem tất cả <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Bệnh nhân</th>
                    <th>Bác sĩ</th>
                    <th>Dịch vụ</th>
                    <th>Giờ</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span style="font-family:monospace;font-size:12px;color:#64748b;">#AP001</span></td>
                    <td>
                        <div class="patient-cell">
                            <div class="row-avatar" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">N</div>
                            <span style="font-weight:600;">Nguyễn Văn A</span>
                        </div>
                    </td>
                    <td>Dr. Trần B</td>
                    <td>Lấy cao răng</td>
                    <td><span style="font-weight:600;">08:00</span></td>
                    <td><span class="badge badge-success"><i class="ri-checkbox-circle-line"></i> Hoàn thành</span></td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn" title="Xem"><i class="ri-eye-line"></i></button>
                            <button class="action-btn" title="Sửa"><i class="ri-edit-line"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><span style="font-family:monospace;font-size:12px;color:#64748b;">#AP002</span></td>
                    <td>
                        <div class="patient-cell">
                            <div class="row-avatar" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">P</div>
                            <span style="font-weight:600;">Phạm Thị C</span>
                        </div>
                    </td>
                    <td>Dr. Lê D</td>
                    <td>Nhổ răng</td>
                    <td><span style="font-weight:600;">09:30</span></td>
                    <td><span class="badge badge-warning"><i class="ri-loader-2-line"></i> Đang thực hiện</span></td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn" title="Xem"><i class="ri-eye-line"></i></button>
                            <button class="action-btn" title="Sửa"><i class="ri-edit-line"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><span style="font-family:monospace;font-size:12px;color:#64748b;">#AP003</span></td>
                    <td>
                        <div class="patient-cell">
                            <div class="row-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">H</div>
                            <span style="font-weight:600;">Hoàng Văn E</span>
                        </div>
                    </td>
                    <td>Dr. Trần B</td>
                    <td>Trám răng</td>
                    <td><span style="font-weight:600;">10:45</span></td>
                    <td><span class="badge badge-info"><i class="ri-time-line"></i> Chờ khám</span></td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn" title="Xem"><i class="ri-eye-line"></i></button>
                            <button class="action-btn" title="Sửa"><i class="ri-edit-line"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><span style="font-family:monospace;font-size:12px;color:#64748b;">#AP004</span></td>
                    <td>
                        <div class="patient-cell">
                            <div class="row-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">L</div>
                            <span style="font-weight:600;">Lê Thị F</span>
                        </div>
                    </td>
                    <td>Dr. Nguyễn G</td>
                    <td>Bọc sứ</td>
                    <td><span style="font-weight:600;">14:00</span></td>
                    <td><span class="badge badge-info"><i class="ri-time-line"></i> Chờ khám</span></td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn" title="Xem"><i class="ri-eye-line"></i></button>
                            <button class="action-btn" title="Sửa"><i class="ri-edit-line"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    // Revenue Line Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 280);
    revenueGradient.addColorStop(0, 'rgba(139, 92, 246, 0.18)');
    revenueGradient.addColorStop(1, 'rgba(139, 92, 246, 0.01)');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
            datasets: [{
                label: 'Doanh thu (triệu VND)',
                data: [185, 210, 195, 230, 215, 240, 228, 255, 242, 268, 245, 280],
                borderColor: '#8b5cf6',
                borderWidth: 2.5,
                backgroundColor: revenueGradient,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#0f172a',
                    bodyColor: '#64748b',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
                    callbacks: {
                        label: ctx => ` ${ctx.raw} triệu VND`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: {
                        font: { size: 12 },
                        color: '#94a3b8',
                        callback: v => v + 'M'
                    }
                }
            }
        }
    });

    // Service Doughnut Chart
    new Chart(document.getElementById('serviceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Trám răng', 'Nhổ răng', 'Lấy cao răng', 'Bọc sứ', 'Khác'],
            datasets: [{
                data: [32, 18, 25, 15, 10],
                backgroundColor: ['#8b5cf6','#3b82f6','#10b981','#f59e0b','#94a3b8'],
                borderWidth: 3,
                borderColor: 'white',
                hoverBorderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 14,
                        font: { size: 12 },
                        color: '#475569',
                        usePointStyle: true,
                        pointStyleWidth: 8,
                    }
                },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#0f172a',
                    bodyColor: '#64748b',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw}%`
                    }
                }
            }
        }
    });
</script>
@endsection
