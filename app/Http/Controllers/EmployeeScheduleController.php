<?php

namespace App\Http\Controllers;

use App\Models\CustomShift;
use App\Models\Employee;
use App\Models\OffDay;
use App\Models\ScheduleRequest;
use App\Services\ScheduleRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    protected $scheduleRequestService;

    public function __construct(ScheduleRequestService $scheduleRequestService)
    {
        $this->scheduleRequestService = $scheduleRequestService;
    }

    /**
     * ✅ Lấy nhân viên hiện tại (tạo mới nếu chưa có)
     */
    private function getEmployee()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập');
        }

        // Kiểm tra employee tồn tại
        $employee = $user->employee;
        
        // Nếu chưa có employee record, tạo mới
        if (!$employee) {
            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        return $employee;
    }

    /**
     * ✅ GET /employees/schedule - Hiển thị trang chính với 2 tabs
     * Tab 1: Calendar để đăng ký ca
     * Tab 2: Xin ngày nghỉ
     */
    public function create()
    {
        try {
            $employee = $this->getEmployee();
            
            // Lấy tất cả ca làm việc áp dụng cho nhân viên
            $shifts = CustomShift::where('is_for_employee', 1)
                ->where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get();
            
            // Lấy data để hiển thị trên các tabs
            $pendingRequests = $this->scheduleRequestService->getPendingRequests($employee->id);
            $approvedSchedules = $this->scheduleRequestService->getApprovedRequests($employee->id);
            
            // ✅ FIX: Thêm pending off-days
            $pendingOffDays = OffDay::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();
            
            $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($employee->id);

            return view('employees.schedule.request-form', compact(
                'shifts',
                'pendingRequests',
                'approvedSchedules',
                'pendingOffDays',
                'approvedOffDays',
                'employee'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ GET /employees/schedule/official - Hiển thị lịch làm việc chính thức đã được duyệt
     * Hiển thị ca làm việc được phê duyệt + ngày nghỉ được phê duyệt
     */
    public function officialSchedule()
    {
        try {
            $employee = $this->getEmployee();
            
            // Lấy ca làm việc được duyệt, sắp xếp theo ngày
            $approvedSchedules = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->with('shift')
                ->orderBy('work_date', 'asc')
                ->get();
            
            // Lấy ngày nghỉ được duyệt, sắp xếp theo ngày
            $approvedOffDays = OffDay::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->orderBy('date', 'asc')
                ->get();

            return view('employees.schedule.official-schedule', compact(
                'approvedSchedules',
                'approvedOffDays',
                'employee'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ POST /employees/schedule - Nhân viên đăng ký ca làm việc
     * Form submit từ modal trong tab "Lịch đăng ký ca"
     * Cho phép đăng ký bất kỳ ngày nào (kể cả quá khứ)
     */
    public function store(Request $request)
    {
        try {
            $employee = $this->getEmployee();
            
            // Validate dữ liệu - cho phép đăng ký bất kỳ ngày nào (kể cả quá khứ)
            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            // Tạo schedule request qua service
            $this->scheduleRequestService->createScheduleRequest(
                $employee->id,
                $validated['work_date'],
                (int)$validated['shift_id'],
                $validated
            );

            // Redirect về trang chính với success message
            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Đã gửi đơn đăng ký ca làm việc!');
        } catch (\Exception $e) {
            // Redirect về trang chính với error message
            return redirect(route('employees.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ PUT /employees/schedule/{scheduleRequest} - Cập nhật đơn đăng ký ca
     * Nhân viên chỉ có thể cập nhật đơn của chính mình và chỉ khi đang pending
     * Có thể cập nhật cả những ngày đã qua
     */
    public function updateSchedule(Request $request, ScheduleRequest $scheduleRequest)
    {
        try {
            $employee = $this->getEmployee();
            
            // Validate dữ liệu - cho phép cập nhật bất kỳ ngày nào (kể cả quá khứ)
            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            // Cập nhật schedule request qua service
            $this->scheduleRequestService->updateScheduleRequest(
                $scheduleRequest->id,
                $employee->id,
                $validated['work_date'],
                (int)$validated['shift_id'],
                $validated
            );

            // Redirect về trang chính với success message
            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Đã cập nhật ca làm việc! Đơn này sẽ được duyệt lại.');
        } catch (\Exception $e) {
            // Redirect về trang chính với error message
            return redirect(route('employees.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /employees/schedule/{scheduleRequest} - Hủy đơn đăng ký ca
     * Nhân viên chỉ có thể hủy đơn của chính mình
     */
    public function cancel(ScheduleRequest $scheduleRequest)
    {
        try {
            $employee = $this->getEmployee();
            
            // Kiểm tra quyền - chỉ nhân viên của chính họ mới có thể hủy
            if ($scheduleRequest->employee_id !== $employee->id) {
                return back()->withErrors(['error' => '❌ Không có quyền hủy đơn này']);
            }

            // Hủy đơn qua service
            $this->scheduleRequestService->cancelScheduleRequest($scheduleRequest->id);

            return back()->with('success', '✅ Đã hủy đơn đăng ký');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ POST /employees/schedule/off-day - Nhân viên xin ngày nghỉ
     * Form submit từ tab "Xin ngày nghỉ"
     */
    public function requestOffDay(Request $request)
    {
        try {
            $employee = $this->getEmployee();

            // Validate dữ liệu
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|min:10|max:200',
            ]);

            // Tạo record OffDay cho mỗi ngày trong khoảng
            $startDate = strtotime($validated['start_date']);
            $endDate = strtotime($validated['end_date']);

            for ($date = $startDate; $date <= $endDate; $date += 86400) {
                $dayStr = date('Y-m-d', $date);
                
                OffDay::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $dayStr,
                    ],
                    [
                        'reason' => $validated['reason'] ?? '',
                        'status' => 'pending',
                    ]
                );
            }

            // Redirect về trang chính với success message
            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Đã gửi đơn xin nghỉ!');
        } catch (\Exception $e) {
            // Redirect về trang chính với error message
            return redirect(route('employees.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ PUT /employees/schedule/off-day/{offDay} - Cập nhật đơn xin nghỉ
     * Nhân viên chỉ có thể cập nhật đơn của chính mình và chỉ khi đang pending
     */
    public function updateOffDay(Request $request, OffDay $offDay)
    {
        try {
            $employee = $this->getEmployee();

            // Kiểm tra quyền - chỉ nhân viên của chính họ mới có thể cập nhật
            if ($offDay->employee_id !== $employee->id) {
                return back()->withErrors(['error' => '❌ Không có quyền cập nhật']);
            }

            // Kiểm tra trạng thái - chỉ cập nhật được đơn đang chờ duyệt
            if ($offDay->status !== 'pending') {
                return back()->withErrors(['error' => '❌ Chỉ có thể cập nhật đơn đang chờ duyệt']);
            }

            // Validate dữ liệu
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|min:10|max:200',
            ]);

            // Cập nhật đơn xin nghỉ
            $offDay->update([
                'date' => $validated['start_date'],
                'reason' => $validated['reason'],
            ]);

            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Cập nhật đơn xin nghỉ thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /employees/schedule/off-day/{offDay} - Hủy đơn xin nghỉ
     * Nhân viên chỉ có thể hủy đơn của chính mình và đơn chưa được duyệt
     */
    public function cancelOffDay(OffDay $offDay)
    {
        try {
            $employee = $this->getEmployee();

            // Kiểm tra quyền - chỉ nhân viên của chính họ mới có thể hủy
            if ($offDay->employee_id !== $employee->id) {
                return back()->withErrors(['error' => '❌ Không có quyền hủy']);
            }

            // Kiểm tra trạng thái - chỉ hủy được đơn đang chờ duyệt
            if ($offDay->status !== 'pending') {
                return back()->withErrors(['error' => '❌ Chỉ có thể hủy đơn đang chờ duyệt']);
            }

            // Xóa đơn xin nghỉ
            $offDay->delete();

            return back()->with('success', '✅ Đã hủy đơn xin nghỉ');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE /employees/schedule/off-day/{offDay} - Alias cho cancelOffDay
     */
    public function destroyOffDay(OffDay $offDay)
    {
        return $this->cancelOffDay($offDay);
    }

    /**
     * ✅ GET /employees/schedule/get-week-data - AJAX: Lấy dữ liệu tuần (shifts + schedules + counts)
     * Dùng cho frontend load week data via AJAX
     */
    public function getWeekData(Request $request)
    {
        try {
            $employee = $this->getEmployee();
            
            // ✅ FIX: Dùng MONDAY để tuần bắt đầu từ thứ 2
            $weekStart = $request->has('week_start') 
                ? Carbon::createFromFormat('Y-m-d', $request->week_start)->startOfWeek(Carbon::MONDAY)
                : now()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            // ✅ THÊM: Lấy tất cả shifts áp dụng cho nhân viên
            $shifts = CustomShift::where('is_for_employee', 1)
                ->where('is_active', 1)
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

            $pendingRequests = ScheduleRequest::where('employee_id', $employee->id)
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

            $approvedRequests = ScheduleRequest::where('employee_id', $employee->id)
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

            $pendingCount = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->count();

            $approvedCount = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->count();

            $approvedOffDaysCount = OffDay::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->count();

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
}