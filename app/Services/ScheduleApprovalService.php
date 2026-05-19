<?php

namespace App\Services;

use App\Models\ScheduleRequest;
use App\Models\OffDay;
use App\Models\ShiftAssignment;
use App\Models\Employee;

class ScheduleApprovalService
{
    /**
     * ✅ Lấy danh sách đơn đăng ký ca chờ xác nhận (Admin view)
     */
    public function getPendingRequests($perPage = 15)
    {
        return ScheduleRequest::where('status', 'pending')
            ->with('employee', 'shift')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * ✅ Lấy danh sách đơn xin nghỉ chờ xác nhận (Admin view)
     */
    public function getPendingOffDays($perPage = 10)
    {
        return OffDay::where('status', 'pending')
            ->with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * ✅ Lấy chi tiết đơn đăng ký
     */
    public function getScheduleRequestDetail($requestId)
    {
        return ScheduleRequest::with('employee', 'shift')->find($requestId);
    }

    /**
     * ✅ Lấy lịch đã phê duyệt của nhân viên
     */
    public function getApprovedSchedules($employeeId, $limit = 10)
    {
        return ScheduleRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->with('shift')
            ->orderBy('work_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ✅ Lấy ngày nghỉ đã phê duyệt của nhân viên
     */
    public function getApprovedOffDays($employeeId, $limit = 5)
    {
        return OffDay::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->orderBy('off_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ✅ Lấy toàn bộ lịch của nhân viên
     */
    public function getEmployeeAllSchedules($employeeId)
    {
        return [
            'requests' => ScheduleRequest::where('employee_id', $employeeId)
                ->with('shift')
                ->orderBy('work_date', 'desc')
                ->get(),
            'off_days' => OffDay::where('employee_id', $employeeId)
                ->orderBy('off_date', 'desc')
                ->get(),
        ];
    }

    /**
     * ✅ Lấy thống kê chi tiết của nhân viên
     */
    public function getEmployeeStats($employeeId): array
    {
        $requests = ScheduleRequest::where('employee_id', $employeeId)->get();
        $offDays = OffDay::where('employee_id', $employeeId)->get();

        return [
            'total_pending' => $requests->where('status', 'pending')->count(),
            'total_approved' => $requests->where('status', 'approved')->count(),
            'total_rejected' => $requests->where('status', 'rejected')->count(),
            'off_days_pending' => $offDays->where('status', 'pending')->count(),
            'off_days_approved' => $offDays->where('status', 'approved')->count(),
        ];
    }

    /**
     * ✅ Lấy thống kê tổng (Admin dashboard)
     */
    public function getStats(): array
    {
        return [
            'total_pending_requests' => ScheduleRequest::where('status', 'pending')->count(),
            'total_pending_offdays' => OffDay::where('status', 'pending')->count(),
            'total_approved_today' => ScheduleRequest::where('status', 'approved')
                ->where('work_date', now()->toDateString())
                ->count(),
        ];
    }

    /**
     * ✅ Phê duyệt đơn đăng ký ca làm việc
     */
    public function approveScheduleRequest($requestId, $adminId, $notes = null): ScheduleRequest
    {
        $request = ScheduleRequest::find($requestId);

        if (!$request) {
            throw new \Exception('Không tìm thấy đơn đăng ký');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('Đơn này đã được xử lý');
        }

        // Cập nhật request
        $request->update([
            'status' => 'approved',
            'admin_notes' => $notes,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        // Tạo ShiftAssignment (work schedule)
        ShiftAssignment::create([
            'employee_id' => $request->employee_id,
            'work_date' => $request->work_date,
            'shift_id' => $request->shift_id,
            'assignment_type' => 'work',
            'assigned_by' => $adminId,
            'notes' => $notes,
        ]);

        return $request;
    }

    /**
     * ✅ Từ chối đơn đăng ký ca làm việc
     */
    public function rejectScheduleRequest($requestId, $adminId, $notes = null): ScheduleRequest
    {
        $request = ScheduleRequest::find($requestId);

        if (!$request) {
            throw new \Exception('Không tìm thấy đơn đăng ký');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('Đơn này đã được xử lý');
        }

        $request->update([
            'status' => 'rejected',
            'admin_notes' => $notes,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $request;
    }

    /**
     * ✅ Phê duyệt ngày nghỉ
     */
    public function approveOffDay($offDayId, $adminId, $notes = null): OffDay
    {
        $offDay = OffDay::find($offDayId);

        if (!$offDay) {
            throw new \Exception('Không tìm thấy đơn xin nghỉ');
        }

        if ($offDay->status !== 'pending') {
            throw new \Exception('Đơn này đã được xử lý');
        }

        $offDay->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);

        return $offDay;
    }

    /**
     * ✅ Từ chối ngày nghỉ
     */
    public function rejectOffDay($offDayId, $adminId, $notes = null): OffDay
    {
        $offDay = OffDay::find($offDayId);

        if (!$offDay) {
            throw new \Exception('Không tìm thấy đơn xin nghỉ');
        }

        if ($offDay->status !== 'pending') {
            throw new \Exception('Đơn này đã được xử lý');
        }

        $offDay->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);

        return $offDay;
    }

    /**
     * ✅ Lấy danh sách bác sĩ
     */
    public function getAllDoctors($perPage = 10)
    {
        return Employee::where('is_doctor', true)
            ->with('user')
            ->paginate($perPage);
    }

    /**
     * ✅ Lấy danh sách nhân viên
     */
    public function getAllEmployees($perPage = 10)
    {
        return Employee::where('is_doctor', false)
            ->with('user')
            ->paginate($perPage);
    }

    /**
     * ✅ Lấy danh sách bác sĩ có đơn pending
     */
    public function getDoctorsWithPendingRequests()
    {
        return Employee::where('is_doctor', true)
            ->whereHas('scheduleRequests', function($query) {
                $query->where('status', 'pending');
            })
            ->with(['scheduleRequests' => function($query) {
                $query->where('status', 'pending')
                    ->with('shift')
                    ->orderBy('created_at', 'desc');
            }])
            ->get();
    }

    /**
     * ✅ Lấy danh sách nhân viên có đơn pending
     */
    public function getEmployeesWithPendingRequests()
    {
        return Employee::where('is_doctor', false)
            ->whereHas('scheduleRequests', function($query) {
                $query->where('status', 'pending');
            })
            ->with(['scheduleRequests' => function($query) {
                $query->where('status', 'pending')
                    ->with('shift')
                    ->orderBy('created_at', 'desc');
            }])
            ->get();
    }
}