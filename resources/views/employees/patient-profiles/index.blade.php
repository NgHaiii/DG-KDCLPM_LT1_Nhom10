@extends('layouts.employee-layout')

@section('title', 'Hồ sơ bệnh nhân')
@section('page-title', 'Hồ sơ bệnh nhân')
@section('page-subtitle', 'Tra cứu và quản lý thông tin bệnh nhân online/offline')

@section('styles')
<style>
    .toolbar {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: 18px;
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .search-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;
    }

    .form-control {
        padding: 11px 13px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background: white;
        color: var(--text-main);
        font-family: var(--font-body);
        font-size: 14px;
        outline: none;
    }

    .search-input {
        min-width: 320px;
        flex: 1;
    }

    .profile-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .profile-table {
        width: 100%;
        border-collapse: collapse;
    }

    .profile-table th,
    .profile-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        text-align: left;
        vertical-align: top;
    }

    .profile-table th {
        background: #f8fafc;
        color: var(--text-main);
        font-weight: 800;
        font-size: 13px;
    }

    .patient-name {
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .muted {
        color: var(--text-muted);
        font-size: 13px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: var(--radius-full);
        background: var(--primary-light);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
    }

    .empty {
        padding: 42px 18px;
        text-align: center;
        color: var(--text-muted);
    }

    .pagination-wrap {
        padding: 16px;
    }

    @media (max-width: 900px) {
        .profile-table,
        .profile-table thead,
        .profile-table tbody,
        .profile-table th,
        .profile-table td,
        .profile-table tr {
            display: block;
        }

        .profile-table thead {
            display: none;
        }

        .profile-table tr {
            border-bottom: 1px solid var(--border-color);
            padding: 12px;
        }

        .profile-table td {
            border-bottom: none;
            padding: 7px 0;
        }

        .search-input {
            min-width: 100%;
        }
    }
</style>
@endsection

@section('header-actions')
<a href="{{ route('employees.reception.index') }}" class="btn btn-primary">
    <i class="ri-user-add-line"></i>
    Tiếp nhận
</a>
@endsection

@section('content')
<div class="toolbar">
    <form method="GET" action="{{ route('employees.patient-profiles.index') }}" class="search-form">
        <input type="text"
               name="keyword"
               value="{{ $keyword ?? '' }}"
               class="form-control search-input"
               placeholder="Tìm theo tên, số điện thoại, email, CCCD...">

        <button type="submit" class="btn btn-secondary">
            <i class="ri-search-line"></i>
            Tìm kiếm
        </button>

        @if(!empty($keyword))
            <a href="{{ route('employees.patient-profiles.index') }}" class="btn btn-secondary">
                <i class="ri-refresh-line"></i>
                Xóa lọc
            </a>
        @endif
    </form>
</div>

<div class="profile-card">
    @if($profiles->count())
        <table class="profile-table">
            <thead>
                <tr>
                    <th>Bệnh nhân</th>
                    <th>Liên hệ</th>
                    <th>Thông tin cá nhân</th>
                    <th>Nguồn hồ sơ</th>
                    <th>Lần khám gần nhất</th>
                </tr>
            </thead>

            <tbody>
                @foreach($profiles as $profile)
                    <tr>
                        <td>
                            <div class="patient-name">{{ $profile->full_name }}</div>
                            <div class="muted">Mã hồ sơ: #{{ $profile->id }}</div>
                        </td>

                        <td>
                            <div><strong>SĐT:</strong> {{ $profile->phone }}</div>
                            <div class="muted">{{ $profile->email ?: 'Chưa có email' }}</div>
                        </td>

                        <td>
                            <div><strong>Ngày sinh:</strong> {{ $profile->dob ? $profile->dob->format('d/m/Y') : 'Chưa cập nhật' }}</div>
                            <div><strong>Giới tính:</strong> {{ $profile->gender_label }}</div>
                            <div class="muted">{{ $profile->address ?: 'Chưa có địa chỉ' }}</div>
                        </td>

                        <td>
                            <span class="badge">{{ $profile->source_label }}</span>
                            @if($profile->is_temporary)
                                <div class="muted" style="margin-top: 6px;">Hồ sơ tạm</div>
                            @endif
                        </td>

                        <td>
                            {{ $profile->last_visit_at ? $profile->last_visit_at->format('d/m/Y H:i') : 'Chưa có lượt khám' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrap">
            {{ $profiles->links() }}
        </div>
    @else
        <div class="empty">
            <i class="ri-user-search-line" style="font-size: 40px; color: var(--primary); display: block; margin-bottom: 10px;"></i>
            Không tìm thấy hồ sơ bệnh nhân phù hợp.
        </div>
    @endif
</div>
@endsection