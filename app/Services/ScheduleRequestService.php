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
     * 🔧 FIX: Thêm filter theo tuần nếu có tham số $weekStart, $weekEnd
     */
    public function getPendingRequests($empId, $weekStart = null, $weekEnd = null)
    {
        $query = ScheduleRequest::where('employee_id', $empId)
            ->where('status', 'pending')
            ->with('shift');
        
        // ✅ Nếu có tuần được chỉ định, filter theo đó
        if ($weekStart && $weekEnd) {
            $query->whereBetween('work_date', [$weekStart, $weekEnd]);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * ✅ Lấy các ca được duyệt của nhân viên
     * 🔧 FIX: Thêm filter theo tuần nếu có tham số $weekStart, $weekEnd
     */
    public function getApprovedRequests($empId, $weekStart = null, $weekEnd = null)
    {
        $query = ScheduleRequest::where('employee_id', $empId)
            ->where('status', 'approved')
            ->with('shift');
        
        // ✅ Nếu có tuần được chỉ định, filter theo đó
        if ($weekStart && $weekEnd) {
            $query->whereBetween('work_date', [$weekStart, $weekEnd]);
        }
        
        return $query->orderBy('work_date', 'asc')->get();
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
     * ✅ Tạo đơn đăng ký ca làm việc
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

        // 4️⃣ Kiểm tra: ca có áp dụng cho bác sĩ hoặc nhân viên không?
        if (!$shift->is_for_employee && !$shift->is_for_doctor) {
            throw new \Exception('❌ Ca này không áp dụng.');
        }

        // 5️⃣ Kiểm tra: đã có đơn pending cùng ngày cùng ca không?
        if ($this->hasDuplicateRequest($empId, $workDate, $shiftId)) {
            throw new \Exception('❌ Bạn đã đăng ký ca này rồi, vui lòng đợi duyệt.');
        }

        // 6️⃣ Kiểm tra: đã có schedule khác vào ngày này chưa?
        if ($this->hasScheduleOnDate($empId, $workDate)) {
            throw new \Exception('❌ Bạn đã có schedule vào ngày này rồi. Vui lòng thay đổi ngày hoặc xóa schedule cũ.');
        }

        // 7️⃣ Chuẩn bị dữ liệu để lưu vào database
        $scheduleData = [
            'employee_id' => $empId,
            'work_date' => $workDate,
            'shift_id' => $shiftId,
            'status' => 'pending',
            'assignment_type' => 'work',
        ];

        // 8️⃣ Nếu nhân viên tùy chỉnh giờ, lưu vào các cột riêng
        $hasCustomHours = !empty($data['start_hour']) || !empty($data['start_minute']) || 
                         !empty($data['end_hour']) || !empty($data['end_minute']);
        
        if ($hasCustomHours) {
            $scheduleData['start_hour'] = (int)($data['start_hour'] ?? $shift->start_hour);
            $scheduleData['start_minute'] = (int)($data['start_minute'] ?? $shift->start_minute);
            $scheduleData['end_hour'] = (int)($data['end_hour'] ?? $shift->end_hour);
            $scheduleData['end_minute'] = (int)($data['end_minute'] ?? $shift->end_minute);
        } else {
            // Nếu không tùy chỉnh, lấy từ shift cố định
            $scheduleData['start_hour'] = $shift->start_hour;
            $scheduleData['start_minute'] = $shift->start_minute;
            $scheduleData['end_hour'] = $shift->end_hour;
            $scheduleData['end_minute'] = $shift->end_minute;
        }

        return ScheduleRequest::create($scheduleData);
    }

    /**
     * ✅ CẬP NHẬT đơn đăng ký ca làm việc
     */
    public function updateScheduleRequest($requestId, $empId, $workDate, $shiftId, $data = [])
    {
        // 1️⃣ Tìm đơn đăng ký
        $request = ScheduleRequest::find($requestId);
        
        if (!$request) {
            throw new \Exception('❌ Không tìm thấy đơn đăng ký');
        }

        // 2️⃣ Kiểm tra quyền - chỉ chủ sở hữu mới được sửa
        if ($request->employee_id !== $empId) {
            throw new \Exception('❌ Bạn không có quyền cập nhật đơn này');
        }

        // 3️⃣ Kiểm tra trạng thái - chỉ sửa được đơn pending
        if ($request->status !== 'pending') {
            throw new \Exception('❌ Chỉ có thể cập nhật các đơn đang chờ duyệt');
        }

        // 4️⃣ Kiểm tra: nhân viên có đã xin nghỉ vào ngày mới không?
        if ($workDate !== $request->work_date && $this->hasApprovedOffDay($empId, $workDate)) {
            throw new \Exception('❌ Bạn đã xin nghỉ vào ngày này. Không thể cập nhật.');
        }

        // 5️⃣ 🆕 Kiểm tra: ngày mới đã có schedule khác chưa?
        if ($workDate !== $request->work_date) {
            $existingSchedule = ScheduleRequest::where('employee_id', $empId)
                ->where('work_date', $workDate)
                ->where('id', '!=', $requestId) // Exclude bản ghi hiện tại
                ->where('status', 'pending')
                ->first();
            
            if ($existingSchedule) {
                throw new \Exception('❌ Bạn đã có schedule vào ngày ' . $workDate . ' rồi. Vui lòng xóa hoặc thay đổi ngày khác.');
            }
        }

        // 6️⃣ Kiểm tra: shift_id có tồn tại không?
        $shift = CustomShift::find($shiftId);
        
        if (!$shift) {
            throw new \Exception('❌ Ca làm việc không tồn tại. Vui lòng chọn ca khác.');
        }

        // 7️⃣ Kiểm tra: ca có còn hoạt động không?
        if (!$shift->is_active) {
            throw new \Exception('❌ Ca làm việc này không còn hoạt động.');
        }

        // 8️⃣ Kiểm tra: ca có áp dụng cho bác sĩ hoặc nhân viên không?
        if (!$shift->is_for_employee && !$shift->is_for_doctor) {
            throw new \Exception('❌ Ca này không áp dụng.');
        }

        // 9️⃣ Chuẩn bị dữ liệu để cập nhật
        $updateData = [
            'work_date' => $workDate,
            'shift_id' => $shiftId,
            'status' => 'pending', // Reset lại pending khi cập nhật
        ];

        // 🔟 Nếu nhân viên tùy chỉnh giờ, lưu vào các cột riêng
        $hasCustomHours = !empty($data['start_hour']) || !empty($data['start_minute']) || 
                         !empty($data['end_hour']) || !empty($data['end_minute']);
        
        if ($hasCustomHours) {
            $updateData['start_hour'] = (int)($data['start_hour'] ?? $shift->start_hour);
            $updateData['start_minute'] = (int)($data['start_minute'] ?? $shift->start_minute);
            $updateData['end_hour'] = (int)($data['end_hour'] ?? $shift->end_hour);
            $updateData['end_minute'] = (int)($data['end_minute'] ?? $shift->end_minute);
        } else {
            // Nếu không tùy chỉnh, lấy từ shift cố định
            $updateData['start_hour'] = $shift->start_hour;
            $updateData['start_minute'] = $shift->start_minute;
            $updateData['end_hour'] = $shift->end_hour;
            $updateData['end_minute'] = $shift->end_minute;
        }

        // 1️⃣1️⃣ Cập nhật vào database
        $request->update($updateData);
        
        return $request;
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

    /**
     * ✅ 🆕 Kiểm tra: đã có schedule vào ngày này chưa? (bất kỳ ca nào)
     */
    public function hasScheduleOnDate($empId, $workDate)
    {
        return ScheduleRequest::where('employee_id', $empId)
            ->where('work_date', $workDate)
            ->where('status', 'pending')
            ->exists();
    }
}