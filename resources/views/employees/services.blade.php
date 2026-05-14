@extends('layouts.employee-layout')

@section('title', 'Bảng giá dịch vụ')
@section('page-title', 'Bảng Giá Dịch Vụ')
@section('page-subtitle', 'Xem danh sách dịch vụ và giá hiện tại')

@section('styles')
<style>
    .service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .service-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        padding: 20px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .service-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-5px);
    }

    .service-name {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }

    .service-description {
        font-size: 13px;
        color: #999;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .service-price {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .price-label {
        font-size: 12px;
        color: #666;
        font-weight: 500;
    }

    .price-value {
        font-size: 20px;
        font-weight: bold;
        color: #667eea;
    }

    .price-unit {
        font-size: 12px;
        color: #999;
    }

    .table-wrapper {
        overflow-x: auto;
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

    .service-name-cell {
        font-weight: 600;
        color: #333;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        background: #d1fae5;
        color: #065f46;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .filter-section {
        margin-bottom: 30px;
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .search-box {
        flex: 1;
        max-width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .view-toggle {
        display: flex;
        gap: 10px;
    }

    .toggle-btn {
        padding: 8px 15px;
        border: 1px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .toggle-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
</style>
@endsection

@section('content')
    <!-- Filter Section -->
    <div class="filter-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Tìm dịch vụ...">
        </div>
        <div class="view-toggle">
            <button class="toggle-btn active" onclick="showGridView()">📊 Lưới</button>
            <button class="toggle-btn" onclick="showTableView()">📋 Bảng</button>
        </div>
    </div>

    <!-- Grid View -->
    <div id="gridView" class="service-grid">
        @forelse($services as $service)
            <div class="service-card" data-service="{{ strtolower($service->name) }}">
                <div class="service-name">{{ $service->name }}</div>
                <div class="service-description">
                    {{ $service->description ?? 'Không có mô tả' }}
                </div>
                <div class="service-price">
                    <div>
                        <div class="price-label">Giá:</div>
                    </div>
                    <div style="text-align: right;">
                        @if($service->currentPrice)
                            <div class="price-value">
                                {{ number_format($service->currentPrice->price, 0, ',', '.') }}
                                <span class="price-unit">đ</span>
                            </div>
                        @else
                            <div class="price-value" style="color: #999; font-size: 14px;">
                                Chưa có giá
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="no-data" style="grid-column: 1 / -1;">
                <p>📭 Chưa có dịch vụ nào</p>
            </div>
        @endforelse
    </div>

    <!-- Table View -->
    <div id="tableView" class="card" style="display: none;">
        <h3>📋 Danh sách dịch vụ</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên dịch vụ</th>
                        <th>Mô tả</th>
                        <th>Giá hiện tại</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $key => $service)
                        <tr data-service="{{ strtolower($service->name) }}">
                            <td>{{ $key + 1 }}</td>
                            <td class="service-name-cell">{{ $service->name }}</td>
                            <td>{{ $service->description ?? '-' }}</td>
                            <td>
                                @if($service->currentPrice)
                                    <strong style="color: #667eea;">
                                        {{ number_format($service->currentPrice->price, 0, ',', '.') }} đ
                                    </strong>
                                @else
                                    <span style="color: #999;">Chưa có giá</span>
                                @endif
                            </td>
                            <td>
                                @if($service->currentPrice)
                                    <span class="badge">Có sẵn</span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Chưa có giá</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="no-data">
                                📭 Chưa có dịch vụ nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stats -->
    <div class="card" style="margin-top: 30px;">
        <h3>📊 Thống kê</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="padding: 15px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 12px; color: #999; margin-bottom: 5px;">Tổng dịch vụ</div>
                <div style="font-size: 24px; font-weight: bold; color: #667eea;">{{ $services->count() }}</div>
            </div>
            <div style="padding: 15px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 12px; color: #999; margin-bottom: 5px;">Dịch vụ có giá</div>
                <div style="font-size: 24px; font-weight: bold; color: #667eea;">
                    {{ $services->filter(fn($s) => $s->currentPrice)->count() }}
                </div>
            </div>
            <div style="padding: 15px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 12px; color: #999; margin-bottom: 5px;">Giá cao nhất</div>
                <div style="font-size: 24px; font-weight: bold; color: #667eea;">
                   @php
    $maxPrice = $services->map(fn($s) => $s->currentPrice?->price)->filter()->max();
@endphp
{{ $maxPrice ? number_format($maxPrice, 0, ',', '.') . ' đ' : '-' }}
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        const query = e.target.value.toLowerCase();
        
        // Search in grid view
        document.querySelectorAll('#gridView .service-card').forEach(card => {
            if (card.dataset.service.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Search in table view
        document.querySelectorAll('#tableView table tbody tr').forEach(row => {
            if (row.dataset.service.includes(query)) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // View toggle
    function showGridView() {
        document.getElementById('gridView').style.display = 'grid';
        document.getElementById('tableView').style.display = 'none';
        document.querySelectorAll('.toggle-btn')[0].classList.add('active');
        document.querySelectorAll('.toggle-btn')[1].classList.remove('active');
    }

    function showTableView() {
        document.getElementById('gridView').style.display = 'none';
        document.getElementById('tableView').style.display = 'block';
        document.querySelectorAll('.toggle-btn')[0].classList.remove('active');
        document.querySelectorAll('.toggle-btn')[1].classList.add('active');
    }
</script>
@endsection