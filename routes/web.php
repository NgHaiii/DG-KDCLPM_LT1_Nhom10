<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;

// ==================== ROUTE CHÍNH ====================
Route::get('/', function () {
    return redirect()->route('login');
});

// ==================== AUTH ROUTES ====================
// Đăng ký
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);

// Đăng nhập
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

// Đăng xuất
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// ==================== QUÊN MẬT KHẨU - KHÔNG CẦN ĐĂNG NHẬP ====================
Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.handle');
Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::get('forgot-password/otp', [AuthController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('forgot-password/send-otp', [AuthController::class, 'sendOtp'])->name('password.send.otp');
Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify.otp');

// ==================== PROTECTED ROUTES - CẦN ĐĂNG NHẬP ====================
Route::middleware('auth')->group(function () {
    
    // ========== DASHBOARD CỦA TỪNG VAI TRÒ ==========
    Route::get('admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('doctor/dashboard', function () {
        return view('doctor.dashboard');
    })->name('doctor.dashboard');

    Route::get('employee/dashboard', function () {
        return view('employee.dashboard');
    })->name('employee.dashboard');

    Route::get('patient/dashboard', function () {
        return view('patient.dashboard');
    })->name('patient.dashboard');

    // ========== ADMIN PANEL - QUẢN LÝ BÁC SĨ VÀ NHÂN VIÊN ==========
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Danh sách bác sĩ
        Route::get('doctors', [EmployeeController::class, 'listDoctors'])->name('doctors');
        
        // Danh sách nhân viên
        Route::get('employees', [EmployeeController::class, 'listEmployees'])->name('employees');
        
        // Thêm mới bác sĩ/nhân viên
        Route::post('employee/store', [EmployeeController::class, 'store'])->name('employee.store');
        
        // Cập nhật bác sĩ/nhân viên
        Route::put('employee/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
        
        // Xóa bác sĩ/nhân viên
        Route::delete('employee/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
    });
});