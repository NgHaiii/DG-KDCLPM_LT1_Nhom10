@extends('layouts.employee-layout')

@section('title', 'Tra cứu hồ sơ bệnh nhân')
@section('page-title', 'Tra cứu hồ sơ bệnh nhân')
@section('page-subtitle', 'Tìm nhanh hồ sơ để hỗ trợ tiếp nhận và điều phối khám bệnh')

@section('content')
<style>
    .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:20px; }
    .stat, .search-box, .profile-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,.06); }
    .stat { padding:18px; display:flex; gap:14px; align-items:center; }
    .stat-icon { width:48px; height:48px; border-radius:14px; display:grid; place-items:center; background:#e0f2fe; color:#0284c7; font-size:24px; }
    .stat-label { color:#64748b; font-weight:800; font-size:13px; }
    .stat-value { color:#0f172a; font-weight:900; font-size:28px; }
    .search-box { padding:18px; margin-bottom:20px; }
    .search-grid { display:grid; grid-template-columns:2fr 1fr auto; gap:10px; align-items:end; }
    .form-label { font-weight:800; color:#0f172a; font-size:13px; margin-bottom:6px; display:block; }
    .form-control { width:100%; border:1px solid #dbe3ef; border-radius:12px; padding:11px 12px; }
    .btn-main { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; background:#0ea5e9; color:#fff; border-radius:12px; padding:11px 15px; font-weight:900; text-decoration:none; cursor:pointer; }
    .btn-light { background:#fff; border:1px solid #dbe3ef; color:#0f172a; }
    .profile-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:16px; }
    .profile-card { padding:18px; }
    .profile-name { font-size:18px; font-weight:900; color:#0f172a; }
    .profile-meta { display:grid; gap:6px; color:#64748b; font-size:13px; margin-top:8px; }
    .visit-box { border:1px solid #e2e8f0; background:#f8fafc; border-radius:14px; padding:12px; margin-top:12px; }
    .visit-title { font-weight:900; color:#0f172a; margin-bottom:6px; }
    .visit-line { color:#64748b; font-size:13px; line-height:1.5; }
    .card-actions { display:flex; justify-content:flex-end; margin-top:14px; }
    .empty-box { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:36px; text-align:center; color:#64748b; }
    .pagination-wrap { margin-top:18px; }
    @media(max-width:800px){ .search-grid{grid-template-columns:1fr;} }
</style>

<div class="stats">
    <div class="stat">
        <div class="stat-icon"><i class="ri-calendar-check-line"></i></div>
        <div>
            <div class="stat-label">Lượt khám hôm nay</div>
            <div class="stat-value">{{ $todayVisitsCount }}</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon"><i class="ri-hourglass-line"></i></div>
        <div>
            <div class="stat-label">Đang chờ</div>
            <div class="stat-value">{{ $waitingCount }}</div>
        </div>
    </div>

    <div class="stat">
        <div class="stat-icon"><i class="ri-check-double-line"></i></div>
        <div>
            <div class="stat-label">Hoàn thành hôm nay</div>
            <div class="stat-value">{{ $completedTodayCount }}</div>
        </div>
    </div>
</div>

<div class="search-box">
    <form method="GET" action="{{ route('employees.patient-profiles.index') }}">
        <div class="search-grid">
            <div>
                <label class="form-label">Tìm hồ sơ</label>
                <input type="text"
                       name="keyword"
                       value="{{ $keyword }}"
                       class="form-control"
                       placeholder="Nhập tên, SĐT, email, CCCD...">
            </div>

            <div>
                <label class="form-label">Nguồn</label>
                <select name="source" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="online" @selected($source === 'online')>Online</option>
                    <option value="offline" @selected($source === 'offline')>Trực tiếp</option>
                </select>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn-main">
                    <i class="ri-search-line"></i>
                    Tìm
                </button>

                <a href="{{ route('employees.patient-profiles.index') }}" class="btn-main btn-light">
                    Xóa
                </a>
            </div>
        </div>
    </form>
</div>

@if($profiles->isEmpty())
    <div class="empty-box">Không tìm thấy hồ sơ phù hợp.</div>
@else
    <div class="profile-grid">
        @foreach($profiles as $profile)
            @php
                $latestAppointment = $profile->appointments->first();
            @endphp

            <div class="profile-card">
                <div class="profile-name">{{ $profile->full_name ?: 'Bệnh nhân #' . $profile->id }}</div>

                <div class="profile-meta">
                    <span><i class="ri-phone-line"></i> {{ $profile->phone ?: 'Chưa có SĐT' }}</span>
                    <span><i class="ri-id-card-line"></i> {{ $profile->identity_number ?: 'Chưa có CCCD' }}</span>
                    <span><i class="ri-map-pin-line"></i> {{ $profile->address ?: 'Chưa có địa chỉ' }}</span>
                </div>

                <div class="visit-box">
                    <div class="visit-title">Thông tin khám gần nhất</div>

                    @if($latestAppointment)
                        <div class="visit-line">
                            {{ $latestAppointment->appointment_date?->format('d/m/Y H:i') }} -
                            {{ $latestAppointment->service?->name ?? 'Dịch vụ' }}
                        </div>

                        <div class="visit-line">
                            Bác sĩ: {{ $latestAppointment->doctor?->name ?? 'Chưa rõ' }}
                        </div>

                        <div class="visit-line">
                            Trạng thái: {{ $latestAppointment->status_label ?? $latestAppointment->status }}
                        </div>
                    @else
                        <div class="visit-line">Chưa có lượt khám.</div>
                    @endif
                </div>

                <div class="card-actions">
                    <a href="{{ route('employees.patient-profiles.show', $profile->id) }}" class="btn-main">
                        <i class="ri-eye-line"></i>
                        Xem hồ sơ
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination-wrap">
        {{ $profiles->links() }}
    </div>
@endif
@endsection