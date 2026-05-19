@extends('layouts.employee-layout')

@section('title', 'Đăng ký ca làm việc & ngày nghỉ')

@section('page-title', 'Quản lý lịch trình')
@section('page-subtitle', 'Đăng ký ca làm việc hoặc xin ngày nghỉ')

@section('content')

<style>
    /* Stats Cards */
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

    .stat-card.pending { border-left-color: #f59e0b; }
    .stat-card.approved { border-left-color: #10b981; }
    .stat-card.offday { border-left-color: #0ea5e9; }

    .stat-card p:first-child {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .stat-card p:last-child {
        font-size: 2.25rem;
        font-weight: 700;
    }

    .stat-card.pending p:last-child { color: #f59e0b; }
    .stat-card.approved p:last-child { color: #10b981; }
    .stat-card.offday p:last-child { color: #0ea5e9; }

    /* Tabs Navigation */
    .tabs-nav {
        display: flex;
        gap: 1rem;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 2rem;
        background: white;
        padding: 0 1rem;
        border-radius: 12px 12px 0 0;
    }

    .tab-btn {
        padding: 1rem 1.5rem;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-btn:hover {
        color: #3b82f6;
    }

    .tab-btn.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    .tab-btn.active.green {
        color: #10b981;
        border-bottom-color: #10b981;
    }

    /* Tab Content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        padding: 2rem;
    }

    .form-body {
        margin: 0;
    }

    .form-body > div {
        margin-bottom: 1.25rem;
    }

    .form-body label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-body input,
    .form-body select,
    .form-body textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-body input:focus,
    .form-body select:focus,
    .form-body textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-body select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem;
        padding-right: 2.5rem;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .btn-submit {
        width: 100%;
        padding: 0.875rem 1rem;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-submit.blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    }

    .btn-submit.blue:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    }

    .btn-submit.green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .btn-submit.green:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    }

    .btn-submit:active {
        transform: scale(0.98);
    }

    /* Approved Schedules */
    .schedule-list {
        display: grid;
        gap: 1rem;
    }

    .schedule-item {
        padding: 1.25rem;
        background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
        border: 2px solid #0ea5e9;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .schedule-item:hover {
        border-color: #0284c7;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
    }

    .schedule-item h4 {
        font-weight: 700;
        color: #0c4a6e;
        margin-bottom: 0.5rem;
    }

    .schedule-item p {
        font-size: 0.875rem;
        color: #0c4a6e;
        margin: 0.25rem 0;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    /* Pending List */
    .pending-list-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        margin-top: 2rem;
    }

    .pending-list-header {
        padding: 1.25rem 1.5rem;
        font-size: 1.125rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .pending-list-body {
        padding: 1.5rem;
    }

    .pending-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #fef3c7 0%, #fef08a 100%);
        border: 2px solid #fde047;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }

    .pending-item:last-child {
        margin-bottom: 0;
    }

    .pending-item:hover {
        border-color: #f59e0b;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
    }

    .pending-item-left {
        flex: 1;
    }

    .pending-item-left p:first-child {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .pending-item-left p:last-child {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .pending-item-middle {
        margin: 0 1rem;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-cancel {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .tabs-nav {
            overflow-x: auto;
            padding: 0 0.5rem;
        }

        .tab-btn {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .pending-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .pending-item-middle {
            align-self: flex-start;
        }
    }
</style>

<!-- Stats -->
<div class="stats-container">
    <div class="stat-card pending">
        <p>⏳ Đơn ca chờ duyệt</p>
        <p>{{ $pendingRequests->count() }}</p>
    </div>

    <div class="stat-card approved">
        <p>✅ Ca được duyệt</p>
        <p>{{ $approvedSchedules->count() }}</p>
    </div>

    <div class="stat-card offday">
        <p>🏖️ Ngày nghỉ được duyệt</p>
        <p>{{ $approvedOffDays->count() }}</p>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-nav">
    <button class="tab-btn active" data-tab="tab-shift">📋 Đăng ký ca làm việc</button>
    <button class="tab-btn" data-tab="tab-offday">🏖️ Xin ngày nghỉ</button>
    <button class="tab-btn" data-tab="tab-approved">✅ Ca đã duyệt</button>
</div>

<!-- Tab 1: Shift Registration -->
<div id="tab-shift" class="tab-content active">
    <div class="form-card">
        <div class="form-body">
            <form action="{{ route('employee.schedule.store') }}" method="POST">
                @csrf

                <div>
                    <label>📅 Ngày làm việc</label>
                    <input type="date" name="work_date" required min="{{ now()->toDateString() }}">
                    @error('work_date')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label>⏰ Ca làm việc</label>
                    <select name="shift_id" required>
                        <option value="">-- Chọn ca --</option>
                        @foreach($availableShifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</option>
                        @endforeach
                    </select>
                    @error('shift_id')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-submit blue">✅ Đăng ký ca</button>
            </form>
        </div>
    </div>

    @if($pendingRequests->count() > 0)
        <div class="pending-list-container">
            <div class="pending-list-header">
                <span>⏳</span> Đơn chờ duyệt ({{ $pendingRequests->count() }})
            </div>
            
            <div class="pending-list-body">
                @foreach($pendingRequests as $request)
                    <div class="pending-item">
                        <div class="pending-item-left">
                            <p>{{ $request->shift->name }}</p>
                            <p>{{ $request->work_date->format('d/m/Y') }} • {{ $request->shift->start_time }} - {{ $request->shift->end_time }}</p>
                        </div>
                        <div class="pending-item-middle">
                            <span class="badge pending">Chờ duyệt</span>
                        </div>
                        <form action="{{ route('employee.schedule.cancel', $request->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-cancel">❌ Hủy</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Tab 2: Off-Day Request -->
<div id="tab-offday" class="tab-content">
    <div class="form-card">
        <div class="form-body">
            <form action="{{ route('employee.schedule.off-day.store') }}" method="POST">
                @csrf

                <div>
                    <label>📅 Từ ngày</label>
                    <input type="date" name="start_date" required min="{{ now()->toDateString() }}">
                </div>

                <div>
                    <label>📅 Đến ngày</label>
                    <input type="date" name="end_date" required min="{{ now()->toDateString() }}">
                </div>

                <div>
                    <label>📝 Lý do</label>
                    <textarea name="reason" rows="4" placeholder="Nhập lý do xin nghỉ..." required></textarea>
                </div>

                <button type="submit" class="btn-submit green">✅ Gửi đơn xin nghỉ</button>
            </form>
        </div>
    </div>

    @if($approvedOffDays->count() > 0)
        <div class="pending-list-container">
            <div class="pending-list-header">
                <span>✅</span> Ngày nghỉ đã duyệt ({{ $approvedOffDays->count() }})
            </div>
            
            <div class="pending-list-body">
                @foreach($approvedOffDays as $offday)
                    <div class="pending-item">
                        <div class="pending-item-left">
                            <p>{{ $offday->reason ?? 'Không có lý do' }}</p>
                            <p>{{ $offday->date->format('d/m/Y') }} • Đã duyệt</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Tab 3: Approved Schedules -->
<div id="tab-approved" class="tab-content">
    <div class="form-card">
        @if($approvedSchedules->count() > 0)
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: #111827;">📅 Danh sách ca đã duyệt</h3>
            <div class="schedule-list">
                @foreach($approvedSchedules as $schedule)
                    <div class="schedule-item">
                        <h4>{{ $schedule->shift->name }}</h4>
                        <p>📅 Ngày: {{ $schedule->work_date->format('d/m/Y') }}</p>
                        <p>⏰ Giờ: {{ $schedule->shift->start_time }} - {{ $schedule->shift->end_time }}</p>
                        <p>✅ Trạng thái: Đã duyệt</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <p>📭 Chưa có ca làm việc nào được duyệt</p>
            </div>
        @endif
    </div>
</div>

<script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            this.classList.add('active');
        });
    });
</script>

@endsection