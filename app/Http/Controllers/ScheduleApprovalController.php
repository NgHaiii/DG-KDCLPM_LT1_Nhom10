<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ScheduleRequest;
use App\Models\OffDay;
use App\Models\CustomShift;
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
     * Danh sách đơn đăng ký & ngày nghỉ chờ duyệt + đã duyệt
     */
    public function index()
    {
        // Lấy tất cả shifts
        $shifts = CustomShift::where('is_active', 1)
            ->orderBy('start_hour', 'asc')
            ->get();

        // ===== PENDING REQUESTS =====
        $allPendingRequests = ScheduleRequest::where('status', 'pending')
            ->with('employee', 'shift')
            ->orderBy('created_at', 'desc')
            ->get();

        // Phân tách bác sĩ pending
        $pendingDoctorIds = $allPendingRequests
            ->where('employee.is_doctor', 1)
            ->pluck('employee_id')
            ->unique();

        $pendingEmployeeIds = $allPendingRequests
            ->where('employee.is_doctor', 0)
            ->pluck('employee_id')
            ->unique();

        // Danh sách bác sĩ có pending requests
        $pendingDoctorsList = Employee::whereIn('id', $pendingDoctorIds)
            ->where('is_doctor', 1)
            ->with(['scheduleRequests' => function($q) {
                $q->where('status', 'pending');
            }])
            ->get()
            ->map(function($doctor) {
                $doctor->pending_requests_count = $doctor->scheduleRequests->count();
                return $doctor;
            });

        // Danh sách nhân viên có pending requests
        $pendingEmployeesList = Employee::whereIn('id', $pendingEmployeeIds)
            ->where('is_doctor', 0)
            ->with(['scheduleRequests' => function($q) {
                $q->where('status', 'pending');
            }])
            ->get()
            ->map(function($emp) {
                $emp->pending_requests_count = $emp->scheduleRequests->count();
                return $emp;
            });

        // ===== APPROVED REQUESTS =====
        $allApprovedRequests = ScheduleRequest::where('status', 'approved')
            ->with('employee', 'shift')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Phân tách bác sĩ approved
        $approvedDoctorIds = $allApprovedRequests
            ->where('employee.is_doctor', 1)
            ->pluck('employee_id')
            ->unique();

        $approvedEmployeeIds = $allApprovedRequests
            ->where('employee.is_doctor', 0)
            ->pluck('employee_id')
            ->unique();

        // Danh sách bác sĩ có approved requests
        $approvedDoctorsList = Employee::whereIn('id', $approvedDoctorIds)
            ->where('is_doctor', 1)
            ->with(['scheduleRequests' => function($q) {
                $q->where('status', 'approved');
            }])
            ->get()
            ->map(function($doctor) {
                $doctor->approved_requests_count = $doctor->scheduleRequests->count();
                return $doctor;
            });

        // Danh sách nhân viên có approved requests
        $approvedEmployeesList = Employee::whereIn('id', $approvedEmployeeIds)
            ->where('is_doctor', 0)
            ->with(['scheduleRequests' => function($q) {
                $q->where('status', 'approved');
            }])
            ->get()
            ->map(function($emp) {
                $emp->approved_requests_count = $emp->scheduleRequests->count();
                return $emp;
            });

        // Lấy pending off days
        $pendingOffDays = OffDay::where('status', 'pending')
            ->with('employee')
            ->orderBy('date', 'asc')
            ->get();

        // ===== STATISTICS =====
        $stats = [
            'total_pending_requests' => $allPendingRequests->count(),
            'total_approved_requests' => $allApprovedRequests->count(),
            'total_pending_offdays' => $pendingOffDays->count(),
            'total_rejected_requests' => ScheduleRequest::where('status', 'rejected')->count(),
        ];

        $pendingDoctorsCount = $pendingDoctorsList->count();
        $pendingEmployeesCount = $pendingEmployeesList->count();
        $approvedSchedulesCount = $allApprovedRequests->count();

        return view('admin.schedule-approval.index', compact(
            'pendingDoctorsList',
            'pendingEmployeesList',
            'approvedDoctorsList',
            'approvedEmployeesList',
            'pendingOffDays',
            'shifts',
            'stats',
            'pendingDoctorsCount',
            'pendingEmployeesCount',
            'approvedSchedulesCount'
        ));
    }

    /**
     * API: Lấy pending requests của employee
     */
    public function getEmployeeRequests($employeeId)
    {
        $requests = ScheduleRequest::where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->with('shift')
            ->orderBy('work_date', 'asc')
            ->get()
            ->map(function($req) {
                return [
                    'id' => $req->id,
                    'work_date' => $req->work_date->format('Y-m-d'),
                    'shift_id' => $req->shift_id,
                    'shift_name' => $req->shift?->name ?? 'N/A',
                    'time_range' => $req->time_range,
                    'start_hour' => $req->start_hour ?? $req->shift?->start_hour ?? 0,
                    'start_minute' => $req->start_minute ?? $req->shift?->start_minute ?? 0,
                    'end_hour' => $req->end_hour ?? $req->shift?->end_hour ?? 0,
                    'end_minute' => $req->end_minute ?? $req->shift?->end_minute ?? 0,
                    'notes' => $req->notes ?? '',
                    'status' => $req->status,
                ];
            });

        return response()->json($requests);
    }

    /**
     * API: Lấy approved requests của employee để chỉnh sửa
     */
    public function getApprovedRequests($employeeId)
    {
        $requests = ScheduleRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->with('shift')
            ->orderBy('work_date', 'asc')
            ->get()
            ->map(function($req) {
                return [
                    'id' => $req->id,
                    'work_date' => $req->work_date->format('Y-m-d'),
                    'shift_id' => $req->shift_id,
                    'shift_name' => $req->shift?->name ?? 'N/A',
                    'time_range' => $req->time_range,
                    'start_hour' => $req->start_hour ?? $req->shift?->start_hour ?? 0,
                    'start_minute' => $req->start_minute ?? $req->shift?->start_minute ?? 0,
                    'end_hour' => $req->end_hour ?? $req->shift?->end_hour ?? 0,
                    'end_minute' => $req->end_minute ?? $req->shift?->end_minute ?? 0,
                    'notes' => $req->notes ?? '',
                    'status' => $req->status,
                ];
            });

        return response()->json($requests);
    }

    /**
     * Phê duyệt đơn đăng ký
     */
    public function approve(ScheduleRequest $scheduleRequest, Request $request)
    {
        try {
            $validated = $request->validate([
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
                'notes' => 'nullable|string|max:500',
            ]);

            // Cập nhật thông tin
            $scheduleRequest->update([
                'shift_id' => $validated['shift_id'],
                'start_hour' => $validated['start_hour'] ?? null,
                'start_minute' => $validated['start_minute'] ?? null,
                'end_hour' => $validated['end_hour'] ?? null,
                'end_minute' => $validated['end_minute'] ?? null,
                'status' => 'approved',
                'notes' => $validated['notes'] ?? '',
                'assigned_by' => auth()->user()->id, // Lưu ID người duyệt
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn đăng ký đã được duyệt!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Từ chối đơn đăng ký
     */
    public function reject(ScheduleRequest $scheduleRequest, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'required|string|max:500',
            ]);

            $scheduleRequest->update([
                'status' => 'rejected',
                'notes' => $validated['notes'],
                'assigned_by' => auth()->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn đăng ký đã bị từ chối!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Duyệt đơn xin nghỉ
     */
    public function approveOffDay(OffDay $offDay, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $offDay->update([
                'status' => 'approved',
                'notes' => $validated['notes'] ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn xin nghỉ đã được duyệt!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Từ chối đơn xin nghỉ
     */
    public function rejectOffDay(OffDay $offDay, Request $request)
    {
        try {
            $validated = $request->validate([
                'notes' => 'required|string|max:500',
            ]);

            $offDay->update([
                'status' => 'rejected',
                'notes' => $validated['notes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn xin nghỉ đã bị từ chối!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}