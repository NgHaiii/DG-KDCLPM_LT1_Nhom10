<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\ScheduleRequestService;
use Illuminate\Http\Request;

class ScheduleRequestController extends Controller
{
    private ScheduleRequestService $service;

    public function __construct(ScheduleRequestService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    /**
     * ✅ Lấy Employee của user hiện tại
     */
    private function getCurrentEmployee()
    {
        $user = auth()->user();
        $employee = Employee::where('linkedUser', $user->id)->first();

        if (!$employee) {
            abort(403, 'Không tìm thấy thông tin nhân viên');
        }

        return $employee;
    }

    /**
     * ✅ Hiển thị form đăng ký lịch
     */
    public function create()
    {
        $employee = $this->getCurrentEmployee();

        return view('schedule.request-form', [
            'employee' => $employee,
            'shifts' => $this->service->getAvailableShifts($employee),
            'pendingRequests' => $this->service->getPendingRequests($employee),
            'approvedRequests' => $this->service->getApprovedRequests($employee),
            'rejectedRequests' => $this->service->getRejectedRequests($employee),
            'approvedOffDays' => $this->service->getApprovedOffDays($employee),
            'isDoctor' => $employee->is_doctor,
        ]);
    }

    /**
     * ✅ Lưu đơn đăng ký ca làm việc
     */
    public function store(Request $request)
    {
        $employee = $this->getCurrentEmployee();

        $validated = $request->validate([
            'work_date' => 'required|date|after_or_equal:today',
            'shift_id' => 'required|exists:shifts,id',
            'duty_type' => 'nullable|in:shift,duty',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $scheduleRequest = $this->service->createScheduleRequest($employee, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Đơn đăng ký lịch đã được gửi thành công',
                'request' => $scheduleRequest->load('shift')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * ✅ Hủy đơn đăng ký
     */
    public function cancel($id)
    {
        $employee = $this->getCurrentEmployee();

        try {
            $this->service->cancelScheduleRequest($employee, $id);

            return response()->json([
                'success' => true,
                'message' => 'Đơn đã bị hủy'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * ✅ Đăng ký ngày nghỉ
     */
    public function requestOffDay(Request $request)
    {
        $employee = $this->getCurrentEmployee();

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:300',
        ]);

        try {
            $offDay = $this->service->requestOffDay($employee, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Đơn xin nghỉ đã được gửi',
                'offDay' => $offDay
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * ✅ Xem danh sách ngày nghỉ của chính mình
     */
    public function myOffDays()
    {
        $employee = $this->getCurrentEmployee();

        return view('schedule.my-off-days', [
            'employee' => $employee,
            'offDays' => $this->service->getAllOffDays($employee),
        ]);
    }

    /**
     * ✅ Hủy đơn xin nghỉ
     */
    public function cancelOffDay($id)
    {
        $employee = $this->getCurrentEmployee();

        try {
            $this->service->cancelOffDay($employee, $id);

            return response()->json([
                'success' => true,
                'message' => 'Đơn xin nghỉ đã bị hủy'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * ✅ Xem lịch đã được duyệt của chính mình
     */
    public function myApprovedSchedules()
    {
        $employee = $this->getCurrentEmployee();

        return view('schedule.my-approved-schedules', [
            'employee' => $employee,
            'approvedSchedules' => $this->service->getMyApprovedSchedules($employee),
        ]);
    }
}