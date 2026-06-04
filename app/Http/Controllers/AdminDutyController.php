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
     * 📋 Hiển thị giao diện chính: Danh mục chuyên khoa + Bảng lịch
     */
    public function index()
    {
        // Lấy tất cả chuyên khoa từ bác sĩ
        $specialties = Employee::where('is_doctor', true)
            ->distinct('specialization')
            ->pluck('specialization')
            ->filter()
            ->sort()
            ->values();

        // Lấy specialty đầu tiên làm mặc định (hoặc từ query)
        $selectedSpecialty = request('specialty') ?? $specialties->first();
        
        $startDate = now()->startOfWeek(1); // Thứ 2
        $endDate = now()->endOfWeek(1);     // Chủ nhật

        // Lấy dữ liệu lịch làm theo chuyên khoa & tuần
        $scheduleData = [];
        if ($selectedSpecialty) {
            // Bác sĩ trong chuyên khoa
            $doctors = Employee::where('is_doctor', true)
                ->where('specialization', $selectedSpecialty)
                ->with(['user'])
                ->get();

            // Lấy lịch làm (assignment_type = 'work')
            $schedules = ScheduleRequest::where('assignment_type', 'work')
                ->whereIn('employee_id', $doctors->pluck('id'))
                ->whereBetween('work_date', [$startDate, $endDate])
                ->with(['employee', 'shift'])
                ->get();

            // Nhóm lịch theo bác sĩ & ngày
            foreach ($doctors as $doctor) {
                $scheduleData[$doctor->id] = [
                    'doctor' => $doctor,
                    'schedules' => $schedules->filter(fn($s) => $s->employee_id == $doctor->id)
                        ->groupBy(fn($s) => $s->work_date->format('Y-m-d'))
                ];
            }
        }

        // Danh sách ca làm việc
        $shifts = CustomShift::where('is_active', true)
            ->where('is_for_doctor', true)
            ->get();

        return view('admin.duty.index', compact(
            'specialties',
            'selectedSpecialty',
            'scheduleData',
            'shifts',
            'startDate',
            'endDate'
        ));
    }

    /**
     * 🎯 API: Lấy bảng lịch theo chuyên khoa + tuần (AJAX)
     */
    public function getScheduleGrid()
    {
        $specialty = request('specialty');
        $weekStart = request('week_start'); // Format: YYYY-MM-DD

        if (!$specialty || !$weekStart) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $startDate = Carbon::parse($weekStart)->startOfWeek(1);
        $endDate = $startDate->copy()->endOfWeek(1);

        // Bác sĩ trong chuyên khoa
        $doctors = Employee::where('is_doctor', true)
            ->where('specialization', $specialty)
            ->with(['user'])
            ->get();

        // Lịch làm
        $schedules = ScheduleRequest::where('assignment_type', 'work')
            ->whereIn('employee_id', $doctors->pluck('id'))
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['employee', 'shift'])
            ->get();

        // Dữ liệu grid
        $grid = [];
        foreach ($doctors as $doctor) {
            $doctorRow = [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->user->name ?? $doctor->phone,
                'days' => []
            ];

            // Mỗi ngày trong tuần
            for ($d = $startDate->copy(); $d <= $endDate; $d->addDay()) {
                $dateStr = $d->format('Y-m-d');
                $daySchedules = $schedules->filter(function($s) use ($doctor, $dateStr) {
                    return $s->employee_id == $doctor->id && $s->work_date->format('Y-m-d') == $dateStr;
                })->values();

                $doctorRow['days'][] = [
                    'date' => $dateStr,
                    'day_name' => $d->format('l'), // Monday, Tuesday, ...
                    'day_num' => $d->format('d/m'),
                    'schedules' => $daySchedules->map(fn($s) => [
                        'id' => $s->id,
                        'shift_name' => $s->shift->name ?? 'N/A',
                        'time' => ($s->start_hour ? str_pad($s->start_hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s->start_minute, 2, '0', STR_PAD_LEFT) : '') . ' - ' . 
                                  ($s->end_hour ? str_pad($s->end_hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s->end_minute, 2, '0', STR_PAD_LEFT) : ''),
                    ])
                ];
            }

            $grid[] = $doctorRow;
        }

        return response()->json([
            'success' => true,
            'data' => $grid,
            'week_start' => $startDate->format('d/m/Y'),
            'week_end' => $endDate->format('d/m/Y'),
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
                'shift_id' => null, // Không cần shift nếu tuỳ chỉnh giờ
                'start_hour' => $validated['start_hour'],
                'start_minute' => $validated['start_minute'],
                'end_hour' => $validated['end_hour'],
                'end_minute' => $validated['end_minute'],
                'assignment_type' => 'duty',
                'status' => 'approved', // Tự động approved
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