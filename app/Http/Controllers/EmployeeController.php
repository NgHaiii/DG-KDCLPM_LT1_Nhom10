<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // Danh sách bác sĩ
    public function listDoctors(Request $request)
    {
        $employees = Employee::where('is_doctor', true)->get();
        $editEmployee = null;
        if ($request->has('edit')) {
            $editEmployee = Employee::find($request->input('edit'));
        }
        return view('admin.doctors.index', compact('employees', 'editEmployee'));
    }

    // Danh sách nhân viên
    public function listEmployees(Request $request)
    {
        $employees = Employee::where('is_doctor', false)->get();
        $editEmployee = null;
        if ($request->has('edit')) {
            $editEmployee = Employee::find($request->input('edit'));
        }
        return view('admin.employees.index', compact('employees', 'editEmployee'));
    }

    // Thêm mới nhân viên hoặc bác sĩ, tự sinh tài khoản user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'position' => 'required',
            'is_doctor' => 'boolean',
            'specialization' => 'nullable',
            'phone' => 'nullable',
            'address' => 'nullable',
        ]);
        $validated['is_doctor'] = $request->boolean('is_doctor');

        // Sinh mã và email theo vai trò
        if ($validated['is_doctor']) {
            $prefix = 'BS';
            $suffix = 'bs';
            $role = 'doctor';
            $count = Employee::where('is_doctor', true)->count() + 1;
        } else {
            $prefix = 'NV';
            $suffix = 'nv';
            $role = 'employee';
            $count = Employee::where('is_doctor', false)->count() + 1;
        }

        $code = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
        $email = $code . $suffix . '@hdat-dental.com.vn';

        // Kiểm tra email đã tồn tại ở bảng users chưa
        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['email' => "Email $email đã tồn tại, vui lòng kiểm tra lại!"])->withInput();
        }

        // Sinh mật khẩu ngẫu nhiên
        $passwordPlain = Str::random(8);

        // Tạo user với role chính xác và đồng bộ phone
        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($passwordPlain),
            'role' => $role,
            'phone' => $validated['phone'] ?? null, // ĐỒNG BỘ PHONE
        ]);

        // Lưu vào bảng employees
        $validated['email'] = $email;
        $validated['code'] = $code;
        Employee::create($validated);

        $routeName = $validated['is_doctor'] ? 'admin.doctors' : 'admin.employees';
        $successMessage = $validated['is_doctor'] 
            ? "✅ Tài khoản bác sĩ đã tạo thành công!<br>Email: <b>$email</b><br>Mã: <b>$code</b><br>Role: <b>Doctor</b><br>Mật khẩu: <b>$passwordPlain</b>"
            : "✅ Tài khoản nhân viên đã tạo thành công!<br>Email: <b>$email</b><br>Mã: <b>$code</b><br>Role: <b>Employee</b><br>Mật khẩu: <b>$passwordPlain</b>";

        return redirect()->route($routeName)->with('success', $successMessage);
    }

    // Cập nhật nhân viên/bác sĩ
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required',
            'phone' => 'nullable',
            'address' => 'nullable',
            'position' => 'required',
            'is_doctor' => 'boolean',
            'specialization' => 'nullable',
        ]);
        $validated['is_doctor'] = $request->boolean('is_doctor');
        $employee->update($validated);

        // ĐỒNG BỘ PHONE SANG BẢNG USERS
        if ($employee->email && $validated['phone']) {
            User::where('email', $employee->email)->update(['phone' => $validated['phone']]);
        }

        if ($employee->is_doctor) {
            return redirect()->route('admin.doctors')->with('success', '✅ Cập nhật bác sĩ thành công!');
        } else {
            return redirect()->route('admin.employees')->with('success', '✅ Cập nhật nhân viên thành công!');
        }
    }

    // Xóa nhân viên/bác sĩ và user liên quan
    public function destroy(Employee $employee)
    {
        $isDoctor = $employee->is_doctor;
        $name = $employee->name;

        // Xóa user có email trùng với employee
        if ($employee->email) {
            User::where('email', $employee->email)->delete();
        }

        $employee->delete();

        if ($isDoctor) {
            return redirect()->route('admin.doctors')->with('success', "✅ Xóa bác sĩ <b>$name</b> thành công!");
        } else {
            return redirect()->route('admin.employees')->with('success', "✅ Xóa nhân viên <b>$name</b> thành công!");
        }
    }
}