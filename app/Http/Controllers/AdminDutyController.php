<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\CustomShift;
use App\Models\ShiftAssignment;
use App\Models\ScheduleRequest;
use App\Services\DutyManagementService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDutyController extends Controller
{
    protected $dutyManagementService;

    public function __construct(DutyManagementService $dutyManagementService)
    {
        $this->dutyManagementService = $dutyManagementService;
    }

    /**
     * 📋 Hiển thị giao diện chính: Danh sách chuyên khoa
     */
    public function index()
    {
        // Lấy tất cả chuyên khoa từ bác sĩ
        $specialties = Employee::where('is_doctor', true)
            ->whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization')
            ->sort()
            ->values();

        // Đếm số bác sĩ per chuyên khoa
        $specialtyStats = [];
        foreach ($specialties as $specialty) {
            $count = Employee::where('is_doctor', true)
                ->where('specialization', $specialty)
                ->count();
            $specialtyStats[$specialty] = $count;
        }

        return view('admin.duty.index', compact('specialties', 'specialtyStats'));
    }

    /**
     * 📅 Lấy bảng lịch tháng theo chuyên khoa (AJAX)
     */
    public function getCalendarBySpecialty()
    {
        $specialty = request('specialty');
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        if (!$specialty) {
            return response()->json(['error' => 'Missing specialty'], 400);
        }

        // Lấy bác sĩ trong chuyên khoa
        $doctors = Employee::where('is_doctor', true)
            ->where('specialization', $specialty)
            ->pluck('id')
            ->toArray();

        // Lấy lịch làm trong tháng
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $schedules = ScheduleRequest::where('assignment_type', 'work')
            ->whereIn('employee_id', $doctors)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['employee', 'shift'])
            ->get();

        // Nhóm theo ngày
        $calendarData = [];
        for ($d = $startDate->copy(); $d <= $endDate; $d->addDay()) {
            $dateStr = $d->format('Y-m-d');
            $daySchedules = $schedules->filter(fn($s) => $s->work_date->format('Y-m-d') == $dateStr);
            
            $calendarData[$dateStr] = [
                'date' => $dateStr,
                'day' => $d->format('d'),
                'dayOfWeek' => $d->format('l'),
                'hasSchedule' => $daySchedules->count() > 0,
                'scheduleCount' => $daySchedules->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'specialty' => $specialty,
            'month' => $month,
            'year' => $year,
            'calendar' => $calendarData,
            'monthName' => $startDate->format('m/Y'),
        ]);
    }

    /**
     * 👨‍⚕️ Lấy danh sách bác sĩ + lịch theo ngày (AJAX)
     */
    public function getDoctorsByDate()
    {
        $specialty = request('specialty');
        $date = request('date');

        if (!$specialty || !$date) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        // Bác sĩ trong chuyên khoa
        $doctors = Employee::where('is_doctor', true)
            ->where('specialization', $specialty)
            ->with('user')
            ->get();

        // Lịch làm của bác sĩ trong ngày
        $schedules = ScheduleRequest::where('assignment_type', 'work')
            ->whereIn('employee_id', $doctors->pluck('id'))
            ->whereDate('work_date', $date)
            ->with(['employee', 'shift'])
            ->get();

        $doctorData = [];
        foreach ($doctors as $doctor) {
            $daySchedules = $schedules->filter(fn($s) => $s->employee_id == $doctor->id);
            
            $doctorData[] = [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->user->name ?? $doctor->phone,
                'specialization' => $doctor->specialization,
                'schedules' => $daySchedules->map(fn($s) => [
                    'shift_name' => $s->shift->name ?? 'N/A',
                    'start_time' => ($s->start_hour ? str_pad($s->start_hour, 2, '0', STR_PAD_LEFT) : '00') . ':' . 
                                   str_pad($s->start_minute ?? 0, 2, '0', STR_PAD_LEFT),
                    'end_time' => ($s->end_hour ? str_pad($s->end_hour, 2, '0', STR_PAD_LEFT) : '00') . ':' . 
                                 str_pad($s->end_minute ?? 0, 2, '0', STR_PAD_LEFT),
                ])->toArray(),
            ];
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'dateFormatted' => Carbon::parse($date)->format('d/m/Y'),
            'doctors' => $doctorData,
        ]);
    }

    /**
     * 💾 Lưu giao ca trực
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'work_date' => 'required|date',
                'start_hour' => 'required|integer|between:0,23',
                'start_minute' => 'required|integer|between:0,59',
                'end_hour' => 'required|integer|between:0,23',
                'end_minute' => 'required|integer|between:0,59',
                'notes' => 'nullable|string|max:500',
            ]);

            // Kiểm tra bác sĩ
            $doctor = Employee::findOrFail($validated['employee_id']);
            if (!$doctor->is_doctor) {
                return back()->with('error', '❌ Không phải bác sĩ');
            }

            // Kiểm tra đã có ca trực trong ngày
            $existing = ShiftAssignment::where('employee_id', $doctor->id)
                ->whereDate('work_date', $validated['work_date'])
                ->where('assignment_type', 'duty')
                ->first();

            if ($existing) {
                return back()->with('error', '❌ Bác sĩ đã có ca trực trong ngày này');
            }

            // Tạo bản ghi
            ShiftAssignment::create([
                'employee_id' => $doctor->id,
                'work_date' => $validated['work_date'],
                'shift_id' => null,
                'start_hour' => $validated['start_hour'],
                'start_minute' => $validated['start_minute'],
                'end_hour' => $validated['end_hour'],
                'end_minute' => $validated['end_minute'],
                'assignment_type' => 'duty',
                'status' => 'approved',
                'notes' => $validated['notes'] ?? '',
                'assigned_by' => auth()->id(),
            ]);

            return back()->with('success', '✅ Giao ca trực thành công');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * ✏️ Cập nhật ca trực
     */
    public function update(ShiftAssignment $shiftAssignment, Request $request)
    {
        try {
            $validated = $request->validate([
                'start_hour' => 'required|integer|between:0,23',
                'start_minute' => 'required|integer|between:0,59',
                'end_hour' => 'required|integer|between:0,23',
                'end_minute' => 'required|integer|between:0,59',
                'notes' => 'nullable|string|max:500',
            ]);

            $shiftAssignment->update($validated);

            return back()->with('success', '✅ Cập nhật ca trực thành công');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * 🗑️ Xóa ca trực
     */
    public function destroy(ShiftAssignment $shiftAssignment)
    {
        try {
            $shiftAssignment->delete();
            return back()->with('success', '✅ Xóa ca trực thành công');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }
}