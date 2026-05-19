<?php

namespace App\Http\Controllers;

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
     * Kiểm tra employee tồn tại
     */
    private function getEmployee()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập');
        }

        $employee = $user->employee;
        if (!$employee) {
            abort(403, 'Bạn không phải nhân viên hoặc chưa được thiết lập.');
        }

        return $employee;
    }

    /**
     * Trang đăng ký ca làm việc & ngày nghỉ
     */

public function create()
{
    $employee = $this->getEmployee();
    $availableShifts = $this->scheduleRequestService->getAvailableShifts(false);
    $pendingRequests = $this->scheduleRequestService->getPendingRequests($employee->id);
    $approvedSchedules = $this->scheduleRequestService->getApprovedRequests($employee->id);
    $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($employee->id);

    return view('employees.schedule.request-form', compact('availableShifts', 'pendingRequests', 'approvedSchedules', 'approvedOffDays', 'employee'));
}

    /**
     * Đăng ký ca làm việc (POST)
     */
    public function store(Request $request)
    {
        try {
            $employee = $this->getEmployee();
            
            $validated = $request->validate([
                'work_date' => 'required|date|after_or_equal:today',
                'shift_id' => 'required|exists:shifts,id',
            ]);

            $this->scheduleRequestService->createScheduleRequest(
                $employee->id,
                $validated['work_date'],
                $validated['shift_id']
            );

            return back()->with('success', '✅ Đã gửi đơn đăng ký ca làm việc!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Hủy đơn đăng ký (DELETE)
     */
    public function cancel(ScheduleRequest $scheduleRequest)
    {
        try {
            $employee = $this->getEmployee();
            
            if ($scheduleRequest->employee_id != $employee->id) {
                return back()->with('error', '❌ Không có quyền hủy đơn này');
            }

            $this->scheduleRequestService->cancelScheduleRequest($scheduleRequest->id);

            return back()->with('success', '✅ Đã hủy đơn đăng ký');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Xem lịch đã duyệt
     */
    public function myApprovedSchedules()
    {
        $employee = $this->getEmployee();
        $approvedSchedules = $this->scheduleRequestService->getApprovedRequests($employee->id);

        return view('employee.schedule.my-approved-schedules', compact('approvedSchedules'));
    }

    /**
     * Đăng ký ngày nghỉ (POST)
     */
    public function requestOffDay(Request $request)
    {
        try {
            $employee = $this->getEmployee();

            $validated = $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:500',
            ]);

            for ($date = strtotime($validated['start_date']); $date <= strtotime($validated['end_date']); $date += 86400) {
                $dayStr = date('Y-m-d', $date);
                
                OffDay::firstOrCreate([
                    'employee_id' => $employee->id,
                    'off_date' => $dayStr,
                ], [
                    'reason' => $validated['reason'] ?? '',
                    'status' => 'pending',
                ]);
            }

            return back()->with('success', '✅ Đã gửi đơn xin nghỉ!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Xem danh sách ngày nghỉ
     */
    public function myOffDays()
    {
        $employee = $this->getEmployee();
        $offDays = $this->scheduleRequestService->getAllOffDays($employee->id);

        return view('employee.schedule.my-off-days', compact('offDays'));
    }

    /**
     * Hủy đơn xin nghỉ (DELETE)
     */
    public function cancelOffDay(OffDay $offDay)
    {
        try {
            $employee = $this->getEmployee();

            if ($offDay->employee_id != $employee->id) {
                return back()->with('error', '❌ Không có quyền hủy');
            }

            if ($offDay->status != 'pending') {
                return back()->with('error', '❌ Chỉ có thể hủy đơn đang chờ duyệt');
            }

            $offDay->delete();

            return back()->with('success', '✅ Đã hủy đơn xin nghỉ');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }
}