<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;

// Khi truy cập trang chủ, tự động chuyển đến trang đăng nhập
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Các route cần đăng nhập
Route::middleware('auth')->group(function () {
    Route::get('change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('change-password', [AuthController::class, 'changePassword'])->name('password.update');

    // Dashboard cho từng vai trò
    Route::get('admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('doctor/dashboard', function () {
        return view('doctor.dashboard');
    })->name('doctor.dashboard');

    Route::get('employee/dashboard', function () {
        return view('employees.dashboard');  // ✅ SỬA: 'employee' → 'employees'
    })->name('employee.dashboard');

    Route::get('patient/dashboard', function () {
        return view('patient.dashboard');
    })->name('patient.dashboard');

    // Quản lý bác sĩ và nhân viên (Admin only)
    Route::prefix('admin')->group(function () {
        // Danh sách bác sĩ
        Route::get('doctors', [EmployeeController::class, 'listDoctors'])->name('admin.doctors');
        
        // Danh sách nhân viên
        Route::get('employees', [EmployeeController::class, 'listEmployees'])->name('admin.employees');
        
        // Thêm/sửa/xóa nhân viên hoặc bác sĩ
        Route::post('employee/store', [EmployeeController::class, 'store'])->name('admin.employee.store');
        Route::put('employee/{employee}', [EmployeeController::class, 'update'])->name('admin.employee.update');
        Route::delete('employee/{employee}', [EmployeeController::class, 'destroy'])->name('admin.employee.destroy');
    });
});