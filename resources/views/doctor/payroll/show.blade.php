@extends('layouts.doctor-layout')

@section('title', 'Chi tiết bảng lương')
@section('page-title', 'Chi tiết bảng lương')
@section('page-subtitle', 'Chi tiết từng ca làm việc, hệ số và số tiền được tính')

@php
    $items = collect($payroll->items ?? []);

    $fmt = fn ($n) => number_format((float) ($n ?? 0), 0, ',', '.') . ' đ';
    $num = fn ($n) => number_format((float) ($n ?? 0), 2, ',', '.');

    $statusLabel = [
        'draft' => 'Nháp',
        'approved' => 'Đã duyệt',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ][$payroll->status] ?? $payroll->status;

    $excelItems = $items->map(function ($item) {
        return [
            'work_date' => \Carbon\Carbon::parse($item['work_date'])->format('d/m/Y'),
            'weekday' => $item['weekday'] ?? '',
            'time' => trim(($item['start_time'] ?? '') . ' - ' . ($item['end_time'] ?? '')),
            'shift_type' => $item['shift_type_label'] ?? $item['shift_type'] ?? '',
            'work_hours' => (float) ($item['work_hours'] ?? 0),
            'shift_coefficient' => (float) ($item['shift_coefficient'] ?? 0),
            'completed_case_count' => (int) ($item['completed_case_count'] ?? 0),
            'service_complexity_total' => (float) ($item['service_complexity_total'] ?? 0),
            'manual_complexity_total' => (float) ($item['manual_complexity_total'] ?? 0),
            'converted_hours' => (float) ($item['converted_hours'] ?? 0),
            'amount' => (float) ($item['amount'] ?? 0),
        ];
    })->values();
@endphp

@section('header-actions')
    <a href="{{ route('doctor.payroll.index') }}" class="btn btn-secondary">
        <i class="ri-arrow-left-line"></i>
        Quay lại
    </a>

@endsection

@section('styles')
<style>
    .sheet {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .sheet-head {
        padding: 18px 22px;
        border-bottom: 1px solid #dbe3ef;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: start;
    }

    .title {
        font-size: 22px;
        font-weight: 950;
        color: #0f172a;
    }

    .meta {
        color: #475569;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
        margin-top: 6px;
    }

    .amount {
        text-align: right;
    }

    .amount strong {
        display: block;
        font-size: 26px;
        font-weight: 950;
        color: #0f172a;
    }

    .badge {
        display: inline-flex;
        padding: 7px 12px;
        border-radius: 999px;
        background: #dcfce7;
        color: #047857;
        font-weight: 900;
        margin-top: 8px;
    }

    .badge.info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.success {
        background: #dcfce7;
        color: #166534;
    }

    .summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        border-bottom: 1px solid #dbe3ef;
    }

    .sum-cell {
        padding: 14px 16px;
        border-right: 1px solid #edf2f7;
    }

    .sum-cell:last-child {
        border-right: 0;
    }

    .sum-cell span {
        display: block;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .sum-cell strong {
        color: #0f172a;
        font-size: 17px;
        font-weight: 950;
    }

    .table-scroll {
        overflow: auto;
    }

    .payroll-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: collapse;
    }

    .payroll-table th {
        background: #e0f2fe;
        color: #0f172a;
        font-size: 12px;
        text-transform: uppercase;
        padding: 12px;
        border: 1px solid #bae6fd;
        text-align: left;
        white-space: nowrap;
    }

    .payroll-table td {
        padding: 11px 12px;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
    }

    .payroll-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }

    .payroll-table tfoot th {
        background: #f1f5f9;
        border: 1px solid #dbe3ef;
        padding: 12px;
        font-weight: 950;
    }

    .right {
        text-align: right;
        white-space: nowrap;
    }

    .center {
        text-align: center;
    }

    .note {
        padding: 16px 22px;
        color: #475569;
        font-weight: 700;
        line-height: 1.6;
    }

    .actions-row {
        padding: 16px 22px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #dbe3ef;
    }

    .btn {
        height: 40px;
        border-radius: 10px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #0f172a;
        cursor: pointer;
    }

    .btn-primary {
        background: #0ea5e9;
        color: #fff;
        border-color: #0ea5e9;
    }

    .btn-secondary:hover {
        background: #f0f9ff;
        color: #0284c7;
    }

    @media(max-width: 900px) {
        .sheet-head,
        .summary {
            grid-template-columns: 1fr;
        }

        .amount {
            text-align: left;
        }
    }

    @media print {
        .sidebar,
        .header,
        .header-actions,
        .actions-row,
        .btn {
            display: none !important;
        }

        .sheet {
            box-shadow: none;
            border: 1px solid #000;
        }

        body {
            background: #fff !important;
        }
    }
