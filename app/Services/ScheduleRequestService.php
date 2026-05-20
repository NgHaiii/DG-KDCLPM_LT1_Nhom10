<?php

namespace App\Services;

use App\Models\ScheduleRequest;
use App\Models\CustomShift;
use App\Models\OffDay;
use App\Models\Employee;

class ScheduleRequestService
{
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
     * ✅ Lấy các ca được duyệt của nhân viên
     */
    public function getApprovedRequests($empId)
    {
        return ScheduleRequest::where('employee_id', $empId)
            ->where('status', 'approved')
            ->with('shift')
            ->orderBy('work_date', 'asc')
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

    /**
     * ✅ Tạo đơn đăng ký ca làm việc (HỆ THỐNG CUSTOM SHIFT)
     * @param int $empId - ID nhân viên
     * @param string $workDate - Ngày làm (YYYY-MM-DD)
     * @param int $shiftId - ID ca làm việc từ custom_shifts table
     * @param array $data - Dữ liệu từ form (custom hours nếu có)
     */
    public function createScheduleRequest($empId, $workDate, $shiftId, $data = [])
    {
        // 1️⃣ Kiểm tra: nhân viên có đã xin nghỉ vào ngày này không?
        if ($this->hasApprovedOffDay($empId, $workDate)) {
            throw new \Exception('❌ Bạn đã xin nghỉ vào ngày này. Không thể đăng ký ca làm việc.');
        }

        // 2️⃣ Kiểm tra: shift_id có tồn tại không?
        $shift = CustomShift::find($shiftId);
        
        if (!$shift) {
            throw new \Exception('❌ Ca làm việc không tồn tại. Vui lòng chọn ca khác.');
        }

        // 3️⃣ Kiểm tra: ca có còn hoạt động không?
        if (!$shift->is_active) {
            throw new \Exception('❌ Ca làm việc này không còn hoạt động.');
        }

        // 4️⃣ Kiểm tra: ca có áp dụng cho nhân viên không?
        if (!$shift->is_for_employee) {
            throw new \Exception('❌ Ca này không áp dụng cho nhân viên.');
        }

        // 5️⃣ Kiểm tra: đã có đơn pending cùng ngày cùng ca không?
        if ($this->hasDuplicateRequest($empId, $workDate, $shiftId)) {
            throw new \Exception('❌ Bạn đã đăng ký ca này rồi, vui lòng đợi duyệt.');
        }

        // 6️⃣ Chuẩn bị dữ liệu để lưu vào database
        $scheduleData = [
            'employee_id' => $empId,
            'work_date' => $workDate,
            'shift_id' => $shiftId,
            'status' => 'pending',
        ];

        // 7️⃣ Nếu nhân viên tùy chỉnh giờ, lưu vào notes dưới dạng JSON
        $hasCustomHours = !empty($data['start_hour']) || !empty($data['start_minute']) || 
                         !empty($data['end_hour']) || !empty($data['end_minute']);
        
        if ($hasCustomHours) {
            $customHours = [
                'start_hour' => (int)($data['start_hour'] ?? $shift->start_hour),
                'start_minute' => (int)($data['start_minute'] ?? $shift->start_minute),
                'end_hour' => (int)($data['end_hour'] ?? $shift->end_hour),
                'end_minute' => (int)($data['end_minute'] ?? $shift->end_minute),
            ];
            $scheduleData['notes'] = json_encode($customHours);
        }

        return ScheduleRequest::create($scheduleData);
    }

    /**
     * ✅ Hủy đơn đăng ký (chỉ nếu chưa duyệt)
     */
    public function cancelScheduleRequest($requestId)
    {
        $request = ScheduleRequest::find($requestId);

        if (!$request) {
            throw new \Exception('❌ Không tìm thấy đơn đăng ký');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('❌ Chỉ có thể hủy các đơn đang chờ duyệt');
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
     * ✅ Kiểm tra: đã có đơn đăng ký cùng ngày cùng ca không?
     */
    public function hasDuplicateRequest($empId, $workDate, $shiftId)
    {
        return ScheduleRequest::where('employee_id', $empId)
            ->where('work_date', $workDate)
            ->where('shift_id', $shiftId)
            ->where('status', 'pending')
            ->exists();
    }
}