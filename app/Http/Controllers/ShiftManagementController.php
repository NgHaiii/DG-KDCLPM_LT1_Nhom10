<?php

namespace App\Http\Controllers;

use App\Models\CustomShift;
use Illuminate\Http\Request;

class ShiftManagementController extends Controller
{
    /**
     * Danh sách ca làm việc
     */
    public function index()
    {
        try {
            $shifts = CustomShift::with('creator')
                ->orderBy('start_hour', 'asc')
                ->get();
            
            return view('admin.shifts.index', compact('shifts'));
        } catch (\Exception $e) {
            return back()->with('error', '❌ Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Lưu ca làm việc
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_hour' => 'required|integer|between:0,23',
                'start_minute' => 'required|integer|between:0,59',
                'end_hour' => 'required|integer|between:0,23',
                'end_minute' => 'required|integer|between:0,59',
                'description' => 'nullable|string',
            ]);

            // Kiểm tra ít nhất áp dụng cho 1 nhóm
            if (!($request->has('is_for_doctor') || $request->has('is_for_employee'))) {
                return back()
                    ->withErrors(['apply' => 'Phải chọn ít nhất "Áp dụng cho Bác Sĩ" hoặc "Áp dụng cho Nhân Viên"'])
                    ->withInput();
            }

            // Convert checkbox thành boolean
            $validated['is_for_doctor'] = $request->has('is_for_doctor') ? 1 : 0;
            $validated['is_for_employee'] = $request->has('is_for_employee') ? 1 : 0;
            $validated['is_active'] = 1;
            $validated['created_by'] = auth()->id();

            CustomShift::create($validated);

            return redirect()->route('admin.shifts.index')->with('success', '✅ Thêm ca làm việc thành công!');
        } catch (\Exception $e) {
            return back()
                ->with('error', '❌ Lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Cập nhật ca làm việc
     */
    public function update(Request $request, CustomShift $shift)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_hour' => 'required|integer|between:0,23',
                'start_minute' => 'required|integer|between:0,59',
                'end_hour' => 'required|integer|between:0,23',
                'end_minute' => 'required|integer|between:0,59',
                'description' => 'nullable|string',
            ]);

            // Kiểm tra ít nhất áp dụng cho 1 nhóm
            if (!($request->has('is_for_doctor') || $request->has('is_for_employee'))) {
                return back()
                    ->withErrors(['apply' => 'Phải chọn ít nhất "Áp dụng cho Bác Sĩ" hoặc "Áp dụng cho Nhân Viên"'])
                    ->withInput();
            }

            // Convert checkbox thành boolean
            $validated['is_for_doctor'] = $request->has('is_for_doctor') ? 1 : 0;
            $validated['is_for_employee'] = $request->has('is_for_employee') ? 1 : 0;

            $shift->update($validated);

            return redirect()->route('admin.shifts.index')->with('success', '✅ Cập nhật ca làm việc thành công!');
        } catch (\Exception $e) {
            return back()
                ->with('error', '❌ Lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Xóa ca làm việc
     */
    public function destroy(CustomShift $shift)
    {
        try {
            $shiftName = $shift->name;
            $shift->delete();
            return back()->with('success', "✅ Đã xóa ca '{$shiftName}' thành công!");
        } catch (\Exception $e) {
            return back()->with('error', '❌ Lỗi: ' . $e->getMessage());
        }
    }
}