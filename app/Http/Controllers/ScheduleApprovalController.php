<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ScheduleRequest;
use App\Models\OffDay;
use App\Services\ScheduleApprovalService;
use Illuminate\Http\Request;

class ScheduleApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(ScheduleApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Danh sách đơn đăng ký & ngày nghỉ chờ duyệt
     */
    public function index()
    {
        $pendingRequests = $this->approvalService->getPendingRequests();
        $pendingOffDays = $this->approvalService->getPendingOffDays();
        $stats = $this->approvalService->getStats();

        return view('admin.schedule-approval.index', compact('pendingRequests', 'pendingOffDays', 'stats'));
    }

    /**
     * Chi tiết đơn đăng ký
     */
    public function show(ScheduleRequest $scheduleRequest)
    {
        $request = $this->approvalService->getScheduleRequestDetail($scheduleRequest->id);
        
        return view('admin.schedule-approval.show', compact('request'));
    }

    /**
     * Phê duyệt đơn đăng ký
     */
    public function approve(ScheduleRequest $scheduleRequest, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $this->approvalService->approveScheduleRequest(
                $scheduleRequest->id,
                auth()->id(),
                $validated['notes'] ?? null
            );

            return back()->with('success', '✅ Đã phê duyệt đơn');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Từ chối đơn đăng ký
     */
    public function reject(ScheduleRequest $scheduleRequest, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $this->approvalService->rejectScheduleRequest(
                $scheduleRequest->id,
                auth()->id(),
                $validated['notes'] ?? null
            );

            return back()->with('success', '✅ Đã từ chối đơn');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Phê duyệt ngày nghỉ
     */
    public function approveOffDay(OffDay $offDay, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $this->approvalService->approveOffDay(
                $offDay->id,
                auth()->id(),
                $validated['notes'] ?? null
            );

            return back()->with('success', '✅ Đã phê duyệt ngày nghỉ');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Từ chối ngày nghỉ
     */
    public function rejectOffDay(OffDay $offDay, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $this->approvalService->rejectOffDay(
                $offDay->id,
                auth()->id(),
                $validated['notes'] ?? null
            );

            return back()->with('success', '✅ Đã từ chối ngày nghỉ');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Xem lịch chi tiết của nhân viên/bác sĩ
     */
    public function employeeSchedules(Employee $employee)
    {
        $allSchedules = $this->approvalService->getEmployeeAllSchedules($employee->id);
        $stats = $this->approvalService->getEmployeeStats($employee->id);

        return view('admin.schedule-approval.employee-schedules', compact('employee', 'allSchedules', 'stats'));
    }

    /**
     * Danh sách bác sĩ
     */
    public function doctors()
    {
        $doctors = $this->approvalService->getAllDoctors();

        return view('admin.schedule-approval.doctors', compact('doctors'));
    }

    /**
     * Danh sách nhân viên
     */
    public function employees()
    {
        $employees = $this->approvalService->getAllEmployees();

        return view('admin.schedule-approval.employees', compact('employees'));
    }
}