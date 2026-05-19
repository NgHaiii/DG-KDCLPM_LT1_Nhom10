<?php

namespace App\Http\Controllers;

use App\Models\OffDay;
use App\Models\ScheduleRequest;
use App\Services\ScheduleRequestService;
use App\Services\DutyManagementService;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    protected $scheduleRequestService;
    protected $dutyManagementService;

    public function __construct(
        ScheduleRequestService $scheduleRequestService,
        DutyManagementService $dutyManagementService
    ) {
        $this->scheduleRequestService = $scheduleRequestService;
        $this->dutyManagementService = $dutyManagementService;
    }

    /**
     * Trang đăng ký ca làm việc & ngày nghỉ
     */
    public function create()
    {
        $employee = auth()->user()->employee;
        $shifts = $this->scheduleRequestService->getAvailableShifts(true); // true = doctor, có Sáng/Chiều/Tối
        $pendingRequests = $this->scheduleRequestService->getPendingRequests($employee->id);
        $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($employee->id);

        return view('doctor.schedule.request-form', compact('shifts', 'pendingRequests', 'approvedOffDays', 'employee'));
    }

    /**
     * Đăng ký ca làm việc (POST)
     */
    public function store(Request $request)
    {
        try {
            $employee = auth()->user()->employee;
            
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
            $employee = auth()->user()->employee;
            
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
        $employee = auth()->user()->employee;
        $approvedSchedules = $this->scheduleRequestService->getApprovedRequests($employee->id);

        return view('doctor.schedule.my-approved-schedules', compact('approvedSchedules'));
    }

    /**
     * Đăng ký ngày nghỉ (POST)
     */
    public function requestOffDay(Request $request)
    {
        try {
            $employee = auth()->user()->employee;

            $validated = $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:500',
            ]);

            // Tạo từng off day cho từng ngày
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
        $employee = auth()->user()->employee;
        $offDays = $this->scheduleRequestService->getAllOffDays($employee->id);

        return view('doctor.schedule.my-off-days', compact('offDays'));
    }

    /**
     * Hủy đơn xin nghỉ (DELETE)
     */
    public function cancelOffDay(OffDay $offDay)
    {
        try {
            $employee = auth()->user()->employee;

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

    /**
     * Xem ca trực được gán (readonly)
     */
    public function myDuties()
    {
        $employee = auth()->user()->employee;
        $duties = $this->dutyManagementService->getDoctorDuties($employee->id);

        return view('doctor.schedule.my-duties', compact('duties'));
    }
}