@extends('layouts.doctor-layout')

@section('title', 'Hồ sơ bệnh án')
@section('page-title', 'Hồ sơ bệnh án')
@section('page-subtitle', 'Tra cứu hồ sơ bệnh nhân đã khám và lịch sử bệnh án liên quan')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .stat-card,
    .toolbar,
    .profile-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
    }

    .stat-card {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-family: var(--font-title);
        font-size: 26px;
        font-weight: 800;
        color: var(--text-main);
    }

    .toolbar {
        padding: 18px;
        margin-bottom: 22px;
    }

    .search-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-input-wrap {
        position: relative;
        flex: 1;
        min-width: 260px;
    }

    .search-input {
        width: 100%;
        padding: 12px 42px 12px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 14px;
        outline: none;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
    }

    .search-loading,
    .search-clear {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        color: var(--text-muted);
        display: none;
    }

    .search-clear {
        border: 0;
        background: transparent;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
    }

    .search-loading.active,
    .search-clear.active {
        display: inline-flex;
    }

    .search-hint {
        margin-top: 10px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .profile-results {
        position: relative;
        min-height: 120px;
    }

    .profile-results.is-loading {
        opacity: 0.58;
        pointer-events: none;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 16px;
    }

    .profile-card {
        padding: 18px;
    }

    .profile-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .profile-name {
        font-family: var(--font-title);
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 5px;
    }

    .profile-meta {
        display: grid;
        gap: 6px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .profile-meta span,
    .visit-line span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        border-radius: var(--radius-full);
        background: var(--primary-light);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .visit-box {
        margin-top: 14px;
        padding: 14px;
        border-radius: var(--radius-md);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .visit-title {
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .visit-line {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .card-actions {
        margin-top: 16px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .empty-state {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: 44px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        display: block;
        font-size: 42px;
        color: var(--primary);
        margin-bottom: 12px;
    }

    .pagination-wrap {
        margin-top: 20px;
    }

    @media (max-width: 576px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .search-form {
            align-items: stretch;
        }

        .search-input-wrap,
        .search-form .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="ri-folder-user-line"></i>
        </div>
        <div>
            <div class="stat-label">Hồ sơ đã phụ trách</div>
            <div class="stat-value">{{ $totalProfiles ?? 0 }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="ri-check-double-line"></i>
        </div>
        <div>
            <div class="stat-label">Lượt khám hoàn thành</div>
            <div class="stat-value">{{ $completedAppointmentsCount ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="toolbar">
    <form method="GET" action="{{ route('doctor.patient-profiles.index') }}" class="search-form" id="profileSearchForm">
        <div class="search-input-wrap">
            <input type="text"
                   name="keyword"
                   id="profileSearchInput"
                   value="{{ $keyword ?? '' }}"
                   class="search-input"
                   autocomplete="off"
                   placeholder="Nhập tên, số điện thoại, email, CCCD để tìm nhanh...">

            <span class="search-loading" id="profileSearchLoading">
                <i class="ri-loader-4-line"></i>
            </span>

            <button type="button" class="search-clear {{ !empty($keyword) ? 'active' : '' }}" id="profileSearchClear" title="Xóa tìm kiếm">
                <i class="ri-close-line"></i>
            </button>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="ri-search-line"></i>
            Tìm kiếm
        </button>

        <a href="{{ route('doctor.patient-profiles.index') }}"
           class="btn btn-secondary {{ empty($keyword) ? 'd-none' : '' }}"
           id="profileResetLink">
            <i class="ri-close-line"></i>
            Xóa lọc
        </a>
    </form>

    <div class="search-hint">
        Kết quả sẽ tự cập nhật khi nhập, không cần tải lại trang.
    </div>
</div>

<div id="profileResults" class="profile-results">
    @if($profiles->isEmpty())
        <div class="empty-state">
            <i class="ri-folder-user-line"></i>
            <h3>Chưa có hồ sơ bệnh án</h3>
            <p>Hồ sơ sẽ xuất hiện sau khi bác sĩ khám và hoàn thành bệnh án cho bệnh nhân.</p>
        </div>
    @else
        <div class="profile-grid">
            @foreach($profiles as $profile)
                @php
                    $latestAppointment = $profile->appointments->first();
                    $latestRecord = $latestAppointment?->medicalRecord;
                    $profileName = $profile->full_name ?: ('Bệnh nhân #' . $profile->id);
                @endphp

                <div class="profile-card">
                    <div class="profile-head">
                        <div>
                            <div class="profile-name">{{ $profileName }}</div>

                            <div class="profile-meta">
                                <span><i class="ri-phone-line"></i>{{ $profile->phone ?: 'Chưa có SĐT' }}</span>

                                @if($profile->email)
                                    <span><i class="ri-mail-line"></i>{{ $profile->email }}</span>
                                @endif

                                @if($profile->identity_number)
                                    <span><i class="ri-id-card-line"></i>{{ $profile->identity_number }}</span>
                                @endif

                                @if($profile->dob)
                                    <span><i class="ri-calendar-line"></i>{{ $profile->dob->format('d/m/Y') }}</span>
                                @endif

                                @if($profile->gender_label)
                                    <span><i class="ri-user-line"></i>{{ $profile->gender_label }}</span>
                                @endif

                                @if($profile->address)
                                    <span><i class="ri-map-pin-line"></i>{{ $profile->address }}</span>
                                @endif
                            </div>
                        </div>

                        <span class="badge">
                            <i class="ri-file-list-3-line"></i>
                            {{ $profile->completed_visits_count ?? 0 }} bệnh án
                        </span>
                    </div>

                    <div class="visit-box">
                        <div class="visit-title">Lần khám gần nhất</div>

                        @if($latestAppointment)
                            <div class="visit-line">
                                <span><i class="ri-stethoscope-line"></i>{{ $latestAppointment->service?->name ?? 'Dịch vụ' }}</span>
                                <span><i class="ri-calendar-check-line"></i>{{ $latestAppointment->appointment_date?->format('d/m/Y H:i') ?? '-' }}</span>
                                <span><i class="ri-check-double-line"></i>{{ $latestAppointment->status_label ?? $latestAppointment->status }}</span>
                            </div>

                            @if($latestRecord)
                                <div class="visit-line" style="margin-top: 8px;">
                                    <span><i class="ri-heart-pulse-line"></i>Chẩn đoán: {{ $latestRecord->diagnosis ?: 'Chưa cập nhật' }}</span>
                                </div>
                            @endif
                        @else
                            <div class="visit-line">
                                <span>Chưa có lượt khám.</span>
                            </div>
                        @endif
                    </div>

                    <div class="card-actions">
                        @if(\Illuminate\Support\Facades\Route::has('doctor.patient-profiles.show'))
                            <a href="{{ route('doctor.patient-profiles.show', $profile->id) }}" class="btn btn-secondary btn-sm">
                                <i class="ri-eye-line"></i>
                                Xem chi tiết
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrap">
            {{ $profiles->appends(['keyword' => $keyword ?? null])->links() }}
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileSearchForm');
    const input = document.getElementById('profileSearchInput');
    const results = document.getElementById('profileResults');
    const loading = document.getElementById('profileSearchLoading');
    const clearButton = document.getElementById('profileSearchClear');
    const resetLink = document.getElementById('profileResetLink');

    if (!form || !input || !results) {
        return;
    }

    let timer = null;
    let controller = null;

    function setLoading(isLoading) {
        results.classList.toggle('is-loading', isLoading);

        if (loading) {
            loading.classList.toggle('active', isLoading);
        }

        if (clearButton) {
            clearButton.classList.toggle('active', !isLoading && input.value.trim() !== '');
        }
    }

    function syncClearState() {
        const hasKeyword = input.value.trim() !== '';

        if (clearButton) {
            clearButton.classList.toggle('active', hasKeyword);
        }

        if (resetLink) {
            resetLink.classList.toggle('d-none', !hasKeyword);
        }
    }

    function buildUrl(pageUrl = null) {
        const url = pageUrl ? new URL(pageUrl, window.location.origin) : new URL(form.action, window.location.origin);
        const keyword = input.value.trim();

        if (keyword) {
            url.searchParams.set('keyword', keyword);
        } else {
            url.searchParams.delete('keyword');
        }

        if (!pageUrl) {
            url.searchParams.delete('page');
        }

        return url;
    }

    async function fetchProfiles(pageUrl = null, pushState = true) {
        const url = buildUrl(pageUrl);

        if (controller) {
            controller.abort();
        }

        controller = new AbortController();
        setLoading(true);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error('Không thể tải dữ liệu hồ sơ.');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newResults = doc.getElementById('profileResults');

            if (!newResults) {
                throw new Error('Không tìm thấy vùng kết quả hồ sơ.');
            }

            results.innerHTML = newResults.innerHTML;

            if (pushState) {
                window.history.replaceState({}, '', url.toString());
            }

            bindPaginationLinks();
        } catch (error) {
            if (error.name !== 'AbortError') {
                results.innerHTML = `
                    <div class="empty-state">
                        <i class="ri-error-warning-line"></i>
                        <h3>Không thể tải kết quả</h3>
                        <p>Vui lòng thử lại hoặc bấm nút Tìm kiếm.</p>
                    </div>
                `;
            }
        } finally {
            setLoading(false);
            syncClearState();
        }
    }

    function bindPaginationLinks() {
        results.querySelectorAll('.pagination a').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                fetchProfiles(link.href, true);
            });
        });
    }

    input.addEventListener('input', function () {
        syncClearState();

        clearTimeout(timer);
        timer = setTimeout(function () {
            fetchProfiles(null, true);
        }, 300);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearTimeout(timer);
        fetchProfiles(null, true);
    });

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            input.value = '';
            input.focus();
            syncClearState();
            fetchProfiles(null, true);
        });
    }

    if (resetLink) {
        resetLink.addEventListener('click', function (event) {
            event.preventDefault();
            input.value = '';
            syncClearState();
            fetchProfiles(null, true);
        });
    }

    bindPaginationLinks();
    syncClearState();
});
</script>
@endsection