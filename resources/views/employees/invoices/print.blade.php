@php
    use Carbon\Carbon;

    $invoice->loadMissing([
        'appointment.medicalRecord',
        'appointment.room',
        'appointment.service',
        'appointment.doctor',
        'patient',
        'patientProfile',
        'doctor',
        'service',
        'cashier',
        'verifier',
        'sentToPatientBy',
        'payments',
    ]);

    $issuedAt = $invoice->issued_at
        ? Carbon::parse($invoice->issued_at)
        : ($invoice->created_at ? Carbon::parse($invoice->created_at) : now());

    $paidAt = $invoice->paid_at ? Carbon::parse($invoice->paid_at) : null;
    $sentAt = $invoice->sent_to_patient_at ? Carbon::parse($invoice->sent_to_patient_at) : null;
    $dueAt = $invoice->payment_due_at ? Carbon::parse($invoice->payment_due_at) : null;
    $submittedAt = $invoice->patient_paid_submitted_at ? Carbon::parse($invoice->patient_paid_submitted_at) : null;
    $verifiedAt = $invoice->verified_at ? Carbon::parse($invoice->verified_at) : null;

    $appointmentDate = $invoice->appointment_date
        ? Carbon::parse($invoice->appointment_date)
        : ($invoice->appointment?->appointment_date ? Carbon::parse($invoice->appointment->appointment_date) : null);

    $patientName = $invoice->display_patient_name
        ?? $invoice->patientProfile?->full_name
        ?? $invoice->patient?->name
        ?? $invoice->patient_name
        ?? 'Chưa có tên';

    $patientPhone = $invoice->display_patient_phone
        ?? $invoice->patientProfile?->phone
        ?? $invoice->patient?->phone
        ?? $invoice->patient_phone
        ?? 'Chưa có SĐT';

    $doctorName = $invoice->display_doctor_name
        ?? $invoice->doctor?->name
        ?? $invoice->appointment?->doctor?->name
        ?? $invoice->doctor_name
        ?? 'Chưa có bác sĩ';

    $serviceName = $invoice->display_service_name
        ?? $invoice->service?->name
        ?? $invoice->appointment?->service?->name
        ?? $invoice->service_name
        ?? 'Chưa có dịch vụ';

    $room = $invoice->appointment?->room;
    $medicalRecord = $invoice->appointment?->medicalRecord;
    $doctorPrescription = trim((string) ($medicalRecord?->prescription ?? ''));

    $cashierName = $invoice->cashier?->name
        ?? $invoice->verifier?->name
        ?? auth()->user()?->name
        ?? 'Nhân viên thu ngân';

    $medicineItems = collect($invoice->medicine_items ?: []);
    $extraItems = collect($invoice->extra_items ?: []);

    $source = $invoice->appointment?->source ?? $invoice->patientProfile?->source ?? 'online';
    $sourceLabel = $source === 'offline' ? 'KHÁM TRỰC TIẾP' : 'ĐẶT LỊCH ONLINE';

    $servicePrice = (float) ($invoice->service_price ?? $invoice->service_amount ?? 0);
    $medicineTotal = (float) ($invoice->medicine_total ?? $medicineItems->sum(fn ($item) => (float) ($item['total'] ?? 0)));
    $extraTotal = (float) ($invoice->extra_total ?? $invoice->extra_amount ?? $extraItems->sum(fn ($item) => (float) ($item['total'] ?? 0)));
    $discountAmount = (float) ($invoice->discount_amount ?? 0);
    $totalAmount = (float) ($invoice->total_amount ?? max($servicePrice + $medicineTotal + $extraTotal - $discountAmount, 0));
    $paidAmount = (float) ($invoice->paid_amount ?? 0);
    $remainingAmount = (float) ($invoice->remaining_amount ?? max($totalAmount - $paidAmount, 0));

    $statusLabel = $invoice->status_label ?? match ($invoice->status) {
        'paid' => 'Đã thanh toán',
        'payment_pending' => 'Chờ xác nhận chuyển khoản',
        'cancelled' => 'Đã hủy',
        default => 'Chờ thanh toán',
    };

    $paymentMethodLabel = $invoice->payment_method_label ?? match ($invoice->payment_method) {
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'card' => 'Thẻ',
        'momo' => 'MoMo',
        'other' => 'Khác',
        default => 'Chưa thanh toán',
    };
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn {{ $invoice->invoice_code }}</title>

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

        .ticket-subtitle,
        .ticket-date {
            text-align: center;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .badge {
            width: fit-content;
            margin: 10px auto 8px;
            padding: 6px 14px;
            border: 1px solid #0f172a;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 900;
        }

        .invoice-code {
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .invoice-status {
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 12px;
        }

        .invoice-status.unpaid { color: #92400e; }
        .invoice-status.payment_pending { color: #075985; }
        .invoice-status.paid { color: #166534; }
        .invoice-status.cancelled { color: #991b1b; }

        .divider {
            border-top: 1px dashed #94a3b8;
            margin: 12px 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 7px;
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

        .room-box {
            border: 2px solid #0f172a;
            padding: 12px;
            margin: 12px 0 14px;
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
            font-size: 13px;
            line-height: 1.45;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #94a3b8;
            padding: 6px 0;
        }

        .items-table td {
            padding: 6px 0;
            vertical-align: top;
            border-bottom: 1px dotted #cbd5e1;
        }

        .items-table .qty {
            width: 32px;
            text-align: center;
        }

        .items-table .money {
            width: 88px;
            text-align: right;
            white-space: nowrap;
        }

        .item-meta {
            color: #64748b;
            font-size: 11px;
            margin-top: 2px;
        }

        .summary {
            font-size: 13px;
            line-height: 1.6;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .summary-row strong {
            white-space: nowrap;
        }

        .summary-row.total {
            margin-top: 6px;
            padding-top: 8px;
            border-top: 1px dashed #0f172a;
            font-size: 16px;
            font-weight: 900;
        }

        .note-box {
            font-size: 12px;
            line-height: 1.5;
            white-space: pre-line;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 9px;
        }

        .payment-note {
            font-size: 12px;
            line-height: 1.5;
            border: 1px solid #bae6fd;
            background: #f0f9ff;
            color: #075985;
            padding: 9px;
        }

        .signature {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-top: 18px;
            text-align: center;
            font-size: 12px;
        }

        .signature-space {
            height: 44px;
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
        <button type="button" class="btn btn-primary" onclick="window.print()">In hóa đơn</button>
        <a href="{{ route('employees.invoices.show', $invoice) }}" class="btn">Về chi tiết</a>
        <a href="{{ route('employees.invoices.index') }}" class="btn">Danh sách</a>
    </div>

    <div class="ticket-wrap">
        <div class="ticket">
            <div class="clinic-name">DENTALCARE</div>
            <div class="ticket-subtitle">Hóa đơn khám bệnh</div>
            <div class="ticket-date">{{ $issuedAt->format('d/m/Y H:i') }}</div>

            <div class="badge">{{ $sourceLabel }}</div>

            <div class="invoice-code">{{ $invoice->invoice_code }}</div>
            <div class="invoice-status {{ $invoice->status }}">
                {{ $statusLabel }}
            </div>

            <div class="divider"></div>

            <div class="info">
                <div class="info-row"><strong>Bệnh nhân:</strong> {{ $patientName }}</div>
                <div class="info-row"><strong>SĐT:</strong> {{ $patientPhone }}</div>
                <div class="info-row"><strong>Dịch vụ:</strong> {{ $serviceName }}</div>
                <div class="info-row"><strong>Bác sĩ:</strong> {{ $doctorName }}</div>
                <div class="info-row">
                    <strong>Ngày khám:</strong>
                    {{ $appointmentDate ? $appointmentDate->format('H:i d/m/Y') : 'Chưa có' }}
                </div>
            </div>

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

            @if($doctorPrescription !== '')
                <div class="divider"></div>
                <div class="section-title">Đơn thuốc / chỉ định bác sĩ</div>
                <div class="note-box">{{ $doctorPrescription }}</div>
            @endif

            <div class="divider"></div>

            <div class="section-title">Chi tiết chi phí</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Nội dung</th>
                        <th class="qty">SL</th>
                        <th class="money">Tiền</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $serviceName }}</strong>
                            <div class="item-meta">Dịch vụ khám/điều trị</div>
                        </td>
                        <td class="qty">1</td>
                        <td class="money">{{ number_format($servicePrice, 0, ',', '.') }}đ</td>
                    </tr>

                    @foreach($medicineItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item['name'] ?? 'Thuốc' }}</strong>
                                <div class="item-meta">
                                    {{ $item['code'] ?? '-' }} · {{ $item['unit'] ?? '-' }}
                                </div>
                            </td>
                            <td class="qty">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="money">{{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach

                    @foreach($extraItems as $item)
                        @if(!empty($item['name']))
                            <tr>
                                <td>
                                    <strong>{{ $item['name'] }}</strong>
                                </td>
                                <td class="qty">{{ $item['quantity'] ?? 1 }}</td>
                                <td class="money">
                                    {{ number_format((float) ($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0))), 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div class="divider"></div>

            <div class="summary">
                <div class="summary-row">
                    <span>Tiền dịch vụ</span>
                    <strong>{{ number_format($servicePrice, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row">
                    <span>Tiền thuốc</span>
                    <strong>{{ number_format($medicineTotal, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row">
                    <span>Phụ phí</span>
                    <strong>{{ number_format($extraTotal, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row">
                    <span>Giảm giá</span>
                    <strong>-{{ number_format($discountAmount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row total">
                    <span>Tổng tiền</span>
                    <strong>{{ number_format($totalAmount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row">
                    <span>Đã thanh toán</span>
                    <strong>{{ number_format($paidAmount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row">
                    <span>Còn lại</span>
                    <strong>{{ number_format($remainingAmount, 0, ',', '.') }}đ</strong>
                </div>

                <div class="summary-row">
                    <span>Phương thức</span>
                    <strong>{{ $paymentMethodLabel }}</strong>
                </div>

                @if($paidAt)
                    <div class="summary-row">
                        <span>Thanh toán lúc</span>
                        <strong>{{ $paidAt->format('H:i d/m/Y') }}</strong>
                    </div>
                @endif
            </div>

            @if($sentAt || $submittedAt || $verifiedAt)
                <div class="divider"></div>
                <div class="section-title">Thông tin thanh toán online</div>

                <div class="payment-note">
                    @if($sentAt)
                        <div><strong>Gửi hóa đơn:</strong> {{ $sentAt->format('H:i d/m/Y') }}</div>
                    @endif

                    @if($dueAt)
                        <div><strong>Hạn thanh toán:</strong> {{ $dueAt->format('H:i d/m/Y') }}</div>
                    @endif

                    @if($submittedAt)
                        <div><strong>Bệnh nhân gửi bill:</strong> {{ $submittedAt->format('H:i d/m/Y') }}</div>
                    @endif

                    @if($verifiedAt)
                        <div><strong>Thu ngân xác nhận:</strong> {{ $verifiedAt->format('H:i d/m/Y') }}</div>
                    @endif

                    @if($invoice->patient_payment_note)
                        <div><strong>Ghi chú BN:</strong> {{ $invoice->patient_payment_note }}</div>
                    @endif
                </div>
            @endif

            @if($invoice->notes)
                <div class="divider"></div>
                <div class="section-title">Ghi chú</div>
                <div class="note-box">{{ $invoice->notes }}</div>
            @endif

            <div class="signature">
                <div>
                    <strong>Người thanh toán</strong>
                    <div class="signature-space"></div>
                    <div>{{ $patientName }}</div>
                </div>

                <div>
                    <strong>Thu ngân</strong>
                    <div class="signature-space"></div>
                    <div>{{ $cashierName }}</div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="footer-note">
                Cảm ơn quý khách đã sử dụng dịch vụ.<br>
                Vui lòng giữ hóa đơn để đối chiếu khi cần.
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