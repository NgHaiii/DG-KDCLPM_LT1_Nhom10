@extends('layouts.admin-layout')

@section('title', 'Quản lý hồ sơ bệnh án')
@section('page-title', 'Quản lý hồ sơ bệnh án')
@section('page-subtitle', 'Tra cứu, lọc và theo dõi toàn bộ hồ sơ bệnh nhân trong hệ thống')

@section('content')
<style>
    .records-page {
        display: grid;
        gap: 22px;
    }

    .records-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 16px;
    }

    .stat-card,
    .filter-panel,
    .records-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07);
    }

    .stat-card {
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 104px;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 25px;
        flex-shrink: 0;
    }

    .stat-icon.blue { background: #e0f2fe; color: #0284c7; }
    .stat-icon.green { background: #dcfce7; color: #15803d; }
    .stat-icon.orange { background: #ffedd5; color: #c2410c; }
    .stat-icon.purple { background: #ede9fe; color: #6d28d9; }

    .stat-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .stat-value {
        color: #0f172a;
        font-size: 30px;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.8px;
    }

    .stat-note {
        margin-top: 6px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
    }

    .filter-panel {
        padding: 20px;
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .filter-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
    }

    .filter-title i {
        color: #0ea5e9;
        font-size: 22px;
    }

    .filter-status {
        min-height: 22px;
        color: #0284c7;
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: minmax(260px, 2fr) 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .field-label {
        display: block;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .form-control {
        width: 100%;
        height: 46px;
        border: 1px solid #dbe3ef;
        border-radius: 13px;
        padding: 0 13px;
        color: #0f172a;
        background: #fff;
        font-size: 14px;
        outline: none;
        transition: all 0.18s ease;
    }

    .form-control:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-action {
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 13px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.18s ease;
    }

    .btn-primary-soft {
        background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
        color: #fff;
        box-shadow: 0 10px 22px rgba(14, 165, 233, 0.25);
    }

    .btn-primary-soft:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(14, 165, 233, 0.32);
    }

    .btn-ghost {
        background: #fff;
        color: #0f172a;
        border-color: #dbe3ef;
    }

    .btn-ghost:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .records-panel {
        overflow: hidden;
        position: relative;
    }

    .records-panel.loading {
        opacity: 0.72;
        pointer-events: none;
    }

    .records-panel.loading::after {
        content: "Đang tải dữ liệu...";
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background: rgba(248, 250, 252, 0.72);
        color: #0284c7;
        font-weight: 900;
        backdrop-filter: blur(2px);
        z-index: 5;
    }

    .panel-head {
        padding: 20px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
    }

    .panel-title i {
        color: #0ea5e9;
        font-size: 22px;
    }

    .panel-subtitle {
        margin-top: 4px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .result-count {
        padding: 8px 12px;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .records-list {
        display: grid;
    }

    .record-row {
        display: grid;
        grid-template-columns: minmax(260px, 1.35fr) minmax(210px, 1fr) minmax(260px, 1.2fr) auto;
        gap: 18px;
        align-items: center;
        padding: 18px 22px;
        border-bottom: 1px solid #edf2f7;
        transition: background 0.18s ease;
    }

    .record-row:hover {
        background: #f8fafc;
    }

    .record-row:last-child {
        border-bottom: 0;
    }

    .record-row.is-hidden {
        display: none;
    }

    .patient-main {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .patient-avatar {
        width: 54px;
        height: 54px;
        border-radius: 17px;
        background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 22px;
        font-weight: 900;
        box-shadow: 0 12px 22px rgba(14, 165, 233, 0.26);
        flex-shrink: 0;
    }

    .patient-name {
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
        margin-bottom: 5px;
        word-break: break-word;
    }

    .patient-code {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        align-items: center;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
    }

    .badge.online {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.offline {
        background: #dcfce7;
        color: #15803d;
    }

    .badge.neutral {
        background: #f1f5f9;
        color: #475569;
    }

    .info-stack {
        display: grid;
        gap: 7px;
        color: #475569;
        font-size: 13px;
        font-weight: 650;
        min-width: 0;
    }

    .info-line {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .info-line i {
        color: #0ea5e9;
        font-size: 16px;
        flex-shrink: 0;
    }

    .info-line span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .visit-box {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 15px;
        padding: 12px 13px;
    }

    .visit-title {
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .visit-title i {
        color: #0ea5e9;
    }

    .visit-desc {
        color: #475569;
        font-size: 13px;
        line-height: 1.45;
        font-weight: 650;
    }

    .diagnosis {
        margin-top: 7px;
        color: #1e293b;
        font-size: 13px;
        font-weight: 750;
    }

    .diagnosis span {
        color: #64748b;
        font-weight: 700;
    }

    .row-actions {
        display: flex;
        justify-content: flex-end;
    }

    .empty-state {
        padding: 54px 22px;
        text-align: center;
        color: #64748b;
    }

    .empty-icon {
        width: 74px;
        height: 74px;
        display: grid;
        place-items: center;
        margin: 0 auto 16px;
        border-radius: 24px;
        background: #e0f2fe;
        color: #0284c7;
        font-size: 36px;
    }

    .empty-title {
        color: #0f172a;
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .client-empty {
        display: none;
        padding: 44px 22px;
        text-align: center;
        color: #64748b;
        background: #fff;
    }

    .client-empty.show {
        display: block;
    }

    .client-empty i {
        width: 68px;
        height: 68px;
        display: grid;
        place-items: center;
        margin: 0 auto 14px;
        border-radius: 22px;
        background: #e0f2fe;
        color: #0284c7;
        font-size: 32px;
    }

    .client-empty strong {
        display: block;
        color: #0f172a;
        font-size: 19px;
        font-weight: 900;
        margin-bottom: 6px;
    }

    .pagination-wrap {
        padding: 18px 22px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }

    .pagination-wrap.is-hidden {
        display: none;
    }

    @media (max-width: 1200px) {
        .records-stats {
            grid-template-columns: repeat(2, minmax(180px, 1fr));
        }

        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }

        .filter-actions {
            grid-column: 1 / -1;
        }

        .record-row {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .row-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .records-stats,
        .filter-grid {
            grid-template-columns: 1fr;
        }

        .panel-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .filter-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-action {
            width: 100%;
        }

        .record-row {
            padding: 16px;
        }
    }
</style>

<div class="records-page" id="recordsPage">
    <div class="records-stats">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="ri-folder-user-line"></i>
            </div>
            <div>
                <div class="stat-label">Tổng hồ sơ</div>
                <div class="stat-value">{{ $totalProfiles ?? 0 }}</div>
                <div class="stat-note">Toàn bộ bệnh nhân đã lưu</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="ri-global-line"></i>
            </div>
            <div>
                <div class="stat-label">Bệnh nhân online</div>
                <div class="stat-value">{{ $onlineProfiles ?? 0 }}</div>
                <div class="stat-note">Đặt lịch qua hệ thống</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="ri-user-received-line"></i>
            </div>
            <div>
                <div class="stat-label">Khám trực tiếp</div>
                <div class="stat-value">{{ $offlineProfiles ?? 0 }}</div>
                <div class="stat-note">Tiếp nhận tại quầy</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="ri-calendar-check-line"></i>
            </div>
            <div>
                <div class="stat-label">Hoàn thành hôm nay</div>
                <div class="stat-value">{{ $completedTodayCount ?? 0 }}</div>
                <div class="stat-note">Ca đã kết thúc khám</div>
            </div>
        </div>
    </div>

    <div class="filter-panel">
        <div class="filter-header">
            <div class="filter-title">
                <i class="ri-filter-3-line"></i>
                Bộ lọc hồ sơ
            </div>

            <div class="filter-status" id="filterStatus"></div>
        </div>

        <form method="GET" action="{{ route('admin.patient-records.index') }}" id="recordFilterForm">
            <div class="filter-grid">
                <div>
                    <label class="field-label">Tìm kiếm nhanh</label>
                    <input
                        type="text"
                        name="keyword"
                        value=""
                        class="form-control"
                        id="quickKeywordInput"
                        placeholder="Nhập tên, SĐT, email hoặc CCCD..."
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label class="field-label">Nguồn hồ sơ</label>
                    <select name="source" class="form-control js-server-filter">
                        <option value="">Tất cả nguồn</option>
                        <option value="online" @selected(($source ?? '') === 'online')>Online</option>
                        <option value="offline" @selected(($source ?? '') === 'offline')>Trực tiếp</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Bác sĩ phụ trách</label>
                    <select name="doctor_id" class="form-control js-server-filter">
                        <option value="">Tất cả bác sĩ</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected((string)($doctorId ?? '') === (string)$doctor->id)>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Trạng thái lịch</label>
                    <select name="status" class="form-control js-server-filter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="confirmed" @selected(($status ?? '') === 'confirmed')>Đã xác nhận</option>
                        <option value="checked_in" @selected(($status ?? '') === 'checked_in')>Đã tiếp nhận</option>
                        <option value="waiting" @selected(($status ?? '') === 'waiting')>Đang chờ</option>
                        <option value="in_progress" @selected(($status ?? '') === 'in_progress')>Đang khám</option>
                        <option value="completed" @selected(($status ?? '') === 'completed')>Hoàn thành</option>
                        <option value="cancelled" @selected(($status ?? '') === 'cancelled')>Đã hủy</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-action btn-primary-soft">
                        <i class="ri-filter-3-line"></i>
                        Lọc
                    </button>

                    <a href="{{ route('admin.patient-records.index') }}" class="btn-action btn-ghost" id="clearFilterBtn">
                        <i class="ri-refresh-line"></i>
                        Xóa lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="records-panel" id="recordsPanel">
        <div class="panel-head">
            <div>
                <div class="panel-title">
                    <i class="ri-file-list-3-line"></i>
                    Danh sách hồ sơ bệnh án
                </div>
                <div class="panel-subtitle">
                    Admin chỉ theo dõi và quản lý tổng quan, không chỉnh sửa nội dung chuyên môn của bác sĩ.
                </div>
            </div>

            <div class="result-count" id="visibleResultCount">
                {{ $profiles->total() }} hồ sơ
            </div>
        </div>

        @if($profiles->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="ri-folder-warning-line"></i>
                </div>
                <div class="empty-title">Không tìm thấy hồ sơ phù hợp</div>
                <div>Thử đổi nguồn hồ sơ, bác sĩ hoặc trạng thái lịch khám.</div>
            </div>
        @else
            <div class="records-list" id="recordsList">
                @foreach($profiles as $profile)
                    @php
                        $latestAppointment = $profile->appointments->first();
                        $latestRecord = $latestAppointment?->medicalRecord;

                        $displayName = $profile->full_name ?: 'Bệnh nhân #' . $profile->id;
                        $initial = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'), 'UTF-8');

                        $sourceValue = $profile->source ?: 'unknown';
                        $sourceLabel = match ($sourceValue) {
                            'online' => 'Online',
                            'offline' => 'Trực tiếp',
                            default => 'Không rõ',
                        };

                        $sourceClass = match ($sourceValue) {
                            'online' => 'online',
                            'offline' => 'offline',
                            default => 'neutral',
                        };

                        $latestStatus = $latestAppointment?->status;
                        $statusLabel = match ($latestStatus) {
                            'pending' => 'Chờ xác nhận',
                            'confirmed' => 'Đã xác nhận',
                            'checked_in' => 'Đã tiếp nhận',
                            'waiting' => 'Đang chờ',
                            'in_progress' => 'Đang khám',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy',
                            default => 'Chưa có lịch',
                        };

                        $searchText = implode(' ', [
                            $displayName,
                            $profile->phone,
                            $profile->email,
                            $profile->identity_number,
                            $profile->address,
                        ]);
                    @endphp

                    <div
                        class="record-row js-record-row"
                        data-search="{{ \Illuminate\Support\Str::lower($searchText) }}"
                    >
                        <div class="patient-main">
                            <div class="patient-avatar">{{ $initial }}</div>

                            <div style="min-width:0;">
                                <div class="patient-name">{{ $displayName }}</div>

                                <div class="patient-code">
                                    <span class="badge {{ $sourceClass }}">
                                        <i class="ri-user-location-line"></i>
                                        {{ $sourceLabel }}
                                    </span>

                                    <span class="badge neutral">
                                        <i class="ri-file-paper-2-line"></i>
                                        {{ $profile->completed_visits_count ?? 0 }} bệnh án
                                    </span>

                                    <span class="badge neutral">
                                        <i class="ri-history-line"></i>
                                        {{ $profile->total_visits_count ?? 0 }} lượt khám
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="info-stack">
                            <div class="info-line">
                                <i class="ri-phone-line"></i>
                                <span>{{ $profile->phone ?: 'Chưa có SĐT' }}</span>
                            </div>

                            <div class="info-line">
                                <i class="ri-mail-line"></i>
                                <span>{{ $profile->email ?: 'Chưa có email' }}</span>
                            </div>

                            <div class="info-line">
                                <i class="ri-id-card-line"></i>
                                <span>{{ $profile->identity_number ?: 'Chưa có CCCD' }}</span>
                            </div>

                            <div class="info-line">
                                <i class="ri-map-pin-line"></i>
                                <span>{{ $profile->address ?: 'Chưa có địa chỉ' }}</span>
                            </div>
                        </div>

                        <div class="visit-box">
                            <div class="visit-title">
                                <i class="ri-stethoscope-line"></i>
                                Lần khám gần nhất
                            </div>

                            @if($latestAppointment)
                                <div class="visit-desc">
                                    {{ $latestAppointment->appointment_date?->format('d/m/Y H:i') }}
                                    · {{ $latestAppointment->service?->name ?? 'Chưa rõ dịch vụ' }}
                                    · {{ $latestAppointment->doctor?->name ?? 'Chưa rõ bác sĩ' }}
                                </div>

                                <div class="visit-desc">
                                    Phòng: {{ $latestAppointment->room?->name ?? 'Chưa phân phòng' }}
                                    · Trạng thái: {{ $statusLabel }}
                                </div>

                                <div class="diagnosis">
                                    <span>Chẩn đoán:</span>
                                    {{ $latestRecord?->diagnosis ?: 'Chưa cập nhật' }}
                                </div>
                            @else
                                <div class="visit-desc">
                                    Hồ sơ chưa có lượt khám được ghi nhận.
                                </div>
                            @endif
                        </div>

                        <div class="row-actions">
                            <a href="{{ route('admin.patient-records.show', $profile->id) }}" class="btn-action btn-primary-soft">
                                <i class="ri-eye-line"></i>
                                Xem hồ sơ
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="client-empty" id="clientEmptyState">
                <i class="ri-search-eye-line"></i>
                <strong>Không có hồ sơ khớp từ khóa</strong>
                <span>Thử nhập tên, số điện thoại, email hoặc CCCD khác.</span>
            </div>

            <div class="pagination-wrap" id="paginationWrap">
                {{ $profiles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('recordFilterForm');
    const keywordInput = document.getElementById('quickKeywordInput');
    const clearBtn = document.getElementById('clearFilterBtn');
    const statusBox = document.getElementById('filterStatus');

    if (!form) {
        return;
    }

    let activeController = null;

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'd')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function getRecordsPanel() {
        return document.getElementById('recordsPanel');
    }

    function getRows() {
        return Array.from(document.querySelectorAll('.js-record-row'));
    }

    function setStatus(message, icon = 'ri-loader-4-line') {
        if (!statusBox) {
            return;
        }

        if (!message) {
            statusBox.innerHTML = '';
            return;
        }

        statusBox.innerHTML = `<i class="${icon}"></i><span>${message}</span>`;
    }

    function updateVisibleCount(count, total, keyword) {
        const countBox = document.getElementById('visibleResultCount');

        if (!countBox) {
            return;
        }

        if (keyword) {
            countBox.textContent = `${count} kết quả khớp`;
            return;
        }

        countBox.textContent = `${total} hồ sơ`;
    }

    function applyQuickSearch() {
        const keyword = normalizeText(keywordInput ? keywordInput.value : '');
        const rows = getRows();
        const clientEmpty = document.getElementById('clientEmptyState');
        const paginationWrap = document.getElementById('paginationWrap');

        let visibleCount = 0;

        rows.forEach(function (row) {
            const searchText = normalizeText(row.dataset.search || row.textContent);
            const isMatch = keyword === '' || searchText.includes(keyword);

            row.classList.toggle('is-hidden', !isMatch);

            if (isMatch) {
                visibleCount++;
            }
        });

        if (clientEmpty) {
            clientEmpty.classList.toggle('show', keyword !== '' && visibleCount === 0);
        }

        if (paginationWrap) {
            paginationWrap.classList.toggle('is-hidden', keyword !== '');
        }

        updateVisibleCount(visibleCount, rows.length, keyword);

        if (keyword) {
            setStatus(`Tìm nhanh: ${visibleCount} kết quả`, 'ri-search-line');
        } else {
            setStatus('');
        }
    }

    function buildServerFilterUrl(pageUrl = null) {
        if (pageUrl) {
            return pageUrl;
        }

        const params = new URLSearchParams();

        form.querySelectorAll('select[name]').forEach(function (select) {
            const value = String(select.value || '').trim();

            if (value !== '') {
                params.append(select.name, value);
            }
        });

        const query = params.toString();

        return form.action + (query ? `?${query}` : '');
    }

    async function loadRecordsFromServer(url = null, pushState = true) {
        const targetUrl = buildServerFilterUrl(url);
        const panel = getRecordsPanel();

        if (!panel) {
            return;
        }

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        panel.classList.add('loading');
        setStatus('Đang tải bộ lọc...', 'ri-loader-4-line');

        try {
            const response = await fetch(targetUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: activeController.signal
            });

            if (!response.ok) {
                throw new Error('Không thể tải dữ liệu hồ sơ');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newPanel = doc.getElementById('recordsPanel');

            if (!newPanel) {
                throw new Error('Không tìm thấy vùng danh sách hồ sơ');
            }

            panel.replaceWith(newPanel);

            if (pushState) {
                window.history.replaceState({}, '', targetUrl);
            }

            applyQuickSearch();

            setStatus('Đã cập nhật bộ lọc', 'ri-check-line');

            window.setTimeout(function () {
                if (!keywordInput || keywordInput.value.trim() === '') {
                    setStatus('');
                }
            }, 900);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            const currentPanel = getRecordsPanel();

            if (currentPanel) {
                currentPanel.classList.remove('loading');
            }

            setStatus('Lỗi khi tải dữ liệu', 'ri-error-warning-line');
            console.error(error);
        }
    }

    if (keywordInput) {
        keywordInput.addEventListener('input', applyQuickSearch);
    }

    form.querySelectorAll('select.js-server-filter').forEach(function (select) {
        select.addEventListener('change', function () {
            loadRecordsFromServer();
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        loadRecordsFromServer();
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function (event) {
            event.preventDefault();

            if (keywordInput) {
                keywordInput.value = '';
            }

            form.querySelectorAll('select[name]').forEach(function (select) {
                select.selectedIndex = 0;
            });

            loadRecordsFromServer(clearBtn.href);
        });
    }

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#recordsPanel .pagination a');

        if (!paginationLink) {
            return;
        }

        event.preventDefault();

        if (keywordInput) {
            keywordInput.value = '';
        }

        loadRecordsFromServer(paginationLink.href);
    });

    applyQuickSearch();
});
</script>
@endsection