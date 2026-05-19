<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OffDay;
use App\Models\ShiftAssignment;

class DutyManagementService
{
    /**
     * ✅ Gán ca trực cho bác sĩ (Admin only)
     */
    public function assignDutyToDoctor($employeeId, $workDate, $shiftId, $assignedBy, $notes = null)
    {
        // Kiểm tra bác sĩ tồn tại
        $doctor = Employee::find($employeeId);
        if (!$doctor || !$doctor->is_doctor) {
            throw new \Exception('Không tìm thấy bác sĩ');
        }

        // Kiểm tra đã có ca trực trong ngày không
        $existingDuty = ShiftAssignment::where('employee_id', $employeeId)
            ->where('work_date', $workDate)
            ->where('assignment_type', 'duty')
            ->first();

        if ($existingDuty) {
            throw new \Exception('Bác sĩ này đã có ca trực trong ngày');
        }

        // Kiểm tra bác sĩ có ngày nghỉ được duyệt không
        $hasApprovedOffDay = OffDay::where('employee_id', $employeeId)
            ->where('off_date', $workDate)
            ->where('status', 'approved')
            ->exists();

        if ($hasApprovedOffDay) {
            throw new \Exception('Bác sĩ có ngày nghỉ được duyệt vào ngày này');
        }

        // Tạo ca trực
        return ShiftAssignment::create([
            'employee_id' => $employeeId,
            'work_date' => $workDate,
            'shift_id' => $shiftId,
            'assignment_type' => 'duty',
            'assigned_by' => $assignedBy,
            'notes' => $notes,
        ]);
    }

    /**
     * ✅ Lấy danh sách ca trực của bác sĩ
     */
    public function getDoctorDuties($employeeId)
    {
        return ShiftAssignment::where('employee_id', $employeeId)
            ->where('assignment_type', 'duty')
            ->with('shift')
            ->orderBy('work_date', 'asc')
            ->get();
    }

    /**
     * ✅ Lấy danh sách bác sĩ có sẵn vào ngày đó (chưa có ca trực, không trong ngày nghỉ)
     */
    public function getAvailableDoctors($workDate)
    {
        // Lấy tất cả bác sĩ
        $allDoctors = Employee::where('is_doctor', true)->get();

        // Filter bác sĩ
        $availableDoctors = $allDoctors->filter(function($doctor) use ($workDate) {
            // Kiểm tra đã có ca trực trong ngày?
            $hasDuty = ShiftAssignment::where('employee_id', $doctor->id)
                ->where('work_date', $workDate)
                ->where('assignment_type', 'duty')
                ->exists();

            if ($hasDuty) {
                return false;
            }

            // Kiểm tra có ngày nghỉ được duyệt?
            $hasOffDay = OffDay::where('employee_id', $doctor->id)
                ->where('off_date', $workDate)
                ->where('status', 'approved')
                ->exists();

            if ($hasOffDay) {
                return false;
            }

            return true;
        });

        return $availableDoctors->values();
    }

    /**
     * ✅ Cập nhật ca trực
     */
    public function updateDuty($shiftAssignmentId, $shiftId, $notes = null)
    {
        $duty = ShiftAssignment::find($shiftAssignmentId);

        if (!$duty) {
            throw new \Exception('Không tìm thấy ca trực');
        }

        $duty->update([
            'shift_id' => $shiftId,
            'notes' => $notes,
        ]);

        return $duty;
    }

    /**
     * ✅ Hủy ca trực
     */
    public function cancelDuty($shiftAssignmentId)
    {
        $duty = ShiftAssignment::find($shiftAssignmentId);

        if (!$duty) {
            throw new \Exception('Không tìm thấy ca trực');
        }

        if ($duty->assignment_type !== 'duty') {
            throw new \Exception('Chỉ có thể hủy ca trực');
        }

        $duty->delete();
    }
}