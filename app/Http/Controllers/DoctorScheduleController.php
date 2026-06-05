<?php

namespace App\Http\Controllers;

use App\Models\CustomShift;
use App\Models\Employee;
use App\Models\OffDay;
use App\Models\ScheduleRequest;
use App\Models\ShiftAssignment;
use App\Services\ScheduleRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    protected $scheduleRequestService;

    public function __construct(ScheduleRequestService $scheduleRequestService)
    {
        $this->scheduleRequestService = $scheduleRequestService;
    }

    /**
     * ✅ Lấy bác sĩ hiện tại (tạo mới nếu chưa có)
     * 🔒 BẢO MẬT: Kiểm tra user_id khớp với employee
     */
    private function getDoctor()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập');
        }

        $doctor = $user->employee;
        
        if (!$doctor) {
            $doctor = Employee::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        if ($doctor->user_id !== $user->id) {
            abort(403, '❌ Bạn không có quyền truy cập dữ liệu này');
        }

        return $doctor;
    }

    /**
     * ✅ GET /doctor/schedule - Hiển thị trang chính
     * 🔒 BẢO MẬT: Chỉ hiển thị dữ liệu của bác sĩ hiện tại
     * 🔧 FIX: Filter dữ liệu theo tuần hiện tại
     */
    public function create()
    {
        try {
            $doctor = $this->getDoctor();
            
            $shifts = CustomShift::where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get();
            
            // ✅ FIX: Dùng MONDAY để tuần bắt đầu từ thứ 2
            if (request('week_start')) {
                try {
                    $weekStart = Carbon::createFromFormat('Y-m-d', request('week_start'))->startOfWeek(Carbon::MONDAY);
                } catch (\Exception $e) {
                    $weekStart = now()->startOfWeek(Carbon::MONDAY);
                }
            } else {
                $weekStart = now()->startOfWeek(Carbon::MONDAY);
            }
            
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            
            // 🔧 FIX: Pass tuần vào service để filter dữ liệu CHÍNH XÁC
            $pendingRequests = $this->scheduleRequestService->getPendingRequests(
                $doctor->id, 
                $weekStart, 
                $weekEnd
            );
            
            $approvedRequests = $this->scheduleRequestService->getApprovedRequests(
                $doctor->id,
                $weekStart,
                $weekEnd
            );
            
            $pendingOffDays = OffDay::where('employee_id', $doctor->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();
            
            $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($doctor->id);

            return view('doctor.schedule.request-form', compact(
                'shifts',
                'pendingRequests',
                'approvedRequests',
                'pendingOffDays',
                'approvedOffDays',
                'doctor',
                'weekStart'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ GET /doctor/schedule/get-week-data - Lấy dữ liệu tuần (AJAX)
     * 🔒 BẢO MẬT: Chỉ trả về dữ liệu của bác sĩ hiện tại
     * ✅ FIX: Bây giờ bao gồm shift data cho TẤT CẢ 7 ngày trong tuần
     */
    public function getWeekData(Request $request)
    {
        try {
            $doctor = $this->getDoctor();
            
            // ✅ FIX: Dùng MONDAY để tuần bắt đầu từ thứ 2
            $weekStart = $request->has('week_start') 
                ? Carbon::createFromFormat('Y-m-d', $request->week_start)->startOfWeek(Carbon::MONDAY)
                : now()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            // ✅ THÊM: Lấy tất cả shifts (để cập nhật shiftsData trên frontend)
            $shifts = CustomShift::where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get()
                ->map(function($s) {
                    return [
                        'id' => $s->id,
                        'name' => strtolower($s->name),
                        'start_hour' => $s->start_hour,
                        'end_hour' => $s->end_hour
                    ];
                });

            $pendingRequests = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'pending')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->get()
                ->map(function($r) {
                    return [
                        'id' => $r->id,
                        'date' => $r->work_date->format('Y-m-d'),
                        'name' => optional($r->shift)->name ?? 'Ca làm việc',
                        'time' => sprintf('%02d:%02d', $r->start_hour ?? 0, $r->start_minute ?? 0) . ' - ' . sprintf('%02d:%02d', $r->end_hour ?? 0, $r->end_minute ?? 0),
                        'shift_id' => $r->shift_id,
                        'status' => 'pending',
                        'start_hour' => $r->start_hour,
                        'start_minute' => $r->start_minute,
                        'end_hour' => $r->end_hour,
                        'end_minute' => $r->end_minute
                    ];
                });

            $approvedRequests = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->get()
                ->map(function($s) {
                    return [
                        'id' => $s->id,
                        'date' => $s->work_date->format('Y-m-d'),
                        'name' => optional($s->shift)->name ?? 'Ca làm việc',
                        'time' => sprintf('%02d:%02d', $s->start_hour ?? 0, $s->start_minute ?? 0) . ' - ' . sprintf('%02d:%02d', $s->end_hour ?? 0, $s->end_minute ?? 0),
                        'shift_id' => $s->shift_id,
                        'status' => 'approved',
                        'start_hour' => $s->start_hour,
                        'start_minute' => $s->start_minute,
                        'end_hour' => $s->end_hour,
                        'end_minute' => $s->end_minute
                    ];
                });

            $pendingCount = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'pending')
                ->count();

            $approvedCount = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->count();

            $approvedOffDaysCount = OffDay::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->count();

            // ✅ THÊM 'shifts' vào response để cập nhật shiftsData trên frontend
            return response()->json([
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'shifts' => $shifts,
                'pending_requests' => $pendingRequests,
                'approved_requests' => $approvedRequests,
                'pending_count' => $pendingCount,
                'approved_count' => $approvedCount,
                'approved_off_days' => $approvedOffDaysCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => '❌ ' . $e->getMessage()], 500);
        }
    }

    /**
     * ✅ POST /doctor/schedule - Đăng ký ca làm việc (AJAX)
     * 🔒 BẢO MẬT: Chỉ tạo cho bác sĩ hiện tại
     */
    public function store(Request $request)
    {
        try {
            $doctor = $this->getDoctor();
            
            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            $this->scheduleRequestService->createScheduleRequest(
                $doctor->id,
                $validated['work_date'],
                (int)$validated['shift_id'],
                $validated
            );

            // ✅ AJAX response
            if ($request->expectsJson()) {
                return response()->json(['message' => '✅ Đã gửi đơn đăng ký ca làm việc!'], 201);
            }

            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Đã gửi đơn đăng ký ca làm việc!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '❌ ' . $e->getMessage()], 422);
            }
            return redirect(route('doctor.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ PUT /doctor/schedule/{scheduleRequest} - Cập nhật ca (AJAX)
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi cập nhật
     */
    public function updateSchedule(Request $request, ScheduleRequest $scheduleRequest)
    {
        try {
            $doctor = $this->getDoctor();
            
            if ($scheduleRequest->employee_id !== $doctor->id) {
                abort(403, '❌ Bạn không có quyền cập nhật đơn này');
            }

            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            $this->scheduleRequestService->updateScheduleRequest(
                $scheduleRequest->id,
                $doctor->id,
                $validated['work_date'],
                (int)$validated['shift_id'],
                $validated
            );

            // ✅ AJAX response
            if ($request->expectsJson()) {
                return response()->json(['message' => '✅ Cập nhật ca thành công!'], 200);
            }

            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Đã cập nhật ca làm việc!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '❌ ' . $e->getMessage()], 422);
            }
            return redirect(route('doctor.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /doctor/schedule/{scheduleRequest} - Hủy ca
     */
    public function cancel(Request $request, ScheduleRequest $scheduleRequest)
    {
        try {
            $doctor = $this->getDoctor();
            
            if ($scheduleRequest->employee_id !== $doctor->id) {
                abort(403, '❌ Không có quyền hủy đơn này');
            }

            $this->scheduleRequestService->cancelScheduleRequest($scheduleRequest->id);

            if ($request->expectsJson()) {
                return response()->json(['message' => '✅ Đã hủy đơn đăng ký'], 200);
            }

            return back()->with('success', '✅ Đã hủy đơn đăng ký');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '❌ ' . $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ POST /doctor/schedule/request-off-day - Xin ngày nghỉ (AJAX)
     * 🔒 BẢO MẬT: Chỉ tạo cho bác sĩ hiện tại
     */
    public function requestOffDay(Request $request)
    {
        try {
            $doctor = $this->getDoctor();

            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|min:5|max:200',
            ]);

            $startDate = strtotime($validated['start_date']);
            $endDate = strtotime($validated['end_date']);

            for ($date = $startDate; $date <= $endDate; $date += 86400) {
                $dayStr = date('Y-m-d', $date);
                
                OffDay::firstOrCreate(
                    [
                        'employee_id' => $doctor->id,
                        'date' => $dayStr,
                    ],
                    [
                        'reason' => $validated['reason'] ?? '',
                        'status' => 'pending',
                    ]
                );
            }

            // ✅ AJAX response
            if ($request->expectsJson()) {
                return response()->json(['message' => '✅ Gửi đơn xin nghỉ thành công!'], 201);
            }

            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Đã gửi đơn xin nghỉ!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '❌ ' . $e->getMessage()], 422);
            }
            return redirect(route('doctor.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ PUT /doctor/schedule/off-day/{offDay} - Cập nhật xin nghỉ (AJAX)
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi cập nhật
     */
    public function updateOffDay(Request $request, OffDay $offDay)
    {
        try {
            $doctor = $this->getDoctor();

            if ($offDay->employee_id !== $doctor->id) {
                abort(403, '❌ Không có quyền cập nhật');
            }

            if ($offDay->status !== 'pending') {
                $msg = 'Chỉ có thể cập nhật đơn đang chờ duyệt';
                if ($request->expectsJson()) {
                    return response()->json(['message' => '❌ ' . $msg], 422);
                }
                return back()->withErrors(['error' => '❌ ' . $msg]);
            }

            $validated = $request->validate([
                'start_date' => 'required|date',
                'reason' => 'nullable|string|min:5|max:200',
            ]);

            $offDay->update([
                'date' => $validated['start_date'],
                'reason' => $validated['reason'] ?? '',
            ]);

            // ✅ AJAX response
            if ($request->expectsJson()) {
                return response()->json(['message' => '✅ Cập nhật thành công!'], 200);
            }

            return redirect(route('doctor.schedule.create'))
                ->with('success', '✅ Cập nhật đơn xin nghỉ thành công!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '❌ ' . $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /doctor/schedule/off-day/{offDay} - Hủy xin nghỉ
     * 🔒 BẢO MẬT: Kiểm tra ownership trước khi hủy
     */
    public function destroyOffDay(Request $request, OffDay $offDay)
    {
        try {
            $doctor = $this->getDoctor();

            if ($offDay->employee_id !== $doctor->id) {
                abort(403, '❌ Không có quyền hủy');
            }

            if ($offDay->status !== 'pending') {
                $msg = 'Chỉ có thể hủy đơn đang chờ duyệt';
                if ($request->expectsJson()) {
                    return response()->json(['message' => '❌ ' . $msg], 422);
                }
                return back()->withErrors(['error' => '❌ ' . $msg]);
            }

            $offDay->delete();

            if ($request->expectsJson()) {
                return response()->json(['message' => '✅ Hủy thành công!'], 200);
            }

            return back()->with('success', '✅ Đã hủy đơn xin nghỉ');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '❌ ' . $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ GET /doctor/schedule/official - Hiển thị lịch chính thức
     * 🔒 BẢO MẬT: Chỉ hiển thị dữ liệu của bác sĩ hiện tại
     * ✅ UPDATED: Pass shifts data và tuần hiện tại
     */
    public function officialSchedule()
    {
        try {
            $doctor = $this->getDoctor();
            
            // ✅ Lấy shifts để hiển thị calendar
            $shifts = CustomShift::where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get();
            
            // ✅ Xác định tuần hiện tại
            if (request('week_start')) {
                try {
                    $weekStart = Carbon::createFromFormat('Y-m-d', request('week_start'))->startOfWeek(Carbon::MONDAY);
                } catch (\Exception $e) {
                    $weekStart = now()->startOfWeek(Carbon::MONDAY);
                }
            } else {
                $weekStart = now()->startOfWeek(Carbon::MONDAY);
            }
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            
            // ✅ Lấy lịch được duyệt cho tuần hiện tại
            $approvedSchedules = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->orderBy('work_date', 'asc')
                ->get();
            
            $approvedDuties = ShiftAssignment::where('employee_id', $doctor->id)
                ->where('assignment_type', 'duty')
                ->where('status', 'approved')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->orderBy('work_date', 'asc')
                ->get();
            
            $approvedOffDays = OffDay::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->orderBy('date', 'asc')
                ->get();

            return view('doctor.schedule.official-schedule', compact(
                'approvedSchedules',
                'approvedDuties',
                'approvedOffDays',
                'shifts',
                'doctor',
                'weekStart'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ GET /doctor/official-schedule/get-week-data - Lấy dữ liệu tuần lịch chính thức (AJAX)
     * 🔒 BẢO MẬT: Chỉ trả về dữ liệu của bác sĩ hiện tại
     * ✅ MỚI: Dành cho lịch chính thức
     * ✅ UPDATED: Thêm shifts vào response
     */
    public function getOfficialWeekData(Request $request)
    {
        try {
            $doctor = $this->getDoctor();
            
            // ✅ Xác định tuần
            $weekStart = $request->has('week_start') 
                ? Carbon::createFromFormat('Y-m-d', $request->week_start)->startOfWeek(Carbon::MONDAY)
                : now()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            // ✅ Lấy tất cả shifts
            $shifts = CustomShift::where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get();

            // ✅ Lấy lịch được duyệt
            $approvedSchedules = ScheduleRequest::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->get()
                ->map(function($s) {
                    return [
                        'id' => $s->id,
                        'date' => $s->work_date->format('Y-m-d'),
                        'name' => optional($s->shift)->name ?? 'Ca làm việc',
                        'time' => sprintf('%02d:%02d', $s->start_hour ?? 0, $s->start_minute ?? 0) . ' - ' . sprintf('%02d:%02d', $s->end_hour ?? 0, $s->end_minute ?? 0),
                        'shift_id' => $s->shift_id,
                        'start_hour' => $s->start_hour,
                        'start_minute' => $s->start_minute,
                        'end_hour' => $s->end_hour,
                        'end_minute' => $s->end_minute,
                    ];
                });

            // ✅ Lấy ngày nghỉ được duyệt
            $approvedOffDays = OffDay::where('employee_id', $doctor->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->get()
                ->map(function($o) {
                    return [
                        'id' => $o->id,
                        'date' => $o->date->format('Y-m-d'),
                        'reason' => $o->reason ?? 'Ngày nghỉ',
                    ];
                });

            // ✅ THÊM 'shifts' vào response
            return response()->json([
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'shifts' => $shifts->map(function($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'start_hour' => $s->start_hour,
                        'end_hour' => $s->end_hour
                    ];
                }),
                'schedules' => $approvedSchedules,
                'off_days' => $approvedOffDays,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => '❌ ' . $e->getMessage()], 500);
        }
    }

    /**
     * ✅ GET /doctor/duty/get-duties - Lấy ca trực (AJAX)
     * 🔒 BẢO MẬT: Chỉ trả về dữ liệu của bác sĩ hiện tại
     */
    public function getDoctorDuties()
    {
        try {
            $doctor = $this->getDoctor();

            $duties = ShiftAssignment::where('employee_id', $doctor->id)
                ->where('assignment_type', 'duty')
                ->with(['shift', 'assignedBy'])
                ->orderBy('work_date', 'desc')
                ->get()
                ->map(function ($duty) {
                    return [
                        'id' => $duty->id,
                        'work_date' => $duty->work_date->format('Y-m-d'),
                        'formatted_date' => $duty->formatted_date,
                        'shift_name' => $duty->shift_name,
                        'time_range' => $duty->time_range,
                        'status' => $duty->status,
                        'status_label' => $duty->status_label,
                        'notes' => $duty->notes,
                        'assigned_by' => $duty->assignedBy?->name,
                    ];
                });

            return response()->json($duties);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ GET /doctor/schedule/work-schedules - Lấy lịch làm việc (AJAX)
     * 🔒 BẢO MẬT: Chỉ trả về dữ liệu của bác sĩ hiện tại
     */
    public function getDoctorWorkSchedules()
    {
        try {
            $doctor = $this->getDoctor();

            $workSchedules = ShiftAssignment::where('employee_id', $doctor->id)
                ->where('assignment_type', 'work')
                ->with(['shift', 'assignedBy'])
                ->orderBy('work_date', 'desc')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'work_date' => $schedule->work_date->format('Y-m-d'),
                        'formatted_date' => $schedule->formatted_date,
                        'shift_name' => $schedule->shift_name,
                        'time_range' => $schedule->time_range,
                        'status' => $schedule->status,
                        'status_label' => $schedule->status_label,
                        'notes' => $schedule->notes,
                    ];
                });

            return response()->json($workSchedules);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}