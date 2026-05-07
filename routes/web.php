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
        return view('employee.dashboard');
    })->name('employee.dashboard');

    Route::get('patient/dashboard', function () {
        return view('patient.dashboard');
    })->name('patient.dashboard');

    // Quản lý nhân viên
    Route::resource('employees', EmployeeController::class);
});