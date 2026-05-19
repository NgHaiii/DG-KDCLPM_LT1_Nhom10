<?php

namespace App\Services;

use App\Models\ScheduleRequest;
use App\Models\OffDay;
use App\Models\Shift;
use App\Models\Employee;

class ScheduleRequestService
{
    /**
     * ✅ Lấy danh sách ca làm việc có sẵn
     * @param bool $isDoctor - true: bác sĩ (có Tối), false: nhân viên (Sáng/Chiều)
     */
    public function getAvailableShifts($isDoctor = false)
    {
        $shifts = Shift::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        // Nhân viên không được đăng ký ca Tối
        if (!$isDoctor) {
            $shifts = $shifts->where('name', '!=', 'Tối');
        }

        return $shifts;
    }

    /**
     * ✅ Lấy danh sách đơn đăng ký chờ duyệt của nhân viên
     */
    public function getPendingRequests($empId)
    {
        return ScheduleRequest::where('employee_id', $empId)
            ->where('status', 'pending')
            ->with('shift')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * ✅ Tạo đơn đăng ký ca làm việc
     */
    public function createScheduleRequest($empId, $workDate, $shiftId)
    {
        // Kiểm tra: có đã xin nghỉ không?
        if ($this->hasApprovedOffDay($empId, $workDate)) {
            throw new \Exception('Bạn đã xin nghỉ vào ngày này. Không thể đăng ký ca làm việc.');
        }

        // Kiểm tra: đã có đơn pending không?
        if ($this->hasDuplicateRequest($empId, $workDate, $shiftId)) {
            throw new \Exception('Bạn đã đăng ký ca này rồi, vui lòng đợi duyệt.');
        }

        // Kiểm tra: ca này có hợp lệ không?
        if (!$this->isValidShiftForEmployee($empId, $shiftId)) {
            throw new \Exception('Ca làm việc không phù hợp với vị trí của bạn.');
        }

        return ScheduleRequest::create([
            'employee_id' => $empId,
            'work_date' => $workDate,
            'shift_id' => $shiftId,
            'status' => 'pending',
        ]);
    }

    /**
     * ✅ Hủy đơn đăng ký (chỉ nếu chưa duyệt)
     */
    public function cancelScheduleRequest($requestId)
    {
        $request = ScheduleRequest::find($requestId);

        if (!$request) {
            throw new \Exception('Không tìm thấy đơn đăng ký');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('Chỉ có thể hủy các đơn đang chờ duyệt');
        }

        $request->delete();
        return true;
    }

    /**
     * ✅ Kiểm tra: nhân viên đã xin nghỉ vào ngày này không?
     */
    public function hasApprovedOffDay($empId, $workDate)
    {
        return OffDay::where('employee_id', $empId)
            ->where('status', 'approved')
            ->where('date', $workDate)
            ->exists();
    }

    /**
     * ✅ Kiểm tra: đã có đơn đăng ký ca này chưa?
     */
    public function hasDuplicateRequest($empId, $workDate, $shiftId)
    {
        return ScheduleRequest::where('employee_id', $empId)
            ->where('work_date', $workDate)
            ->where('shift_id', $shiftId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * ✅ Kiểm tra: ca này có phù hợp với vị trí không?
     * Nhân viên không được đăng ký ca Tối
     */
    public function isValidShiftForEmployee($empId, $shiftId)
    {
        $employee = Employee::find($empId);
        $shift = Shift::find($shiftId);

        if (!$employee || !$shift) {
            return false;
        }

        // Nếu là nhân viên (không phải bác sĩ), không được chọn ca Tối
        if (!$employee->is_doctor && $shift->name === 'Tối') {
            return false;
        }

        return true;
    }

    /**
     * ✅ Lấy các ca được duyệt của nhân viên
     */
    public function getApprovedRequests($empId)
    {
        return ScheduleRequest::where('employee_id', $empId)
            ->where('status', 'approved')
            ->with('shift')
            ->orderBy('work_date', 'desc')
            ->get();
    }

    /**
     * ✅ Lấy các ngày nghỉ được duyệt của nhân viên
     */
    public function getApprovedOffDays($empId)
    {
        return OffDay::where('employee_id', $empId)
            ->where('status', 'approved')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * ✅ Lấy tất cả ngày nghỉ (tất cả trạng thái)
     */
    public function getAllOffDays($empId)
    {
        return OffDay::where('employee_id', $empId)
            ->orderBy('date', 'desc')
            ->get();
    }
}