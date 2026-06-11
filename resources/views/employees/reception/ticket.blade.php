<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu số thứ tự</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f8fafc;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        .screen-actions {
            padding: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary {
            background: #0ea5e9;
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #111827;
            border: 1px solid #d1d5db;
        }

        .ticket-wrap {
            width: 80mm;
            margin: 20px auto;
            background: white;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .ticket {
            border: 1px dashed #111827;
            padding: 12px;
            text-align: center;
        }

        .brand {
            font-size: 17px;
            font-weight: 900;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }

        .clinic {
            font-size: 11px;
            line-height: 1.35;
            margin-bottom: 10px;
        }

        .type {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #111827;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .label {
            font-size: 13px;
            margin-top: 4px;
        }

        .number {
            font-size: 52px;
            line-height: 1;
            font-weight: 900;
            margin: 8px 0 12px;
        }

        .info {
            text-align: left;
            font-size: 12px;
            line-height: 1.55;
            border-top: 1px dashed #9ca3af;
            padding-top: 10px;
            margin-top: 10px;
        }

        .info-row {
            margin-bottom: 3px;
        }

        .footer {
            border-top: 1px dashed #9ca3af;
            margin-top: 10px;
            padding-top: 8px;
            font-size: 11px;
            line-height: 1.4;
        }

        @media print {
            body {
                background: white;
            }

            .screen-actions {
                display: none;
            }

            .ticket-wrap {
                margin: 0;
                width: 100%;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
@php
    $isOffline = ($appointment->source ?? 'online') === 'offline';
@endphp

<div class="screen-actions">
    <button class="btn btn-primary" onclick="window.print()">In lại phiếu</button>
    <a href="{{ route('employees.reception.queue') }}" class="btn btn-secondary">Về danh sách khám</a>
    <a href="{{ route('employees.reception') }}" class="btn btn-secondary">Tiếp nhận tiếp</a>
</div>

<div class="ticket-wrap">
    <div class="ticket">
        <div class="brand">DENTALCARE</div>
        <div class="clinic">
            Phiếu tiếp nhận khám bệnh<br>
            {{ now()->format('d/m/Y H:i') }}
        </div>

        <div class="type">
            {{ $isOffline ? 'Khám trực tiếp' : 'Lịch đặt online' }}
        </div>

        <div class="label">Số thứ tự</div>
        <div class="number">{{ $appointment->queue_number ?? '-' }}</div>

        <div class="info">
            <div class="info-row"><strong>Bệnh nhân:</strong> {{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}</div>
            <div class="info-row"><strong>Dịch vụ:</strong> {{ $appointment->service?->name ?? '-' }}</div>
            <div class="info-row"><strong>Bác sĩ:</strong> {{ $appointment->doctor?->name ?? '-' }}</div>
            <div class="info-row"><strong>Phòng:</strong> {{ $appointment->room?->name ?? 'Chưa có phòng' }}</div>
            <div class="info-row"><strong>Giờ hẹn:</strong> {{ $appointment->appointment_date?->format('H:i d/m/Y') ?? '-' }}</div>
            <div class="info-row"><strong>Tiếp nhận:</strong> {{ $appointment->checked_in_at?->format('H:i d/m/Y') ?? now()->format('H:i d/m/Y') }}</div>
        </div>

        <div class="footer">
            Vui lòng giữ phiếu và chờ gọi số.<br>
            Cảm ơn quý khách.
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 400);
    });
</script>
</body>
</html><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu số thứ tự</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f8fafc;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        .screen-actions {
            padding: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary {
            background: #0ea5e9;
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #111827;
            border: 1px solid #d1d5db;
        }

        .ticket-wrap {
            width: 80mm;
            margin: 20px auto;
            background: white;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .ticket {
            border: 1px dashed #111827;
            padding: 12px;
            text-align: center;
        }

        .brand {
            font-size: 17px;
            font-weight: 900;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }

        .clinic {
            font-size: 11px;
            line-height: 1.35;
            margin-bottom: 10px;
        }

        .type {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #111827;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .label {
            font-size: 13px;
            margin-top: 4px;
        }

        .number {
            font-size: 52px;
            line-height: 1;
            font-weight: 900;
            margin: 8px 0 12px;
        }

        .room-block {
            border: 2px solid #111827;
            padding: 8px;
            margin: 10px 0;
            text-align: left;
        }

        .room-title {
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .room-main {
            text-align: center;
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 6px;
        }

        .info {
            text-align: left;
            font-size: 12px;
            line-height: 1.55;
            border-top: 1px dashed #9ca3af;
            padding-top: 10px;
            margin-top: 10px;
        }

        .info-row {
            margin-bottom: 3px;
        }

        .direction {
            text-align: left;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 7px;
            margin-top: 8px;
            font-size: 11px;
            line-height: 1.4;
        }

        .footer {
            border-top: 1px dashed #9ca3af;
            margin-top: 10px;
            padding-top: 8px;
            font-size: 11px;
            line-height: 1.4;
        }

        @media print {
            body {
                background: white;
            }

            .screen-actions {
                display: none;
            }

            .ticket-wrap {
                margin: 0;
                width: 100%;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
@php
    $isOffline = ($appointment->source ?? 'online') === 'offline';
    $room = $appointment->room;

    $patientPhone = $appointment->patient?->phone
        ?? $appointment->patient?->phone_number
        ?? $appointment->patient?->tel
        ?? null;

    if (!$patientPhone && $appointment->notes) {
        preg_match('/SĐT:\s*([0-9+\-\s]+)/u', $appointment->notes, $matches);
        $patientPhone = $matches[1] ?? null;
    }
@endphp

<div class="screen-actions">
    <button class="btn btn-primary" onclick="window.print()">In lại phiếu</button>
    <a href="{{ route('employees.reception.queue') }}" class="btn btn-secondary">Về danh sách khám</a>
    <a href="{{ route('employees.reception') }}" class="btn btn-secondary">Tiếp nhận tiếp</a>
</div>

<div class="ticket-wrap">
    <div class="ticket">
        <div class="brand">DENTALCARE</div>
        <div class="clinic">
            Phiếu tiếp nhận khám bệnh<br>
            {{ now()->format('d/m/Y H:i') }}
        </div>

        <div class="type">
            {{ $isOffline ? 'Khám trực tiếp' : 'Lịch đặt online' }}
        </div>

        <div class="label">Số thứ tự</div>
        <div class="number">{{ $appointment->queue_number ?? '-' }}</div>

        <div class="room-block">
            <div class="room-title">Phòng khám</div>

            @if($room)
                <div class="room-main">{{ $room->name }}</div>

                @if(!empty($room->code))
                    <div class="info-row"><strong>Mã phòng:</strong> {{ $room->code }}</div>
                @endif

                @if(!empty($room->floor))
                    <div class="info-row"><strong>Tầng:</strong> {{ $room->floor }}</div>
                @endif

                @if(!empty($room->type))
                    <div class="info-row"><strong>Loại phòng:</strong> {{ $room->type }}</div>
                @endif

                @if(!empty($room->location))
                    <div class="info-row"><strong>Vị trí/khu vực:</strong> {{ $room->location }}</div>
                @endif
            @else
                <div class="room-main">Chưa có phòng</div>
                <div class="info-row">Vui lòng hỏi lễ tân để được hướng dẫn.</div>
            @endif
        </div>

        <div class="info">
            <div class="info-row"><strong>Bệnh nhân:</strong> {{ $appointment->patient?->name ?? 'Bệnh nhân #' . $appointment->patient_id }}</div>
            <div class="info-row"><strong>SĐT:</strong> {{ $patientPhone ?: 'Chưa cập nhật' }}</div>
            <div class="info-row"><strong>Dịch vụ:</strong> {{ $appointment->service?->name ?? '-' }}</div>
            <div class="info-row"><strong>Bác sĩ:</strong> {{ $appointment->doctor?->name ?? '-' }}</div>
            <div class="info-row"><strong>Giờ hẹn/dự kiến:</strong> {{ $appointment->appointment_date?->format('H:i d/m/Y') ?? '-' }}</div>
            <div class="info-row"><strong>Tiếp nhận:</strong> {{ $appointment->checked_in_at?->format('H:i d/m/Y') ?? now()->format('H:i d/m/Y') }}</div>
        </div>

        <div class="direction">
            <strong>Hướng dẫn:</strong>
            @if($room)
                Đến {{ $room->name }}
                @if(!empty($room->floor))
                    , tầng {{ $room->floor }}
                @endif
                @if(!empty($room->location))
                    , khu vực {{ $room->location }}
                @endif
                . Nếu không tìm thấy phòng, vui lòng đưa phiếu này cho lễ tân.
            @else
                Vui lòng đưa phiếu này cho lễ tân để được hướng dẫn đến phòng khám.
            @endif
        </div>

        <div class="footer">
            Vui lòng giữ phiếu và chờ gọi số.<br>
            Cảm ơn quý khách.
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 400);
    });
</script>
</body>
</html>