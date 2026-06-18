<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorCaseComplexity;
use App\Models\DoctorPayroll;
use App\Models\Employee;
use App\Models\SalaryConfig;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DoctorPayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $doctorId = $request->input('doctor_id');

        $doctors = $this->doctorQuery()->get();

        $payrolls = DoctorPayroll::query()
            ->with(['doctor', 'salaryConfig', 'creator', 'approver'])
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
            ->latest('generated_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summaryQuery = DoctorPayroll::query()
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId));

        $summary = [
            'total_payrolls' => (clone $summaryQuery)->count(),
            'gross_amount' => (clone $summaryQuery)->sum('gross_amount'),
            'net_amount' => (clone $summaryQuery)->sum('net_amount'),
            'paid_amount' => (clone $summaryQuery)->where('status', 'paid')->sum('net_amount'),
        ];

        return view('admin.payroll.index', compact(
            'doctors',
            'payrolls',
            'summary',
            'month',
            'year',
            'doctorId'
        ));
    }

    public function settings()
    {
        $config = SalaryConfig::current();

        if (!$config) {
            $config = SalaryConfig::create($this->filterSalaryConfigColumns([
                'name' => 'Cấu hình lương mặc định',
                'base_hourly_rate' => 0,

                'degree_bachelor_coefficient' => 1.30,
                'degree_master_coefficient' => 1.50,
                'degree_doctor_coefficient' => 1.70,
                'degree_associate_professor_coefficient' => 2.00,
                'degree_professor_coefficient' => 2.50,
                'degree_default_coefficient' => 1.30,

                'weekday_office_coefficient' => 1.00,
                'weekday_overtime_coefficient' => 1.20,
                'weekend_coefficient' => 1.50,

                'morning_shift_coefficient' => 1.00,
                'evening_shift_coefficient' => 1.20,

                'office_start_time' => '08:00',
                'office_end_time' => '17:00',
                'morning_shift_start' => '08:00',
                'morning_shift_end' => '17:00',
                'evening_shift_start' => '14:00',
                'evening_shift_end' => '22:00',

                'exclude_lunch_break' => true,
                'lunch_start_time' => '11:30',
                'lunch_end_time' => '12:30',

                'exclude_evening_break' => true,
                'evening_break_start_time' => '17:30',
                'evening_break_end_time' => '18:00',

                'is_active' => true,
            ]));
        }

        return view('admin.payroll.settings', compact('config'));
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'base_hourly_rate' => 'required|numeric|min:0',

            'degree_bachelor_coefficient' => 'required|numeric|min:0|max:10',
            'degree_master_coefficient' => 'required|numeric|min:0|max:10',
            'degree_doctor_coefficient' => 'required|numeric|min:0|max:10',
            'degree_associate_professor_coefficient' => 'required|numeric|min:0|max:10',
            'degree_professor_coefficient' => 'required|numeric|min:0|max:10',
            'degree_default_coefficient' => 'required|numeric|min:0|max:10',

            'weekday_office_coefficient' => 'required|numeric|min:0|max:10',
            'weekday_overtime_coefficient' => 'required|numeric|min:0|max:10',
            'weekend_coefficient' => 'required|numeric|min:0|max:10',

            'morning_shift_coefficient' => 'nullable|numeric|min:0|max:10',
            'evening_shift_coefficient' => 'nullable|numeric|min:0|max:10',

            'office_start_time' => 'nullable|date_format:H:i',
            'office_end_time' => 'nullable|date_format:H:i',

            'morning_shift_start' => 'nullable|date_format:H:i',
            'morning_shift_end' => 'nullable|date_format:H:i',
            'evening_shift_start' => 'nullable|date_format:H:i',
            'evening_shift_end' => 'nullable|date_format:H:i',

            'lunch_start_time' => 'required|date_format:H:i',
            'lunch_end_time' => 'required|date_format:H:i',

            'evening_break_start_time' => 'nullable|date_format:H:i',
            'evening_break_end_time' => 'nullable|date_format:H:i',

            'notes' => 'nullable|string',
        ]);

        $morningStart = $validated['morning_shift_start']
            ?? $validated['office_start_time']
            ?? '08:00';

        $morningEnd = $validated['morning_shift_end']
            ?? $validated['office_end_time']
            ?? '17:00';

        $eveningStart = $validated['evening_shift_start'] ?? '14:00';
        $eveningEnd = $validated['evening_shift_end'] ?? '22:00';

        SalaryConfig::query()->update(['is_active' => false]);

        SalaryConfig::create($this->filterSalaryConfigColumns([
            'name' => $validated['name'] ?: 'Cấu hình lương áp dụng từ ' . now()->format('d/m/Y H:i'),
            'base_hourly_rate' => $validated['base_hourly_rate'],

            'degree_bachelor_coefficient' => $validated['degree_bachelor_coefficient'],
            'degree_master_coefficient' => $validated['degree_master_coefficient'],
            'degree_doctor_coefficient' => $validated['degree_doctor_coefficient'],
            'degree_associate_professor_coefficient' => $validated['degree_associate_professor_coefficient'],
            'degree_professor_coefficient' => $validated['degree_professor_coefficient'],
            'degree_default_coefficient' => $validated['degree_default_coefficient'],

            'weekday_office_coefficient' => $validated['weekday_office_coefficient'],
            'weekday_overtime_coefficient' => $validated['weekday_overtime_coefficient'],
            'weekend_coefficient' => $validated['weekend_coefficient'],

            'morning_shift_coefficient' => $validated['morning_shift_coefficient']
                ?? $validated['weekday_office_coefficient'],

            'evening_shift_coefficient' => $validated['evening_shift_coefficient']
                ?? $validated['weekday_overtime_coefficient'],

            'office_start_time' => $validated['office_start_time'] ?? $morningStart,
            'office_end_time' => $validated['office_end_time'] ?? $morningEnd,

            'morning_shift_start' => $morningStart,
            'morning_shift_end' => $morningEnd,
            'evening_shift_start' => $eveningStart,
            'evening_shift_end' => $eveningEnd,

            'exclude_lunch_break' => $request->boolean('exclude_lunch_break'),
            'lunch_start_time' => $validated['lunch_start_time'],
            'lunch_end_time' => $validated['lunch_end_time'],

            'exclude_evening_break' => $request->boolean('exclude_evening_break'),
            'evening_break_start_time' => $validated['evening_break_start_time'] ?? '17:30',
            'evening_break_end_time' => $validated['evening_break_end_time'] ?? '18:00',

            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]));

        return back()->with('success', 'Đã lưu cấu hình tính lương mới.');
    }

    public function caseComplexities(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $doctorId = $request->input('doctor_id');

        $doctors = $this->doctorQuery()->get();

        $services = Service::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $complexities = DoctorCaseComplexity::query()
            ->with([
                'doctor',
                'appointment.service',
                'appointment.patient',
                'appointment.patientProfile',
                'patientProfile',
                'creator',
            ])
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
            ->latest('case_date')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $appointments = Appointment::query()
            ->with(['patient', 'patientProfile', 'service', 'doctor'])
            ->where('status', 'completed')
            ->whereYear('appointment_date', $year)
            ->whereMonth('appointment_date', $month)
            ->when($doctorId, fn ($query) => $query->where('doctor_id', $doctorId))
            ->latest('appointment_date')
            ->limit(100)
            ->get();

        return view('admin.payroll.case-complexities', compact(
            'doctors',
            'services',
            'complexities',
            'appointments',
            'month',
            'year',
            'doctorId'
        ));
    }

    public function updateServiceComplexities(Request $request)
    {
        $validated = $request->validate([
            'service_coefficients' => 'required|array',
            'service_coefficients.*' => 'nullable|numeric|min:0|max:0.5',
        ]);

        if (!Schema::hasColumn('services', 'salary_complexity_coefficient')) {
            return back()->with('error', 'Bảng services chưa có cột salary_complexity_coefficient.');
        }

        foreach ($validated['service_coefficients'] as $serviceId => $coefficient) {
            Service::where('id', $serviceId)->update([
                'salary_complexity_coefficient' => max(0, min((float) ($coefficient ?? 0), 0.5)),
            ]);
        }

        return back()->with('success', 'Đã cập nhật hệ số xử lý theo dịch vụ.');
    }

    public function storeCaseComplexity(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:employees,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_profile_id' => 'nullable|exists:patient_profiles,id',
            'salary_month' => 'required|integer|min:1|max:12',
            'salary_year' => 'required|integer|min:2000|max:2100',
            'case_date' => 'required|date',
            'complexity_coefficient' => 'required|numeric|min:0.1|max:0.5',
            'case_title' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::with(['service'])->find($validated['appointment_id']);

            if ($appointment) {
                $validated['doctor_id'] = $appointment->doctor_id;
                $validated['patient_profile_id'] = $appointment->patient_profile_id ?: ($validated['patient_profile_id'] ?? null);
                $validated['case_date'] = $appointment->appointment_date->toDateString();
                $validated['salary_month'] = (int) $appointment->appointment_date->month;
                $validated['salary_year'] = (int) $appointment->appointment_date->year;
                $validated['case_title'] = $validated['case_title'] ?: ($appointment->service?->name ?? 'Ca bệnh phức tạp');
            }
        }

        $doctor = $this->doctorQuery()->where('id', $validated['doctor_id'])->first();

        if (!$doctor) {
            return back()->with('error', 'Bác sĩ không hợp lệ.');
        }

        $validated['created_by'] = Auth::id();

        DoctorCaseComplexity::create($validated);

        return back()->with('success', 'Đã ghi nhận hệ số cộng thêm cho ca bệnh đặc biệt.');
    }

    public function deleteCaseComplexity(DoctorCaseComplexity $complexity)
    {
        $complexity->delete();

        return back()->with('success', 'Đã xóa hệ số ca bệnh phức tạp.');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:employees,id',
            'salary_month' => 'required|integer|min:1|max:12',
            'salary_year' => 'required|integer|min:2000|max:2100',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $doctor = $this->doctorQuery()->where('id', $validated['doctor_id'])->first();

        if (!$doctor) {
            return back()->with('error', 'Bác sĩ không hợp lệ.');
        }

        $config = SalaryConfig::current();

        if (!$config) {
            return back()->with('error', 'Chưa có cấu hình tính lương. Vui lòng thiết lập mức tiền một giờ trước.');
        }

        if ((float) $config->base_hourly_rate <= 0) {
            return back()->with('error', 'Mức tiền cơ bản cho một giờ phải lớn hơn 0.');
        }

        $existingPayroll = DoctorPayroll::where('doctor_id', $doctor->id)
            ->where('salary_month', $validated['salary_month'])
            ->where('salary_year', $validated['salary_year'])
            ->first();

        if ($existingPayroll && in_array($existingPayroll->status, ['approved', 'paid'], true)) {
            return back()->with('error', 'Phiếu lương tháng này đã được duyệt hoặc đã thanh toán, không thể lập lại.');
        }

        $shifts = $this->getDoctorApprovedShifts(
            $doctor->id,
            (int) $validated['salary_month'],
            (int) $validated['salary_year']
        );

        if ($shifts->isEmpty()) {
            return back()->with('error', 'Bác sĩ này chưa có ca làm việc đã duyệt trong tháng được chọn.');
        }

        $appointments = $this->getDoctorCompletedAppointments(
            $doctor->id,
            (int) $validated['salary_month'],
            (int) $validated['salary_year']
        );

        $manualComplexities = $this->getDoctorComplexities(
            $doctor->id,
            (int) $validated['salary_month'],
            (int) $validated['salary_year']
        );

        $doctorCoefficient = $config->getDoctorCoefficientByDegree($doctor->degree ?? null);

        $items = [];
        $totalWorkHours = 0;
        $totalComplexityCoefficient = 0;
        $totalConvertedHours = 0;
        $grossAmount = 0;

        $firstShiftIdsByDate = $shifts
            ->groupBy('work_date')
            ->map(fn ($items) => $items->sortBy('start_hour')->first()->id)
            ->toArray();

        foreach ($shifts as $shift) {
            $workDate = Carbon::parse($shift->work_date);

            $start = $this->makeShiftTime($shift->work_date, $shift->start_hour, $shift->start_minute);
            $end = $this->makeShiftTime($shift->work_date, $shift->end_hour, $shift->end_minute);

            if (!$start || !$end) {
                continue;
            }

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            $workHours = $this->calculateWorkHours($start, $end, $config);
            $shiftType = $this->resolveShiftType($start, $end, $config);
            $shiftCoefficient = $this->resolveShiftCoefficientByType($shiftType, $config);

            $serviceComplexityTotal = $this->sumServiceComplexitiesForShift($appointments, $start, $end);
            $manualComplexityTotal = $this->sumManualComplexitiesForShift(
                $manualComplexities,
                $shift,
                $start,
                $end,
                $firstShiftIdsByDate
            );

            $complexityTotal = $serviceComplexityTotal + $manualComplexityTotal;
            $completedCaseCount = $this->countAppointmentsForShift($appointments, $start, $end);

            $convertedHours = $workHours * ($shiftCoefficient + $complexityTotal);
            $amount = $convertedHours * $doctorCoefficient * (float) $config->base_hourly_rate;

            $items[] = [
                'shift_assignment_id' => $shift->id,
                'work_date' => $workDate->toDateString(),
                'weekday' => $workDate->isoFormat('dddd'),
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'assignment_type' => $shift->assignment_type,
                'shift_type' => $shiftType,
                'shift_type_label' => $this->getShiftTypeLabel($shiftType),
                'work_hours' => round($workHours, 2),
                'shift_coefficient' => round($shiftCoefficient, 2),
                'completed_case_count' => $completedCaseCount,
                'service_complexity_total' => round($serviceComplexityTotal, 2),
                'manual_complexity_total' => round($manualComplexityTotal, 2),
                'patient_complexity_total' => round($complexityTotal, 2),
                'converted_hours' => round($convertedHours, 2),
                'doctor_coefficient' => round($doctorCoefficient, 2),
                'base_hourly_rate' => (float) $config->base_hourly_rate,
                'amount' => round($amount, 2),
            ];

            $totalWorkHours += $workHours;
            $totalComplexityCoefficient += $complexityTotal;
            $totalConvertedHours += $convertedHours;
            $grossAmount += $amount;
        }

        if (empty($items)) {
            return back()->with('error', 'Không có ca làm việc hợp lệ để lập phiếu lương.');
        }

        $bonusAmount = (float) ($validated['bonus_amount'] ?? 0);
        $deductionAmount = (float) ($validated['deduction_amount'] ?? 0);
        $netAmount = max($grossAmount + $bonusAmount - $deductionAmount, 0);

        $payrollCode = DoctorPayroll::generateCode(
            $doctor->id,
            (int) $validated['salary_month'],
            (int) $validated['salary_year']
        );

        $payroll = DoctorPayroll::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'salary_month' => $validated['salary_month'],
                'salary_year' => $validated['salary_year'],
            ],
            [
                'payroll_code' => $existingPayroll?->payroll_code ?: $payrollCode,
                'salary_config_id' => $config->id,
                'created_by' => Auth::id(),

                'base_hourly_rate' => $config->base_hourly_rate,
                'doctor_coefficient' => $doctorCoefficient,

                'total_work_hours' => round($totalWorkHours, 2),
                'total_patient_complexity_coefficient' => round($totalComplexityCoefficient, 2),
                'total_converted_hours' => round($totalConvertedHours, 2),

                'gross_amount' => round($grossAmount, 2),
                'bonus_amount' => round($bonusAmount, 2),
                'deduction_amount' => round($deductionAmount, 2),
                'net_amount' => round($netAmount, 2),

                'items' => $items,
                'status' => 'draft',
                'generated_at' => now(),
                'approved_at' => null,
                'approved_by' => null,
                'paid_at' => null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.payroll.show', $payroll)
            ->with('success', 'Đã lập phiếu lương cho bác sĩ.');
    }

    public function show(DoctorPayroll $payroll)
    {
        $payroll->load(['doctor', 'salaryConfig', 'creator', 'approver']);

        $complexities = DoctorCaseComplexity::query()
            ->with(['appointment.service', 'patientProfile'])
            ->where('doctor_id', $payroll->doctor_id)
            ->where('salary_month', $payroll->salary_month)
            ->where('salary_year', $payroll->salary_year)
            ->latest('case_date')
            ->get();

        return view('admin.payroll.show', compact('payroll', 'complexities'));
    }

    public function approve(DoctorPayroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Chỉ có thể duyệt phiếu lương đang ở trạng thái nháp.');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt phiếu lương.');
    }

    public function markPaid(DoctorPayroll $payroll)
    {
        if (!in_array($payroll->status, ['draft', 'approved'], true)) {
            return back()->with('error', 'Không thể thanh toán phiếu lương này.');
        }

        $payroll->update([
            'status' => 'paid',
            'approved_by' => $payroll->approved_by ?: Auth::id(),
            'approved_at' => $payroll->approved_at ?: now(),
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Đã đánh dấu phiếu lương là đã thanh toán.');
    }

    public function monthlyReport(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $payrolls = DoctorPayroll::query()
            ->with('doctor')
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->orderByDesc('net_amount')
            ->get();

        $report = [
            'mode' => 'monthly',
            'title' => 'Báo cáo tiền lương tất cả bác sĩ trong tháng',
            'month' => $month,
            'year' => $year,
            'doctor_count' => $payrolls->count(),
            'total_work_hours' => $payrolls->sum('total_work_hours'),
            'total_converted_hours' => $payrolls->sum('total_converted_hours'),
            'gross_amount' => $payrolls->sum('gross_amount'),
            'net_amount' => $payrolls->sum('net_amount'),
            'payrolls' => $payrolls,
        ];

        return view('admin.payroll.reports', compact('report'));
    }

    public function doctorYearlyReport(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:employees,id',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $doctor = $this->doctorQuery()->findOrFail($validated['doctor_id']);

        $payrolls = DoctorPayroll::query()
            ->where('doctor_id', $doctor->id)
            ->where('salary_year', $year)
            ->orderBy('salary_month')
            ->get();

        $report = [
            'mode' => 'doctor-yearly',
            'title' => 'Báo cáo tiền lương của một bác sĩ trong một năm',
            'doctor' => $doctor,
            'year' => $year,
            'total_work_hours' => $payrolls->sum('total_work_hours'),
            'total_converted_hours' => $payrolls->sum('total_converted_hours'),
            'gross_amount' => $payrolls->sum('gross_amount'),
            'net_amount' => $payrolls->sum('net_amount'),
            'payrolls' => $payrolls,
        ];

        return view('admin.payroll.reports', compact('report'));
    }

    public function yearlyReport(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        $payrolls = DoctorPayroll::query()
            ->with('doctor')
            ->where('salary_year', $year)
            ->get();

        $doctorReports = $payrolls
            ->groupBy('doctor_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'doctor' => $first->doctor,
                    'months_count' => $items->count(),
                    'total_work_hours' => $items->sum('total_work_hours'),
                    'total_converted_hours' => $items->sum('total_converted_hours'),
                    'gross_amount' => $items->sum('gross_amount'),
                    'net_amount' => $items->sum('net_amount'),
                    'paid_amount' => $items->where('status', 'paid')->sum('net_amount'),
                ];
            })
            ->sortByDesc('net_amount')
            ->values();

        $report = [
            'mode' => 'yearly',
            'title' => 'Báo cáo tiền lương tất cả bác sĩ trong một năm',
            'year' => $year,
            'doctor_count' => $doctorReports->count(),
            'gross_amount' => $doctorReports->sum('gross_amount'),
            'net_amount' => $doctorReports->sum('net_amount'),
            'paid_amount' => $doctorReports->sum('paid_amount'),
            'doctor_reports' => $doctorReports,
        ];

        return view('admin.payroll.reports', compact('report'));
    }

    private function doctorQuery()
    {
        return Employee::query()
            ->where('is_doctor', 1)
            ->orderBy('name');
    }

    private function getDoctorApprovedShifts(int $doctorId, int $month, int $year)
    {
        return DB::table('shift_assignments')
            ->where('employee_id', $doctorId)
            ->where('status', 'approved')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->whereNotNull('start_hour')
            ->whereNotNull('end_hour')
            ->orderBy('work_date')
            ->orderBy('start_hour')
            ->orderBy('start_minute')
            ->get();
    }

    private function getDoctorCompletedAppointments(int $doctorId, int $month, int $year)
    {
        return Appointment::query()
            ->with(['service'])
            ->where('doctor_id', $doctorId)
            ->where('status', 'completed')
            ->whereYear('appointment_date', $year)
            ->whereMonth('appointment_date', $month)
            ->get();
    }

    private function getDoctorComplexities(int $doctorId, int $month, int $year)
    {
        return DoctorCaseComplexity::query()
            ->with(['appointment'])
            ->where('doctor_id', $doctorId)
            ->where('salary_month', $month)
            ->where('salary_year', $year)
            ->get();
    }

    private function makeShiftTime($date, $hour, $minute): ?Carbon
    {
        if ($hour === null) {
            return null;
        }

        return Carbon::parse($date)
            ->setHour((int) $hour)
            ->setMinute((int) ($minute ?? 0))
            ->setSecond(0);
    }

    private function calculateWorkHours(Carbon $start, Carbon $end, SalaryConfig $config): float
    {
        $minutes = max($start->diffInMinutes($end), 0);

        if ($config->exclude_lunch_break) {
            $minutes -= $this->calculateBreakOverlapMinutes(
                $start,
                $end,
                $config->lunch_start_time ?? '11:30:00',
                $config->lunch_end_time ?? '12:30:00'
            );
        }

        if ($config->exclude_evening_break) {
            $minutes -= $this->calculateBreakOverlapMinutes(
                $start,
                $end,
                $config->evening_break_start_time ?? '17:30:00',
                $config->evening_break_end_time ?? '18:00:00'
            );
        }

        return round(max($minutes, 0) / 60, 2);
    }

    private function calculateBreakOverlapMinutes(Carbon $start, Carbon $end, string $breakStartTime, string $breakEndTime): int
    {
        $breakStart = Carbon::parse($start->toDateString() . ' ' . $breakStartTime);
        $breakEnd = Carbon::parse($start->toDateString() . ' ' . $breakEndTime);

        if ($breakEnd->lessThanOrEqualTo($breakStart)) {
            $breakEnd->addDay();
        }

        $overlapStart = $start->greaterThan($breakStart) ? $start : $breakStart;
        $overlapEnd = $end->lessThan($breakEnd) ? $end : $breakEnd;

        if ($overlapEnd->greaterThan($overlapStart)) {
            return $overlapStart->diffInMinutes($overlapEnd);
        }

        return 0;
    }

    private function resolveShiftType(Carbon $start, Carbon $end, SalaryConfig $config): string
    {
        if ($start->isWeekend()) {
            return 'weekend';
        }

        $morningStart = $this->configTime($start, $config, 'morning_shift_start', '08:00:00');
        $morningEnd = $this->configTime($start, $config, 'morning_shift_end', '17:00:00');

        $eveningStart = $this->configTime($start, $config, 'evening_shift_start', '14:00:00');
        $eveningEnd = $this->configTime($start, $config, 'evening_shift_end', '22:00:00');

        if ($morningEnd->lessThanOrEqualTo($morningStart)) {
            $morningEnd->addDay();
        }

        if ($eveningEnd->lessThanOrEqualTo($eveningStart)) {
            $eveningEnd->addDay();
        }

        if ($this->sameShiftWindow($start, $end, $morningStart, $morningEnd)) {
            return 'morning';
        }

        if ($this->sameShiftWindow($start, $end, $eveningStart, $eveningEnd)) {
            return 'evening';
        }

        if ($this->isInsideWindow($start, $end, $morningStart, $morningEnd) && $start->lt($eveningStart)) {
            return 'morning';
        }

        if ($this->isInsideWindow($start, $end, $eveningStart, $eveningEnd)) {
            return 'evening';
        }

        return 'overtime';
    }

    private function resolveShiftCoefficientByType(string $shiftType, SalaryConfig $config): float
{
    return match ($shiftType) {
        'morning' => (float) ($config->morning_shift_coefficient ?? $config->weekday_office_coefficient ?? 1.00),

        // Ca tối 14:00 - 22:00 vẫn là ca hành chính của phòng khám,
        // nên mặc định dùng hệ số hành chính, không dùng hệ số ngoài giờ.
        'evening' => (float) ($config->evening_shift_coefficient ?? $config->weekday_office_coefficient ?? 1.00),

        'weekend' => (float) ($config->weekend_coefficient ?? 1.50),

        default => (float) ($config->weekday_overtime_coefficient ?? 1.20),
    };
}

    private function getShiftTypeLabel(string $shiftType): string
    {
        return match ($shiftType) {
            'morning' => 'Ca sáng',
            'evening' => 'Ca tối',
            'weekend' => 'Cuối tuần',
            default => 'Ngoài giờ / tăng ca',
        };
    }

    private function configTime(Carbon $date, SalaryConfig $config, string $column, string $default): Carbon
    {
        $value = $config->{$column} ?? $default;

        return Carbon::parse($date->toDateString() . ' ' . $value);
    }

    private function sameShiftWindow(Carbon $start, Carbon $end, Carbon $shiftStart, Carbon $shiftEnd): bool
    {
        return $start->format('H:i') === $shiftStart->format('H:i')
            && $end->format('H:i') === $shiftEnd->format('H:i');
    }

    private function isInsideWindow(Carbon $start, Carbon $end, Carbon $windowStart, Carbon $windowEnd): bool
    {
        return $start->greaterThanOrEqualTo($windowStart)
            && $end->lessThanOrEqualTo($windowEnd);
    }

    private function sumServiceComplexitiesForShift($appointments, Carbon $start, Carbon $end): float
    {
        return (float) $appointments
            ->filter(function ($appointment) use ($start, $end) {
                $appointmentTime = Carbon::parse($appointment->appointment_date);

                return $appointmentTime->greaterThanOrEqualTo($start)
                    && $appointmentTime->lessThan($end);
            })
            ->sum(function ($appointment) {
                return (float) ($appointment->service?->salary_complexity_coefficient ?? 0);
            });
    }

    private function countAppointmentsForShift($appointments, Carbon $start, Carbon $end): int
    {
        return $appointments
            ->filter(function ($appointment) use ($start, $end) {
                $appointmentTime = Carbon::parse($appointment->appointment_date);

                return $appointmentTime->greaterThanOrEqualTo($start)
                    && $appointmentTime->lessThan($end);
            })
            ->count();
    }

    private function sumManualComplexitiesForShift($complexities, $shift, Carbon $start, Carbon $end, array $firstShiftIdsByDate): float
    {
        $workDate = Carbon::parse($shift->work_date)->toDateString();
        $firstShiftIdOfDate = $firstShiftIdsByDate[$workDate] ?? null;

        return (float) $complexities
            ->filter(function ($complexity) use ($workDate, $start, $end, $shift, $firstShiftIdOfDate) {
                if ($complexity->appointment && $complexity->appointment->appointment_date) {
                    $appointmentTime = Carbon::parse($complexity->appointment->appointment_date);

                    return $appointmentTime->toDateString() === $workDate
                        && $appointmentTime->greaterThanOrEqualTo($start)
                        && $appointmentTime->lessThan($end);
                }

                if ($complexity->case_date?->toDateString() !== $workDate) {
                    return false;
                }

                return (int) $shift->id === (int) $firstShiftIdOfDate;
            })
            ->sum('complexity_coefficient');
    }

    private function filterSalaryConfigColumns(array $data): array
    {
        $columns = Schema::getColumnListing('salary_configs');

        return array_intersect_key($data, array_flip($columns));
    }

    public function doctorIndex(Request $request)
    {
        $doctorId = $this->getDoctorIdForUser();
        if (!$doctorId) {
            abort(403, 'Tài khoản không liên kết với thông tin bác sĩ.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $query = DoctorPayroll::query()
            ->where('doctor_id', $doctorId)
            ->whereIn('status', ['approved', 'paid'])
            ->where('salary_month', $month)
            ->where('salary_year', $year);

        $summaryQuery = clone $query;

        $summary = [
            'total_payrolls' => $summaryQuery->count(),
            'gross_amount' => $summaryQuery->sum('gross_amount'),
            'net_amount' => $summaryQuery->sum('net_amount'),
            'paid_amount' => $summaryQuery->where('status', 'paid')->sum('net_amount'),
        ];

        $payrolls = $query
            ->with(['doctor', 'salaryConfig', 'creator', 'approver'])
            ->latest('generated_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('doctor.payroll.index', compact(
            'payrolls',
            'summary',
            'month',
            'year'
        ));
    }

    public function doctorShow(DoctorPayroll $payroll)
    {
        $doctorId = $this->getDoctorIdForUser();
        if (!$doctorId || $payroll->doctor_id !== $doctorId || !in_array($payroll->status, ['approved', 'paid'], true)) {
            abort(403, 'Bạn không có quyền xem phiếu lương này hoặc phiếu lương chưa được xác nhận.');
        }

        $payroll->load(['doctor', 'salaryConfig', 'creator', 'approver']);

        $complexities = DoctorCaseComplexity::query()
            ->with(['appointment.service', 'patientProfile'])
            ->where('doctor_id', $payroll->doctor_id)
            ->where('salary_month', $payroll->salary_month)
            ->where('salary_year', $payroll->salary_year)
            ->latest('case_date')
            ->get();

        return view('doctor.payroll.show', compact('payroll', 'complexities'));
    }

    private function getDoctorIdForUser(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Tìm Employee liên kết với user này (không tự tạo để tránh lỗi DB)
        $doctor = Employee::where('user_id', $user->id)->first();

        if ($doctor) {
            // Nếu là doctor role mà is_doctor chưa được đánh dấu → tự động cập nhật
            if ($user->role === 'doctor' && !$doctor->is_doctor) {
                $doctor->update(['is_doctor' => 1]);
                $doctor->is_doctor = 1;
            }
            // Trả về id nếu là bác sĩ (role doctor hoặc is_doctor = true)
            if ($user->role === 'doctor' || $doctor->is_doctor) {
                return $doctor->id;
            }
        }

        return null;
    }
}