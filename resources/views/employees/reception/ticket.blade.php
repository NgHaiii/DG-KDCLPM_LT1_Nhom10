@php
    use Carbon\Carbon;

    $appointment->loadMissing(['patient', 'patientProfile', 'doctor', 'service', 'room']);

    $appointmentDate = $appointment->appointment_date
        ? Carbon::parse($appointment->appointment_date)
        : now();

    $checkedInAt = $appointment->checked_in_at
        ? Carbon::parse($appointment->checked_in_at)
        : now();

    $patientName = $appointment->patient_display_name ?: 'Chưa có tên';
    $patientPhone = $appointment->patient_display_phone ?: 'Chưa có SĐT';

    $queueNumber = $appointment->queue_number ?? '-';

    $sourceLabel = $appointment->source === 'online'
        ? 'ĐẶT LỊCH ONLINE'
        : 'KHÁM TRỰC TIẾP';

    $sourceNote = $appointment->source === 'online'
        ? 'Bệnh nhân đã đặt lịch trước qua hệ thống'
        : 'Bệnh nhân tiếp nhận trực tiếp tại quầy';

    $room = $appointment->room;
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu tiếp nhận khám bệnh</title>

    <style>
        body {
            margin: 0;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
            color: #0f172a;
        }

        .page-actions {
            width: 420px;
            margin: 24px auto 16px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            border: 1px solid #cbd5e1;
            background: white;
            color: #0f172a;
            padding: 10px 14px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #0ea5e9;
            border-color: #0ea5e9;
            color: white;
        }

        .ticket-wrap {
            width: 420px;
            margin: 0 auto 32px;
            background: white;
            border: 1px solid #e2e8f0;
            padding: 16px;
        }

        .ticket {
            border: 1px dashed #0f172a;
            padding: 18px 16px;
        }

        .clinic-name {
            text-align: center;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .ticket-subtitle {
            text-align: center;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .ticket-date {
            text-align: center;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .badge {
            width: fit-content;
            margin: 0 auto 6px;
            padding: 6px 14px;
            border: 1px solid #0f172a;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 900;
        }

        .source-note {
            text-align: center;
            font-size: 11px;
            color: #475569;
            margin-bottom: 14px;
        }

        .queue-label {
            text-align: center;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .queue-number {
            text-align: center;
            font-size: 62px;
            line-height: 1;
            font-weight: 900;
            margin-bottom: 18px;
        }

        .room-box {
            border: 2px solid #0f172a;
            padding: 12px;
            margin-bottom: 14px;
            text-align: center;
        }

        .room-label {
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .room-name {
            font-size: 22px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .room-detail {
            text-align: left;
            font-size: 14px;
            line-height: 1.45;
        }

        .divider {
            border-top: 1px dashed #94a3b8;
            margin: 12px 0;
        }

        .info {
            font-size: 13px;
            line-height: 1.55;
        }

        .info-row {
            margin-bottom: 3px;
            word-break: break-word;
        }

        .info-row strong {
            font-weight: 900;
        }

        .guide-box {
            margin-top: 12px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            font-size: 12px;
            line-height: 1.45;
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            line-height: 1.45;
            margin-top: 12px;
        }

        @media print {
            body {
                background: white;
            }

            .page-actions {
                display: none !important;
            }

            .ticket-wrap {
                margin: 0;
                width: 80mm;
                border: none;
                padding: 0;
            }

            .ticket {
                border: 1px dashed #000;
                padding: 12px;
            }

            @page {
                size: 80mm auto;
                margin: 6mm;
            }
        }
    </style>
</head>
<body>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">In lại phiếu</button>
        <a href="{{ route('employees.reception.queue') }}" class="btn">Về danh sách khám</a>
        <a href="{{ route('employees.reception') }}" class="btn">Tiếp nhận tiếp</a>
    </div>

    <div class="ticket-wrap">
        <div class="ticket">
            <div class="clinic-name">DENTALCARE</div>
            <div class="ticket-subtitle">Phiếu tiếp nhận khám bệnh</div>
            <div class="ticket-date">{{ $checkedInAt->format('d/m/Y H:i') }}</div>

            <div class="badge">{{ $sourceLabel }}</div>
            <div class="source-note">{{ $sourceNote }}</div>

            <div class="queue-label">Số thứ tự</div>
            <div class="queue-number">{{ $queueNumber }}</div>

            <div class="room-box">
                <div class="room-label">PHÒNG KHÁM</div>
                <div class="room-name">{{ $room?->name ?? 'Chưa có phòng' }}</div>

                <div class="room-detail">
                    <div><strong>Mã phòng:</strong> {{ $room?->code ?? 'Chưa cập nhật' }}</div>
                    <div><strong>Tầng:</strong> {{ $room?->floor ?? 'Chưa cập nhật' }}</div>
                    <div><strong>Loại phòng:</strong> {{ $room?->type ?? 'Chưa cập nhật' }}</div>
                    <div><strong>Vị trí/khu vực:</strong> {{ $room?->location ?? 'Chưa cập nhật' }}</div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="info">
                <div class="info-row">
                    <strong>Bệnh nhân:</strong> {{ $patientName }}
                </div>

                <div class="info-row">
                    <strong>SĐT:</strong> {{ $patientPhone }}
                </div>

                <div class="info-row">
                    <strong>Dịch vụ:</strong> {{ $appointment->service?->name ?? 'Chưa có dịch vụ' }}
                </div>

                <div class="info-row">
                    <strong>Bác sĩ:</strong> {{ $appointment->doctor?->name ?? 'Chưa có bác sĩ' }}
                </div>

                <div class="info-row">
                    <strong>Giờ hẹn khám dự kiến:</strong> {{ $appointmentDate->format('H:i d/m/Y') }}
                </div>

                <div class="info-row">
                    <strong>Tiếp nhận:</strong> {{ $checkedInAt->format('H:i d/m/Y') }}
                </div>
            </div>

            <div class="guide-box">
                <strong>Hướng dẫn:</strong>
                Đến {{ $room?->name ?? 'phòng khám được chỉ định' }}
                @if($room?->floor)
                    , tầng {{ $room->floor }}
                @endif
                @if($room?->location)
                    , {{ $room->location }}
                @endif
                . Nếu không tìm thấy phòng, vui lòng đưa phiếu này cho lễ tân.
            </div>

            <div class="divider"></div>

            <div class="footer-note">
                Vui lòng giữ phiếu và chờ gọi số.<br>
                Cảm ơn quý khách.
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>