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

// Quên mật khẩu - KHÔNG cần đăng nhập
Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.handle');
Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::get('forgot-password/otp', [AuthController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('forgot-password/send-otp', [AuthController::class, 'sendOtp'])->name('password.send.otp');
Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify.otp');

// Các route cần đăng nhập
Route::middleware('auth')->group(function () {
    // Dashboard cho từng vai trò
    Route::get('admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('doctor/dashboard', function () {
        return view('doctor.dashboard');
    })->name('doctor.dashboard');

    Route::get('employee/dashboard', function () {
        return view('employees.dashboard');
    })->name('employee.dashboard');

    Route::get('patient/dashboard', function () {
        return view('patient.dashboard');
    })->name('patient.dashboard');

    // Quản lý bác sĩ và nhân viên (Admin only)
    Route::prefix('admin')->group(function () {
        Route::get('doctors', [EmployeeController::class, 'listDoctors'])->name('admin.doctors');
        Route::get('employees', [EmployeeController::class, 'listEmployees'])->name('admin.employees');
        Route::post('employee/store', [EmployeeController::class, 'store'])->name('admin.employee.store');
        Route::put('employee/{employee}', [EmployeeController::class, 'update'])->name('admin.employee.update');
        Route::delete('employee/{employee}', [EmployeeController::class, 'destroy'])->name('admin.employee.destroy');
    });
});