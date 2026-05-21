<?php

namespace App\Http\Controllers;

use App\Models\CustomShift;
use App\Models\Employee;
use App\Models\OffDay;
use App\Models\ScheduleRequest;
use App\Services\ScheduleRequestService;
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
     * ✅ GET /employees/schedule - Hiển thị trang chính với 3 tabs
     * Tab 1: Calendar để đăng ký ca
     * Tab 2: Xin ngày nghỉ
     * Tab 3: Xem ca & ngày nghỉ đã duyệt
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
            $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($employee->id);

            return view('employees.schedule.request-form', compact(
                'shifts',
                'pendingRequests',
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
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:500',
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
}