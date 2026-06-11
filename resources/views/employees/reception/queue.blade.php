@extends('layouts.employee-layout')

@section('title', 'Danh sách khám')
@section('page-title', 'Danh sách khám trong ngày')
@section('page-subtitle', 'Theo dõi bệnh nhân đã tiếp nhận, đang chờ, đang khám và đã hoàn thành')

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
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .toolbar-left {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .toolbar-title {
        font-weight: 800;
        color: var(--text-main);
    }

    .toolbar-date {
        color: var(--text-muted);
        font-size: 13px;
    }

    .toolbar-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .date-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
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

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
    }

    .search-box {
        position: relative;
        min-width: 280px;
    }

    .search-box i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 17px;
    }

    .search-box input {
        width: 100%;
        padding-left: 38px;
    }

    .board {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .column {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .column-header {
        padding: 18px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .column-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-family: var(--font-title);
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
    }

    .column-title i {
        color: var(--primary);
        font-size: 20px;
    }

    .count-pill {
        min-width: 28px;
        height: 28px;
        padding: 0 9px;
        border-radius: var(--radius-full);
        background: var(--primary-light);
        color: var(--primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
    }

    .column-body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 220px;
    }

    .queue-card {
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        background: #f8fafc;
        padding: 14px;
    }

    .queue-main {
        display: grid;
        grid-template-columns: 44px 1fr;
        gap: 12px;
        align-items: flex-start;
    }

    .queue-number {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        background: white;
        border: 1px solid #e2e8f0;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 16px;
    }

    .patient-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .patient-name {
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 3px;
        line-height: 1.35;
    }

    .patient-phone {
        color: var(--text-muted);
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-waiting {
        background: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
    }

    .status-progress {
        background: #ede9fe;
        color: #5b21b6;
        border: 1px solid #ddd6fe;
    }

    .status-completed {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .compact-info {
        display: grid;
        gap: 6px;
        color: var(--text-muted);
        font-size: 13px;
        line-height: 1.4;
    }

    .compact-line {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .compact-line i {
        color: var(--primary);
        font-size: 15px;
        flex-shrink: 0;
    }

    .compact-line span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .card-footer {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .source-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: var(--radius-full);
        background: white;
        border: 1px solid #e2e8f0;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .print-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: var(--radius-md);
        background: var(--primary);
        color: white;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
    }

    .print-link:hover {
        color: white;
        filter: brightness(0.95);
    }

    .empty,
    .search-empty {
        padding: 28px 12px;
        text-align: center;
        color: var(--text-muted);
        font-size: 14px;
    }

    .search-empty {
        display: none;
    }

    @media (max-width: 1100px) {
        .board {
            grid-template-columns: 1fr;
        }

        .search-box {
            min-width: 100%;
        }

        .toolbar-actions,
        .date-form {
            width: 100%;
        }

        .date-form input[type="date"] {
            flex: 1;
        }
    }
</style>
@endsection

@section('header-actions')
<a href="{{ route('employees.reception') }}" class="btn btn-primary">
    <i class="ri-user-add-line"></i>
    Tiếp nhận
</a>
@endsection

@section('content')
@php
    $todayAppointments = $todayAppointments ?? collect();
    $completedAppointments = $completedAppointments ?? collect();

    $waitingList = $todayAppointments->whereIn('status', ['checked_in', 'waiting']);
    $inProgressList = $todayAppointments->where('status', 'in_progress');
@endphp

<div class="toolbar">
    <div class="toolbar-left">
        <div class="toolbar-title">Ngày theo dõi</div>
        <div class="toolbar-date">{{ \Carbon\Carbon::parse($date ?? now())->format('d/m/Y') }}</div>
    </div>

    <div class="toolbar-actions">
        <div class="search-box">
            <i class="ri-search-line"></i>
            <input type="text"
                   id="patientSearchInput"
                   class="form-control"
                   placeholder="Tìm tên, SĐT, STT, dịch vụ, bác sĩ..."
                   autocomplete="off">
        </div>

        <form method="GET" action="{{ route('employees.reception.queue') }}" class="date-form">
            <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" class="form-control">
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="ri-search-line"></i>
                Lọc
            </button>
        </form>
    </div>
</div>

<div class="board">
    <section class="column">
        <div class="column-header">
            <div class="column-title">
                <i class="ri-hourglass-2-line"></i>
                Đang chờ khám
            </div>
            <span class="count-pill" data-count-pill>{{ $waitingList->count() }}</span>
        </div>

        <div class="column-body">
            @forelse($waitingList as $appointment)
                @php
                    $patientPhone = $appointment->patient?->phone
                        ?? $appointment->patient?->phone_number
                        ?? $appointment->patient?->tel
                        ?? null;

                    if (!$patientPhone && $appointment->notes) {
                        preg_match('/SĐT:\s*([0-9+\-\s]+)/u', $appointment->notes, $matches);
                        $patientPhone = isset($matches[1]) ? trim($matches[1]) : null;
                    }

                    $source = ($appointment->source ?? 'online') === 'offline' ? 'Khám trực tiếp' : 'Lịch online';

                    $searchText = implode(' ', [
                        $appointment->queue_number,
                        $appointment->patient?->name,
                        $patientPhone,
                        $appointment->service?->name,
                        $appointment->doctor?->name,
                        $appointment->room?->name,
                        $source,
                    ]);
                @endphp

                <div class="queue-card" data-search="{{ e(mb_strtolower($searchText)) }}">
                    <div class="queue-main">
                        <div class="queue-number">
                            {{ $appointment->queue_number ?? '-' }}
                        </div>

                        <div>
                            <div class="patient-row">
                                <div>
                                    <div class="patient-name">
                                        {{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}
                                    </div>

                                    <div class="patient-phone">
                                        <i class="ri-phone-line"></i>
                                        {{ $patientPhone ?: 'Chưa có SĐT' }}
                                    </div>
                                </div>

                                <span class="status status-waiting">
                                    <i class="ri-time-line"></i>
                                    Đang chờ
                                </span>
                            </div>

                            <div class="compact-info">
                                <div class="compact-line">
                                    <i class="ri-stethoscope-line"></i>
                                    <span>{{ $appointment->service?->name ?? 'Chưa có dịch vụ' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-user-heart-line"></i>
                                    <span>{{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-door-open-line"></i>
                                    <span>{{ $appointment->room?->name ?? 'Chưa có phòng' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-login-circle-line"></i>
                                    <span>Tiếp nhận: {{ $appointment->checked_in_at?->format('H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="source-tag">
                            <i class="{{ ($appointment->source ?? 'online') === 'offline' ? 'ri-user-add-line' : 'ri-global-line' }}"></i>
                            {{ $source }}
                        </span>

                        <a href="{{ route('employees.reception.ticket', $appointment->id) }}"
                           target="_blank"
                           class="print-link">
                            <i class="ri-printer-line"></i>
                            In lại STT
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty">Chưa có bệnh nhân đang chờ.</div>
            @endforelse

            <div class="search-empty">Không tìm thấy bệnh nhân phù hợp.</div>
        </div>
    </section>

    <section class="column">
        <div class="column-header">
            <div class="column-title">
                <i class="ri-stethoscope-line"></i>
                Đang khám
            </div>
            <span class="count-pill" data-count-pill>{{ $inProgressList->count() }}</span>
        </div>

        <div class="column-body">
            @forelse($inProgressList as $appointment)
                @php
                    $patientPhone = $appointment->patient?->phone
                        ?? $appointment->patient?->phone_number
                        ?? $appointment->patient?->tel
                        ?? null;

                    if (!$patientPhone && $appointment->notes) {
                        preg_match('/SĐT:\s*([0-9+\-\s]+)/u', $appointment->notes, $matches);
                        $patientPhone = isset($matches[1]) ? trim($matches[1]) : null;
                    }

                    $source = ($appointment->source ?? 'online') === 'offline' ? 'Khám trực tiếp' : 'Lịch online';

                    $searchText = implode(' ', [
                        $appointment->queue_number,
                        $appointment->patient?->name,
                        $patientPhone,
                        $appointment->service?->name,
                        $appointment->doctor?->name,
                        $appointment->room?->name,
                        $source,
                    ]);
                @endphp

                <div class="queue-card" data-search="{{ e(mb_strtolower($searchText)) }}">
                    <div class="queue-main">
                        <div class="queue-number">
                            {{ $appointment->queue_number ?? '-' }}
                        </div>

                        <div>
                            <div class="patient-row">
                                <div>
                                    <div class="patient-name">
                                        {{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}
                                    </div>

                                    <div class="patient-phone">
                                        <i class="ri-phone-line"></i>
                                        {{ $patientPhone ?: 'Chưa có SĐT' }}
                                    </div>
                                </div>

                                <span class="status status-progress">
                                    <i class="ri-pulse-line"></i>
                                    Đang khám
                                </span>
                            </div>

                            <div class="compact-info">
                                <div class="compact-line">
                                    <i class="ri-stethoscope-line"></i>
                                    <span>{{ $appointment->service?->name ?? 'Chưa có dịch vụ' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-user-heart-line"></i>
                                    <span>{{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-door-open-line"></i>
                                    <span>{{ $appointment->room?->name ?? 'Chưa có phòng' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-play-circle-line"></i>
                                    <span>Bắt đầu: {{ $appointment->started_at?->format('H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="source-tag">
                            <i class="{{ ($appointment->source ?? 'online') === 'offline' ? 'ri-user-add-line' : 'ri-global-line' }}"></i>
                            {{ $source }}
                        </span>

                        <a href="{{ route('employees.reception.ticket', $appointment->id) }}"
                           target="_blank"
                           class="print-link">
                            <i class="ri-printer-line"></i>
                            In lại STT
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty">Chưa có ca đang khám.</div>
            @endforelse

            <div class="search-empty">Không tìm thấy bệnh nhân phù hợp.</div>
        </div>
    </section>

    <section class="column">
        <div class="column-header">
            <div class="column-title">
                <i class="ri-check-double-line"></i>
                Đã hoàn thành
            </div>
            <span class="count-pill" data-count-pill>{{ $completedAppointments->count() }}</span>
        </div>

        <div class="column-body">
            @forelse($completedAppointments as $appointment)
                @php
                    $patientPhone = $appointment->patient?->phone
                        ?? $appointment->patient?->phone_number
                        ?? $appointment->patient?->tel
                        ?? null;

                    if (!$patientPhone && $appointment->notes) {
                        preg_match('/SĐT:\s*([0-9+\-\s]+)/u', $appointment->notes, $matches);
                        $patientPhone = isset($matches[1]) ? trim($matches[1]) : null;
                    }

                    $source = ($appointment->source ?? 'online') === 'offline' ? 'Khám trực tiếp' : 'Lịch online';

                    $searchText = implode(' ', [
                        $appointment->queue_number,
                        $appointment->patient?->name,
                        $patientPhone,
                        $appointment->service?->name,
                        $appointment->doctor?->name,
                        $appointment->room?->name,
                        $source,
                    ]);
                @endphp

                <div class="queue-card" data-search="{{ e(mb_strtolower($searchText)) }}">
                    <div class="queue-main">
                        <div class="queue-number">
                            {{ $appointment->queue_number ?? '-' }}
                        </div>

                        <div>
                            <div class="patient-row">
                                <div>
                                    <div class="patient-name">
                                        {{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}
                                    </div>

                                    <div class="patient-phone">
                                        <i class="ri-phone-line"></i>
                                        {{ $patientPhone ?: 'Chưa có SĐT' }}
                                    </div>
                                </div>

                                <span class="status status-completed">
                                    <i class="ri-check-line"></i>
                                    Hoàn thành
                                </span>
                            </div>

                            <div class="compact-info">
                                <div class="compact-line">
                                    <i class="ri-stethoscope-line"></i>
                                    <span>{{ $appointment->service?->name ?? 'Chưa có dịch vụ' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-user-heart-line"></i>
                                    <span>{{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}</span>
                                </div>

                                <div class="compact-line">
                                    <i class="ri-check-double-line"></i>
                                    <span>Hoàn thành: {{ $appointment->completed_at?->format('H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <span class="source-tag">
                            <i class="{{ ($appointment->source ?? 'online') === 'offline' ? 'ri-user-add-line' : 'ri-global-line' }}"></i>
                            {{ $source }}
                        </span>

                        <a href="{{ route('employees.reception.ticket', $appointment->id) }}"
                           target="_blank"
                           class="print-link">
                            <i class="ri-printer-line"></i>
                            In lại STT
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty">Chưa có ca hoàn thành.</div>
            @endforelse

            <div class="search-empty">Không tìm thấy bệnh nhân phù hợp.</div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('patientSearchInput');

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            filterQueueCards(input.value);
        });
    });

    function normalizeText(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function filterQueueCards(keyword) {
        const normalizedKeyword = normalizeText(keyword);

        document.querySelectorAll('.column').forEach(function (column) {
            const cards = column.querySelectorAll('.queue-card');
            const emptyBySearch = column.querySelector('.search-empty');
            const countPill = column.querySelector('[data-count-pill]');
            let visibleCount = 0;

            cards.forEach(function (card) {
                const searchableText = normalizeText(card.dataset.search || '');
                const isMatched = normalizedKeyword === '' || searchableText.includes(normalizedKeyword);

                card.style.display = isMatched ? '' : 'none';

                if (isMatched) {
                    visibleCount++;
                }
            });

            if (countPill) {
                countPill.textContent = visibleCount;
            }

            if (emptyBySearch) {
                emptyBySearch.style.display = normalizedKeyword !== '' && cards.length > 0 && visibleCount === 0
                    ? 'block'
                    : 'none';
            }
        });
    }
</script>
@endsection