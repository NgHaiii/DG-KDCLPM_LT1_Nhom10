<?php

namespace app\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function listDoctors($editId = null)
    {
        $employees = Employee::where('is_doctor', true)->get();
        $editEmployee = $editId ? Employee::find($editId) : null;
        return compact('employees', 'editEmployee');
    }

    public function listEmployees($editId = null)
    {
        $employees = Employee::where('is_doctor', false)->get();
        $editEmployee = $editId ? Employee::find($editId) : null;
        return compact('employees', 'editEmployee');
    }

    public function createEmployee(array $validated)
    {
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

        if (User::where('email', $email)->exists()) {
            throw new \Exception("Email $email đã tồn tại, vui lòng kiểm tra lại!");
        }

        $passwordPlain = Str::random(8);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($passwordPlain),
            'role' => $role,
            'phone' => $validated['phone'] ?? null,
        ]);

        $validated['email'] = $email;
        $validated['code'] = $code;
        Employee::create($validated);

        return [
            'email' => $email,
            'code' => $code,
            'role' => $role,
            'password' => $passwordPlain,
            'is_doctor' => $validated['is_doctor'],
        ];
    }

    public function updateEmployee(Employee $employee, array $validated)
    {
        $employee->update($validated);

        if ($employee->email && $validated['phone']) {
            User::where('email', $employee->email)->update(['phone' => $validated['phone']]);
        }
    }

    public function deleteEmployee(Employee $employee)
    {
        if ($employee->email) {
            User::where('email', $employee->email)->delete();
        }
        $employee->delete();
    }
}