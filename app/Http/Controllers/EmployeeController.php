<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // Hiển thị danh sách và form thêm/sửa
    public function index(Request $request)
    {
        $employees = Employee::all();
        $editEmployee = null;
        if ($request->has('edit')) {
            $editEmployee = Employee::find($request->input('edit'));
        }
        return view('employees.index', compact('employees', 'editEmployee'));
    }

    // Thêm mới nhân viên
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable',
            'address' => 'nullable',
            'position' => 'required',
            'is_doctor' => 'boolean',
            'specialization' => 'nullable',
        ]);
        $validated['is_doctor'] = $request->has('is_doctor');
        Employee::create($validated);
        return redirect()->route('employees.index');
    }

    // Cập nhật nhân viên
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable',
            'address' => 'nullable',
            'position' => 'required',
            'is_doctor' => 'boolean',
            'specialization' => 'nullable',
        ]);
        $validated['is_doctor'] = $request->has('is_doctor');
        $employee->update($validated);
        return redirect()->route('employees.index');
    }

    // Xóa nhân viên
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index');
    }
}