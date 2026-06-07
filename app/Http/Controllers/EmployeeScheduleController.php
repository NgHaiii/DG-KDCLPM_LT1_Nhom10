<?php

namespace App\Http\Controllers;

use App\Models\CustomShift;
use App\Models\Employee;
use App\Models\OffDay;
use App\Models\ScheduleRequest;
use App\Services\ScheduleRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    protected $scheduleRequestService;

    public function __construct(ScheduleRequestService $scheduleRequestService)
    {
        $this->scheduleRequestService = $scheduleRequestService;
    }

    private function getEmployee()
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Vui lòng đăng nhập');
        }

        $employee = $user->employee;

        if (!$employee) {
            $employee = Employee::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        return $employee;
    }

    public function create()
    {
        try {
            $employee = $this->getEmployee();

            $shifts = CustomShift::where('is_for_employee', 1)
                ->where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get();

            $pendingRequests = $this->scheduleRequestService->getPendingRequests($employee->id);
            $approvedSchedules = $this->scheduleRequestService->getApprovedRequests($employee->id);
            $approvedRequests = $approvedSchedules;

            $pendingOffDays = OffDay::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->orderBy('date', 'asc')
                ->get();

            $approvedOffDays = $this->scheduleRequestService->getApprovedOffDays($employee->id);

            return view('employees.schedule.request-form', compact(
                'shifts',
                'pendingRequests',
                'approvedRequests',
                'approvedSchedules',
                'pendingOffDays',
                'approvedOffDays',
                'employee'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    public function officialSchedule()
    {
        try {
            $employee = $this->getEmployee();

            $approvedSchedules = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->with('shift')
                ->orderBy('work_date', 'asc')
                ->get();

            $approvedOffDays = OffDay::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->orderBy('date', 'asc')
                ->get();

            return view('employees.schedule.official-schedule', compact(
                'approvedSchedules',
                'approvedOffDays',
                'employee'
            ));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Lỗi: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $employee = $this->getEmployee();

            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            $this->scheduleRequestService->createScheduleRequest(
                $employee->id,
                $validated['work_date'],
                (int) $validated['shift_id'],
                $validated
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '✅ Đã gửi đơn đăng ký ca làm việc!',
                ], 201);
            }

            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Đã gửi đơn đăng ký ca làm việc!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '❌ ' . $e->getMessage(),
                ], 422);
            }

            return redirect(route('employees.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    public function updateSchedule(Request $request, ScheduleRequest $scheduleRequest)
    {
        try {
            $employee = $this->getEmployee();

            $validated = $request->validate([
                'work_date' => 'required|date',
                'shift_id' => 'required|integer|exists:custom_shifts,id',
                'start_hour' => 'nullable|integer|between:0,23',
                'start_minute' => 'nullable|integer|between:0,59',
                'end_hour' => 'nullable|integer|between:0,23',
                'end_minute' => 'nullable|integer|between:0,59',
            ]);

            $this->scheduleRequestService->updateScheduleRequest(
                $scheduleRequest->id,
                $employee->id,
                $validated['work_date'],
                (int) $validated['shift_id'],
                $validated
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '✅ Đã cập nhật ca làm việc! Đơn này sẽ được duyệt lại.',
                ]);
            }

            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Đã cập nhật ca làm việc! Đơn này sẽ được duyệt lại.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '❌ ' . $e->getMessage(),
                ], 422);
            }

            return redirect(route('employees.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    public function cancel(Request $request, ScheduleRequest $scheduleRequest)
    {
        try {
            $employee = $this->getEmployee();

            if ($scheduleRequest->employee_id !== $employee->id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '❌ Không có quyền hủy đơn này',
                    ], 403);
                }

                return back()->withErrors(['error' => '❌ Không có quyền hủy đơn này']);
            }

            $this->scheduleRequestService->cancelScheduleRequest($scheduleRequest->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '✅ Đã hủy đơn đăng ký',
                ]);
            }

            return back()->with('success', '✅ Đã hủy đơn đăng ký');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '❌ ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    public function requestOffDay(Request $request)
    {
        try {
            $employee = $this->getEmployee();

            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|min:10|max:200',
            ]);

            $startDate = strtotime($validated['start_date']);
            $endDate = strtotime($validated['end_date']);

            for ($date = $startDate; $date <= $endDate; $date += 86400) {
                $dayStr = date('Y-m-d', $date);

                OffDay::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $dayStr,
                    ],
                    [
                        'reason' => $validated['reason'],
                        'status' => 'pending',
                    ]
                );
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '✅ Đã gửi đơn xin nghỉ!',
                ], 201);
            }

            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Đã gửi đơn xin nghỉ!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '❌ ' . $e->getMessage(),
                ], 422);
            }

            return redirect(route('employees.schedule.create'))
                ->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    public function updateOffDay(Request $request, OffDay $offDay)
    {
        try {
            $employee = $this->getEmployee();

            if ($offDay->employee_id !== $employee->id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '❌ Không có quyền cập nhật',
                    ], 403);
                }

                return back()->withErrors(['error' => '❌ Không có quyền cập nhật']);
            }

            if ($offDay->status !== 'pending') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '❌ Chỉ có thể cập nhật đơn đang chờ duyệt',
                    ], 422);
                }

                return back()->withErrors(['error' => '❌ Chỉ có thể cập nhật đơn đang chờ duyệt']);
            }

            if ($request->has('date')) {
                $validated = $request->validate([
                    'date' => 'required|date',
                    'reason' => 'required|string|min:10|max:200',
                ]);

                $date = $validated['date'];
            } else {
                $validated = $request->validate([
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'reason' => 'required|string|min:10|max:200',
                ]);

                $date = $validated['start_date'];
            }

            $offDay->update([
                'date' => $date,
                'reason' => $validated['reason'],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '✅ Cập nhật đơn xin nghỉ thành công!',
                ]);
            }

            return redirect(route('employees.schedule.create'))
                ->with('success', '✅ Cập nhật đơn xin nghỉ thành công!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '❌ ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    public function cancelOffDay(OffDay $offDay)
    {
        try {
            $employee = $this->getEmployee();

            if ($offDay->employee_id !== $employee->id) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'message' => '❌ Không có quyền hủy',
                    ], 403);
                }

                return back()->withErrors(['error' => '❌ Không có quyền hủy']);
            }

            if ($offDay->status !== 'pending') {
                if (request()->expectsJson()) {
                    return response()->json([
                        'message' => '❌ Chỉ có thể hủy đơn đang chờ duyệt',
                    ], 422);
                }

                return back()->withErrors(['error' => '❌ Chỉ có thể hủy đơn đang chờ duyệt']);
            }

            $offDay->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => '✅ Đã hủy đơn xin nghỉ',
                ]);
            }

            return back()->with('success', '✅ Đã hủy đơn xin nghỉ');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => '❌ ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => '❌ ' . $e->getMessage()]);
        }
    }

    public function destroyOffDay(OffDay $offDay)
    {
        return $this->cancelOffDay($offDay);
    }

    public function getWeekData(Request $request)
    {
        try {
            $employee = $this->getEmployee();

            $weekStart = $request->has('week_start')
                ? Carbon::createFromFormat('Y-m-d', $request->week_start)->startOfWeek(Carbon::MONDAY)
                : now()->startOfWeek(Carbon::MONDAY);

            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $shifts = CustomShift::where('is_for_employee', 1)
                ->where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get()
                ->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => strtolower($s->name),
                        'start_hour' => $s->start_hour,
                        'start_minute' => $s->start_minute,
                        'end_hour' => $s->end_hour,
                        'end_minute' => $s->end_minute,
                    ];
                });

            $pendingRequests = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'date' => $r->work_date->format('Y-m-d'),
                        'name' => optional($r->shift)->name ?? 'Ca làm việc',
                        'time' => sprintf('%02d:%02d', $r->start_hour ?? 0, $r->start_minute ?? 0)
                            . ' - ' .
                            sprintf('%02d:%02d', $r->end_hour ?? 0, $r->end_minute ?? 0),
                        'shift_id' => $r->shift_id,
                        'status' => 'pending',
                        'start_hour' => $r->start_hour,
                        'start_minute' => $r->start_minute,
                        'end_hour' => $r->end_hour,
                        'end_minute' => $r->end_minute,
                    ];
                });

            $approvedRequests = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->get()
                ->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'date' => $s->work_date->format('Y-m-d'),
                        'name' => optional($s->shift)->name ?? 'Ca làm việc',
                        'time' => sprintf('%02d:%02d', $s->start_hour ?? 0, $s->start_minute ?? 0)
                            . ' - ' .
                            sprintf('%02d:%02d', $s->end_hour ?? 0, $s->end_minute ?? 0),
                        'shift_id' => $s->shift_id,
                        'status' => 'approved',
                        'start_hour' => $s->start_hour,
                        'start_minute' => $s->start_minute,
                        'end_hour' => $s->end_hour,
                        'end_minute' => $s->end_minute,
                    ];
                });

            return response()->json([
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'shifts' => $shifts,
                'pending_requests' => $pendingRequests,
                'approved_requests' => $approvedRequests,
                'pending_count' => ScheduleRequest::where('employee_id', $employee->id)->where('status', 'pending')->count(),
                'approved_count' => ScheduleRequest::where('employee_id', $employee->id)->where('status', 'approved')->count(),
                'approved_off_days' => OffDay::where('employee_id', $employee->id)->where('status', 'approved')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '❌ ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getOfficialWeekData(Request $request)
    {
        try {
            $employee = $this->getEmployee();

            $weekStart = $request->has('week_start')
                ? Carbon::createFromFormat('Y-m-d', $request->week_start)->startOfWeek(Carbon::MONDAY)
                : now()->startOfWeek(Carbon::MONDAY);

            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $shifts = CustomShift::where('is_for_employee', 1)
                ->where('is_active', 1)
                ->orderBy('start_hour', 'asc')
                ->get()
                ->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => strtolower($s->name),
                        'start_hour' => $s->start_hour,
                        'start_minute' => $s->start_minute,
                        'end_hour' => $s->end_hour,
                        'end_minute' => $s->end_minute,
                    ];
                });

            $schedules = ScheduleRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('work_date', [$weekStart, $weekEnd])
                ->with('shift')
                ->get()
                ->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'date' => $s->work_date->format('Y-m-d'),
                        'name' => optional($s->shift)->name ?? 'Ca làm việc',
                        'time' => sprintf('%02d:%02d', $s->start_hour ?? 0, $s->start_minute ?? 0)
                            . ' - ' .
                            sprintf('%02d:%02d', $s->end_hour ?? 0, $s->end_minute ?? 0),
                        'shift_id' => $s->shift_id,
                        'start_hour' => $s->start_hour,
                        'start_minute' => $s->start_minute,
                        'end_hour' => $s->end_hour,
                        'end_minute' => $s->end_minute,
                    ];
                });

            $offDays = OffDay::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->get()
                ->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'date' => optional($o->date)->format('Y-m-d') ?? $o->date,
                        'reason' => $o->reason ?? 'Xin nghỉ',
                    ];
                });

            return response()->json([
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'shifts' => $shifts,
                'schedules' => $schedules,
                'off_days' => $offDays,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '❌ ' . $e->getMessage(),
            ], 500);
        }
    }
}