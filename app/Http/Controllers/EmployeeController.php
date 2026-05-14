<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    protected $employeeService;
    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function listDoctors(Request $request)
    {
        $editId = $request->has('edit') ? $request->input('edit') : null;
        $data = $this->employeeService->listDoctors($editId);
        return view('admin.doctors.index', $data);
    }

    public function listEmployees(Request $request)
    {
        $editId = $request->has('edit') ? $request->input('edit') : null;
        $data = $this->employeeService->listEmployees($editId);
        return view('admin.employees.index', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'position' => 'required',
            'is_doctor' => 'boolean',
            'specialization' => 'nullable',
            'phone' => 'nullable',
            'address' => 'nullable',
            'dob' => 'nullable|date',
            'gender' => 'nullable',
            'workplace' => 'nullable',
            'degree' => 'nullable',
            'status' => 'nullable',
            'linkedUser' => 'nullable',
        ]);
        $validated['is_doctor'] = $request->boolean('is_doctor');

        try {
            $result = $this->employeeService->createEmployee($validated);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }

        $routeName = $validated['is_doctor'] ? 'admin.doctors' : 'admin.employees';
        $successMessage = $validated['is_doctor'] 
            ? "✅ Tài khoản bác sĩ đã tạo thành công!<br>Email: <b>{$result['email']}</b><br>Mã: <b>{$result['code']}</b><br>Role: <b>Doctor</b><br>Mật khẩu: <b>{$result['password']}</b>"
            : "✅ Tài khoản nhân viên đã tạo thành công!<br>Email: <b>{$result['email']}</b><br>Mã: <b>{$result['code']}</b><br>Role: <b>Employee</b><br>Mật khẩu: <b>{$result['password']}</b>";

        return redirect()->route($routeName)->with('success', $successMessage);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required',
            'phone' => 'nullable',
            'address' => 'nullable',
            'position' => 'required',
            'is_doctor' => 'boolean',
            'specialization' => 'nullable',
            'dob' => 'nullable|date',
            'gender' => 'nullable',
            'workplace' => 'nullable',
            'degree' => 'nullable',
            'status' => 'nullable',
            'linkedUser' => 'nullable',
        ]);
        $validated['is_doctor'] = $request->boolean('is_doctor');
        $this->employeeService->updateEmployee($employee, $validated);

        if ($employee->is_doctor) {
            return redirect()->route('admin.doctors')->with('success', '✅ Cập nhật bác sĩ thành công!');
        } else {
            return redirect()->route('admin.employees')->with('success', '✅ Cập nhật nhân viên thành công!');
        }
    }

    public function destroy(Employee $employee)
    {
        $isDoctor = $employee->is_doctor;
        $name = $employee->name;
        $this->employeeService->deleteEmployee($employee);

        if ($isDoctor) {
            return redirect()->route('admin.doctors')->with('success', "✅ Xóa bác sĩ <b>$name</b> thành công!");
        } else {
            return redirect()->route('admin.employees')->with('success', "✅ Xóa nhân viên <b>$name</b> thành công!");
        }
    }
}