</style>
@endsection

@section('content')
<div class="sheet">
    <div class="sheet-head">
        <div>
            <div class="title">BẢNG LƯƠNG BÁC SĨ CHI TIẾT</div>
            <div class="meta">
                Mã phiếu: <strong>{{ $payroll->payroll_code }}</strong><br>
                Bác sĩ: <strong>{{ $payroll->doctor?->name ?? 'Không rõ' }}</strong>
                @if($payroll->doctor?->degree)
                    - {{ $payroll->doctor->degree }}
                @endif
                <br>
                Kỳ lương: <strong>Tháng {{ $payroll->salary_month }}/{{ $payroll->salary_year }}</strong><br>
                Ngày lập:
                {{ optional($payroll->generated_at)->format('d/m/Y H:i') ?? $payroll->created_at?->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="amount">
            <strong>{{ $fmt($payroll->net_amount) }}</strong>
            <span class="badge {{ $payroll->status === 'paid' ? 'success' : 'info' }}">{{ $statusLabel }}</span>
        </div>
    </div>

    <div class="summary">
        <div class="sum-cell">
            <span>Tiền/giờ cơ bản</span>
            <strong>{{ $fmt($payroll->base_hourly_rate) }}</strong>
        </div>
        <div class="sum-cell">
            <span>HS học vị</span>
            <strong>{{ $num($payroll->doctor_coefficient) }}</strong>
        </div>
        <div class="sum-cell">
            <span>Giờ làm việc</span>
            <strong>{{ $num($payroll->total_work_hours) }}</strong>
        </div>
        <div class="sum-cell">
            <span>Giờ quy đổi</span>
            <strong>{{ $num($payroll->total_converted_hours) }}</strong>
        </div>
        <div class="sum-cell">
            <span>Hệ số cộng thêm</span>
            <strong>{{ $num($payroll->total_patient_complexity_coefficient) }}</strong>
        </div>
    </div>

    <div class="table-scroll">
        <table id="payrollTable" class="payroll-table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Thứ</th>
                    <th>Giờ làm</th>
                    <th>Loại ca</th>
                    <th class="center">Số giờ</th>
                    <th class="center">HS ca</th>
                    <th class="center">Số ca khám</th>
                    <th class="center">HS dịch vụ</th>
                    <th class="center">HS cộng thêm</th>
                    <th class="center">Giờ quy đổi</th>
                    <th class="right">Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['work_date'])->format('d/m/Y') }}</td>
                        <td>{{ $item['weekday'] ?? '' }}</td>
                        <td>{{ $item['start_time'] ?? '' }} - {{ $item['end_time'] ?? '' }}</td>
                        <td>{{ $item['shift_type_label'] ?? $item['shift_type'] ?? '' }}</td>
                        <td class="center">{{ $num($item['work_hours'] ?? 0) }}</td>
                        <td class="center">{{ $num($item['shift_coefficient'] ?? 0) }}</td>
                        <td class="center">{{ $item['completed_case_count'] ?? 0 }}</td>
                        <td class="center">{{ $num($item['service_complexity_total'] ?? 0) }}</td>
                        <td class="center">{{ $num($item['manual_complexity_total'] ?? 0) }}</td>
                        <td class="center">{{ $num($item['converted_hours'] ?? 0) }}</td>
                        <td class="right">{{ $fmt($item['amount'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="center">Chưa có dữ liệu chi tiết ca làm việc.</td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="4">Tổng cộng</th>
                    <th class="center">{{ $num($payroll->total_work_hours) }}</th>
                    <th></th>
                    <th></th>
                    <th colspan="2" class="center">{{ $num($payroll->total_patient_complexity_coefficient) }}</th>
                    <th class="center">{{ $num($payroll->total_converted_hours) }}</th>
                    <th class="right">{{ $fmt($payroll->gross_amount) }}</th>
                </tr>
                <tr>
                    <th colspan="10">Thưởng</th>
                    <th class="right">{{ $fmt($payroll->bonus_amount) }}</th>
                </tr>
                <tr>
                    <th colspan="10">Khấu trừ</th>
                    <th class="right">{{ $fmt($payroll->deduction_amount) }}</th>
                </tr>
                <tr>
                    <th colspan="10">Thực nhận</th>
                    <th class="right">{{ $fmt($payroll->net_amount) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($payroll->notes)
        <div class="note">
            <strong>Ghi chú:</strong> {{ $payroll->notes }}
        </div>
    @endif

    <div class="actions-row" style="justify-content: center; border-top: 1px solid #dbe3ef; padding: 16px 22px;">
        <span style="color: #64748b; font-size: 13px; font-weight: 700;">
            <i class="ri-information-line"></i>
            Bảng lương đã được xác nhận bởi Ban Quản Trị. Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ bộ phận Hành chính - Nhân sự.
        </span>
    </div>
</div>
@endsection

@section('scripts')
<script>
const payrollExport = {
    code: @json($payroll->payroll_code),
    doctor: @json($payroll->doctor?->name ?? 'Không rõ'),
    degree: @json($payroll->doctor?->degree ?? ''),
    period: @json('Tháng ' . $payroll->salary_month . '/' . $payroll->salary_year),
    generatedAt: @json(optional($payroll->generated_at)->format('d/m/Y H:i') ?? $payroll->created_at?->format('d/m/Y H:i')),
    status: @json($statusLabel),
    baseHourlyRate: {{ (float) $payroll->base_hourly_rate }},
    doctorCoefficient: {{ (float) $payroll->doctor_coefficient }},
    totalWorkHours: {{ (float) $payroll->total_work_hours }},
    totalConvertedHours: {{ (float) $payroll->total_converted_hours }},
    totalComplexity: {{ (float) $payroll->total_patient_complexity_coefficient }},
    grossAmount: {{ (float) $payroll->gross_amount }},
    bonusAmount: {{ (float) $payroll->bonus_amount }},
    deductionAmount: {{ (float) $payroll->deduction_amount }},
    netAmount: {{ (float) $payroll->net_amount }},
    notes: @json($payroll->notes ?? ''),
    items: @json($excelItems),
};

function xmlEscape(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function excelCell(value, style = 'Text', type = 'String') {
    if (type === 'Number') {
        const number = Number(value || 0);
        return `<Cell ss:StyleID="${style}"><Data ss:Type="Number">${number}</Data></Cell>`;
    }

    return `<Cell ss:StyleID="${style}"><Data ss:Type="String">${xmlEscape(value)}</Data></Cell>`;
}

function emptyCell(count = 1) {
    return '<Cell></Cell>'.repeat(count);
}

function exportPayrollExcel() {
    const p = payrollExport;
    const fileName = `${p.code}-${new Date().toISOString().slice(0, 10)}.xls`;
    const rows = [];

    rows.push(`
        <Row ss:Height="34">
            <Cell ss:MergeAcross="10" ss:StyleID="Title">
                <Data ss:Type="String">BẢNG LƯƠNG BÁC SĨ</Data>
            </Cell>
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            ${excelCell('Mã phiếu', 'Label')}
            ${excelCell(p.code, 'Value')}
            ${excelCell('Bác sĩ', 'Label')}
            ${excelCell(p.doctor, 'Value')}
            ${excelCell('Học vị', 'Label')}
            ${excelCell(p.degree || 'Chưa cập nhật', 'Value')}
            ${excelCell('Trạng thái', 'Label')}
            ${excelCell(p.status, 'Value')}
            ${emptyCell(3)}
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            ${excelCell('Kỳ lương', 'Label')}
            ${excelCell(p.period, 'Value')}
            ${excelCell('Ngày lập', 'Label')}
            ${excelCell(p.generatedAt, 'Value')}
            ${excelCell('Tiền/giờ', 'Label')}
            ${excelCell(p.baseHourlyRate, 'Currency', 'Number')}
            ${excelCell('HS bác sĩ', 'Label')}
            ${excelCell(p.doctorCoefficient, 'Number2', 'Number')}
            ${emptyCell(3)}
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            ${excelCell('Tổng giờ làm', 'Label')}
            ${excelCell(p.totalWorkHours, 'Number2', 'Number')}
            ${excelCell('Tổng giờ quy đổi', 'Label')}
            ${excelCell(p.totalConvertedHours, 'Number2', 'Number')}
            ${excelCell('Tổng hệ số ca bệnh', 'Label')}
            ${excelCell(p.totalComplexity, 'Number2', 'Number')}
            ${emptyCell(5)}
        </Row>
    `);

    rows.push('<Row ss:Height="10"></Row>');

    rows.push(`
        <Row ss:Height="28">
            ${excelCell('NGÀY', 'Header')}
            ${excelCell('THỨ', 'Header')}
            ${excelCell('GIỜ LÀM', 'Header')}
            ${excelCell('LOẠI CA', 'Header')}
            ${excelCell('SỐ GIỜ', 'HeaderCenter')}
            ${excelCell('HS CA', 'HeaderCenter')}
            ${excelCell('SỐ CA KHÁM', 'HeaderCenter')}
            ${excelCell('HS DỊCH VỤ', 'HeaderCenter')}
            ${excelCell('HS CỘNG THÊM', 'HeaderCenter')}
            ${excelCell('GIỜ QUY ĐỔI', 'HeaderCenter')}
            ${excelCell('THÀNH TIỀN', 'HeaderRight')}
        </Row>
    `);

    if (p.items.length === 0) {
        rows.push(`
            <Row ss:Height="26">
                <Cell ss:MergeAcross="10" ss:StyleID="Empty">
                    <Data ss:Type="String">Chưa có dữ liệu chi tiết ca làm việc.</Data>
                </Cell>
            </Row>
        `);
    } else {
        p.items.forEach((item, index) => {
            const rowStyle = index % 2 === 0 ? 'Normal' : 'Alt';

            rows.push(`
                <Row ss:Height="24">
                    ${excelCell(item.work_date, rowStyle)}
                    ${excelCell(item.weekday, rowStyle)}
                    ${excelCell(item.time, rowStyle)}
                    ${excelCell(item.shift_type, rowStyle)}
                    ${excelCell(item.work_hours, rowStyle + 'Number', 'Number')}
                    ${excelCell(item.shift_coefficient, rowStyle + 'Number', 'Number')}
                    ${excelCell(item.completed_case_count, rowStyle + 'Integer', 'Number')}
                    ${excelCell(item.service_complexity_total, rowStyle + 'Number', 'Number')}
                    ${excelCell(item.manual_complexity_total, rowStyle + 'Number', 'Number')}
                    ${excelCell(item.converted_hours, rowStyle + 'Number', 'Number')}
                    ${excelCell(item.amount, rowStyle + 'Currency', 'Number')}
                </Row>
            `);
        });
    }

    rows.push('<Row ss:Height="10"></Row>');

    rows.push(`
        <Row ss:Height="26">
            <Cell ss:MergeAcross="3" ss:StyleID="TotalLabel">
                <Data ss:Type="String">TỔNG CỘNG</Data>
            </Cell>
            ${excelCell(p.totalWorkHours, 'TotalNumber', 'Number')}
            ${emptyCell(2)}
            <Cell ss:MergeAcross="1" ss:StyleID="TotalNumber">
                <Data ss:Type="Number">${Number(p.totalComplexity || 0)}</Data>
            </Cell>
            ${excelCell(p.totalConvertedHours, 'TotalNumber', 'Number')}
            ${excelCell(p.grossAmount, 'TotalCurrency', 'Number')}
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            <Cell ss:MergeAcross="9" ss:StyleID="SummaryLabel">
                <Data ss:Type="String">THƯỞNG</Data>
            </Cell>
            ${excelCell(p.bonusAmount, 'SummaryCurrency', 'Number')}
        </Row>
    `);

    rows.push(`
        <Row ss:Height="24">
            <Cell ss:MergeAcross="9" ss:StyleID="SummaryLabel">
                <Data ss:Type="String">KHẤU TRỪ</Data>
            </Cell>
            ${excelCell(p.deductionAmount, 'SummaryCurrency', 'Number')}
        </Row>
    `);

    rows.push(`
        <Row ss:Height="30">
            <Cell ss:MergeAcross="9" ss:StyleID="NetLabel">
                <Data ss:Type="String">THỰC NHẬN</Data>
            </Cell>
            ${excelCell(p.netAmount, 'NetCurrency', 'Number')}
        </Row>
    `);

    if (p.notes) {
        rows.push('<Row ss:Height="10"></Row>');
        rows.push(`
            <Row ss:Height="34">
                <Cell ss:MergeAcross="10" ss:StyleID="Note">
                    <Data ss:Type="String">Ghi chú: ${xmlEscape(p.notes)}</Data>
                </Cell>
            </Row>
        `);
    }

    const workbook =
        '<' + '?xml version="1.0" encoding="UTF-8"?>\n' +
        '<' + '?mso-application progid="Excel.Sheet"?>\n' +
        String.raw`<Workbook
    xmlns="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

    <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
        <Author>DentalCare</Author>
        <Company>DentalCare</Company>
    </DocumentProperties>

    <Styles>
        <Style ss:ID="Default" ss:Name="Normal">
            <Alignment ss:Vertical="Center"/>
            <Font ss:FontName="Calibri" ss:Size="11"/>
        </Style>

        <Style ss:ID="Title">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Font ss:FontName="Calibri" ss:Size="18" ss:Bold="1" ss:Color="#0F172A"/>
            <Interior ss:Color="#E0F2FE" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#38BDF8"/>
            </Borders>
        </Style>

        <Style ss:ID="Label">
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#334155"/>
            <Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="Value">
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#0F172A"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="Header">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#0284C7" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" stroke-linecap="round" ss:Color="#0369A1"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0369A1"/>
            </Borders>
        </Style>

        <Style ss:ID="HeaderCenter" ss:Parent="Header">
            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        </Style>

        <Style ss:ID="HeaderRight" ss:Parent="Header">
            <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
        </Style>

        <Style ss:ID="Normal">
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="Alt">
            <Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
            </Borders>
        </Style>

        <Style ss:ID="NormalNumber" ss:Parent="Normal">
            <Alignment ss:Horizontal="Center"/>
            <NumberFormat ss:Format="0.00"/>
        </Style>

        <Style ss:ID="AltNumber" ss:Parent="Alt">
            <Alignment ss:Horizontal="Center"/>
            <NumberFormat ss:Format="0.00"/>
        </Style>

        <Style ss:ID="NormalInteger" ss:Parent="Normal">
            <Alignment ss:Horizontal="Center"/>
            <NumberFormat ss:Format="0"/>
        </Style>

        <Style ss:ID="AltInteger" ss:Parent="Alt">
            <Alignment ss:Horizontal="Center"/>
            <NumberFormat ss:Format="0"/>
        </Style>

        <Style ss:ID="NormalCurrency" ss:Parent="Normal">
            <Alignment ss:Horizontal="Right"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="AltCurrency" ss:Parent="Alt">
            <Alignment ss:Horizontal="Right"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="Number2">
            <NumberFormat ss:Format="0.00"/>
        </Style>

        <Style ss:ID="Currency">
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="TotalLabel">
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#0F172A"/>
            <Interior ss:Color="#E0F2FE" ss:Pattern="Solid"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BAE6FD"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BAE6FD"/>
            </Borders>
        </Style>

        <Style ss:ID="TotalNumber" ss:Parent="TotalLabel">
            <Alignment ss:Horizontal="Center"/>
            <NumberFormat ss:Format="0.00"/>
        </Style>

        <Style ss:ID="TotalCurrency" ss:Parent="TotalLabel">
            <Alignment ss:Horizontal="Right"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="SummaryLabel">
            <Alignment ss:Horizontal="Right"/>
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#334155"/>
        </Style>

        <Style ss:ID="SummaryCurrency">
            <Alignment ss:Horizontal="Right"/>
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Color="#334155"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="NetLabel">
            <Alignment ss:Horizontal="Right"/>
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Size="13" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#16A34A" ss:Pattern="Solid"/>
        </Style>

        <Style ss:ID="NetCurrency">
            <Alignment ss:Horizontal="Right"/>
            <Font ss:FontName="Calibri" ss:Bold="1" ss:Size="13" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#16A34A" ss:Pattern="Solid"/>
            <NumberFormat ss:Format="#,##0 [$₫-vi-VN]"/>
        </Style>

        <Style ss:ID="Empty">
            <Alignment ss:Horizontal="Center"/>
            <Font ss:FontName="Calibri" ss:Italic="1" ss:Color="#64748b"/>
        </Style>

        <Style ss:ID="Note">
            <Alignment ss:WrapText="1"/>
            <Font ss:FontName="Calibri" ss:Italic="1" ss:Color="#475569"/>
            <Interior ss:Color="#FFFBEB" ss:Pattern="Solid"/>
        </Style>
    </Styles>

    <Worksheet ss:Name="Bang luong">
        <Table>
            <Column ss:Width="105"/>
            <Column ss:Width="95"/>
            <Column ss:Width="120"/>
            <Column ss:Width="125"/>
            <Column ss:Width="80"/>
            <Column ss:Width="80"/>
            <Column ss:Width="95"/>
            <Column ss:Width="95"/>
            <Column ss:Width="110"/>
            <Column ss:Width="105"/>
            <Column ss:Width="135"/>
            ${rows.join('')}
        </Table>

        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
            <FreezePanes/>
            <FrozenNoSplit/>
            <SplitHorizontal>6</SplitHorizontal>
            <TopRowBottomPane>6</TopRowBottomPane>
            <ActivePane>2</ActivePane>
            <ProtectObjects>False</ProtectObjects>
            <ProtectScenarios>False</ProtectScenarios>
        </WorksheetOptions>
    </Worksheet>
</Workbook>`;

    const blob = new Blob([workbook], { type: 'application/vnd.ms-excel;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection
