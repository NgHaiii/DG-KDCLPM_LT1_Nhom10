<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Services\DutyManagementService;
use Illuminate\Http\Request;

class AdminDutyController extends Controller
{
    protected $dutyManagementService;

    public function __construct(DutyManagementService $dutyManagementService)
    {
        $this->dutyManagementService = $dutyManagementService;
    }

    /**
     * Danh sách ca trực
     */
    public function index()
    {
        $duties = ShiftAssignment::where('assignment_type', 'duty')
            ->with(['employee', 'shift'])
            ->orderBy('work_date', 'asc')
            ->get();

        $stats = [
            'total' => $duties->count(),
            'assigned' => $duties->count(),
        ];

        return view('admin.duty.index', compact('duties', 'stats'));
    }

    /**
     * Form thêm ca trực
     */
    public function create()
    {
        $shifts = Shift::where('is_active', true)->get();
        
        return view('admin.duty.create', compact('shifts'));
    }

    /**
     * Lưu ca trực (POST)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'work_date' => 'required|date',
                'shift_id' => 'required|exists:shifts,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $this->dutyManagementService->assignDutyToDoctor(
                $validated['employee_id'],
                $validated['work_date'],
                $validated['shift_id'],
                auth()->id(),
                $validated['notes'] ?? ''
            );

            return redirect()->route('admin.duty.index')
                ->with('success', '✅ Đã gán ca trực cho bác sĩ');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật ca trực (PUT)
     */
    public function update(ShiftAssignment $shiftAssignment, Request $request)
    {
        try {
            $validated = $request->validate([
                'shift_id' => 'required|exists:shifts,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $this->dutyManagementService->updateDuty(
                $shiftAssignment->id,
                $validated['shift_id'],
                $validated['notes'] ?? ''
            );

            return back()->with('success', '✅ Đã cập nhật ca trực');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Xóa ca trực (DELETE)
     */
    public function destroy(ShiftAssignment $shiftAssignment)
    {
        try {
            $this->dutyManagementService->cancelDuty($shiftAssignment->id);

            return back()->with('success', '✅ Đã xóa ca trực');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Lấy danh sách bác sĩ có sẵn theo ngày
     */
    public function getAvailableDoctors(Request $request)
    {
        try {
            $workDate = $request->query('work_date');
            
            if (!$workDate) {
                return response()->json(['error' => 'work_date required'], 400);
            }

            $doctors = $this->dutyManagementService->getAvailableDoctors($workDate);

            return response()->json([
                'success' => true,
                'doctors' => $doctors->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->user->name ?? $d->phone,
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}