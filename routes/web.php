<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\ScheduleApprovalController;
use App\Http\Controllers\AdminDutyController;

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

    Route::get('patient/dashboard', function () {
        return view('patient.dashboard');
    })->name('patient.dashboard');

    // ========== ADMIN PANEL - QUẢN LÝ BÁC SĨ, NHÂN VIÊN, DỊCH VỤ, GIÁ ==========
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Danh sách bác sĩ
        Route::get('doctors', [EmployeeController::class, 'listDoctors'])->name('doctors');
        
        // Danh sách nhân viên
        Route::get('employees', [EmployeeController::class, 'listEmployees'])->name('employees');
        
        // Thêm mới bác sĩ/nhân viên
        Route::post('employee/store', [EmployeeController::class, 'store'])->name('employee.store');
        
        // Cập nhật bác sĩ/nhân viên
        Route::put('employee/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::patch('employee/{employee}', [EmployeeController::class, 'update']);
        
        // Xóa bác sĩ/nhân viên
        Route::delete('employee/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');

        // ========== QUẢN LÝ DỊCH VỤ ==========
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // ========== QUẢN LÝ GIÁ DỊCH VỤ ==========
        Route::get('prices', [PriceController::class, 'index'])->name('prices.index');
        Route::post('prices', [PriceController::class, 'store'])->name('prices.store');
        Route::patch('prices/{price}', [PriceController::class, 'update'])->name('prices.update');
        Route::delete('prices/{price}', [PriceController::class, 'destroy'])->name('prices.destroy');

        // ========== UC2.1 + UC2.2: QUẢN LÝ LỊCH TRÌNH BÁC SĨ & NHÂN VIÊN ==========
        Route::prefix('schedule-approval')->name('schedule-approval.')->group(function () {
            // Danh sách đơn đăng ký (UC2.1 & UC2.2)
            Route::get('/', [ScheduleApprovalController::class, 'index'])->name('index');
            
            // Chi tiết đơn
            Route::get('request/{scheduleRequest}', [ScheduleApprovalController::class, 'show'])->name('show');
            
            // Phê duyệt ca làm việc (UC2.2)
            Route::post('request/{scheduleRequest}/approve', [ScheduleApprovalController::class, 'approve'])->name('approve');
            Route::post('request/{scheduleRequest}/reject', [ScheduleApprovalController::class, 'reject'])->name('reject');
            
            // Phê duyệt ngày nghỉ (UC2.1)
            Route::post('off-day/{offDay}/approve', [ScheduleApprovalController::class, 'approveOffDay'])->name('off-day.approve');
            Route::post('off-day/{offDay}/reject', [ScheduleApprovalController::class, 'rejectOffDay'])->name('off-day.reject');
            
            // Xem lịch chi tiết của nhân viên/bác sĩ
            Route::get('employee/{employee}', [ScheduleApprovalController::class, 'employeeSchedules'])->name('employee-schedules');
            
            // Danh sách bác sĩ
            Route::get('doctors', [ScheduleApprovalController::class, 'doctors'])->name('doctors');
            
            // Danh sách nhân viên
            Route::get('employees', [ScheduleApprovalController::class, 'employees'])->name('employees');
        });

        // ========== UC2.3: QUẢN LÝ CA TRỰC BÁC SĨ ==========
        Route::prefix('duty')->name('duty.')->group(function () {
            // Danh sách ca trực
            Route::get('/', [AdminDutyController::class, 'index'])->name('index');
            
            // Form thêm ca trực
            Route::get('create', [AdminDutyController::class, 'create'])->name('create');
            
            // Thêm ca trực
            Route::post('/', [AdminDutyController::class, 'store'])->name('store');
            
            // Cập nhật ca trực
            Route::put('{shiftAssignment}', [AdminDutyController::class, 'update'])->name('update');
            
            // Xóa ca trực
            Route::delete('{shiftAssignment}', [AdminDutyController::class, 'destroy'])->name('destroy');
            
            // AJAX: Lấy danh sách bác sĩ có sẵn theo ngày
            Route::get('available-doctors', [AdminDutyController::class, 'getAvailableDoctors'])->name('available-doctors');
        });
    });

    // ========== DOCTOR PANEL - BÁC SĨ ==========
    Route::prefix('doctor')->name('doctor.')->group(function () {
        
        // ========== UC2.1: ĐƠN XIN NGHỈ ==========
        Route::prefix('schedule')->name('schedule.')->group(function () {
            // Trang đăng ký ca làm việc + ngày nghỉ
            Route::get('/', [DoctorScheduleController::class, 'create'])->name('create');
            
            // Đăng ký ca làm việc (UC2.2)
            Route::post('/', [DoctorScheduleController::class, 'store'])->name('store');
            
            // Hủy đơn đăng ký ca làm việc (UC2.2)
            Route::delete('{scheduleRequest}', [DoctorScheduleController::class, 'cancel'])->name('cancel');
            
            // Xem lịch đã duyệt (UC2.2)
            Route::get('approved', [DoctorScheduleController::class, 'myApprovedSchedules'])->name('approved');
            
            // ========== UC2.1: NGÀY NGHỈ ==========
            // Đăng ký ngày nghỉ
            Route::post('off-day', [DoctorScheduleController::class, 'requestOffDay'])->name('off-day.store');
            
            // Xem danh sách ngày nghỉ
            Route::get('off-days', [DoctorScheduleController::class, 'myOffDays'])->name('off-days');
            
            // Hủy đơn xin nghỉ
            Route::delete('off-day/{offDay}', [DoctorScheduleController::class, 'cancelOffDay'])->name('off-day.cancel');
        });

        // ========== UC2.3: CA TRỰC BÁC SĨ (READONLY) ==========
        Route::get('duties', [DoctorScheduleController::class, 'myDuties'])->name('duties');
    });

    // ========== EMPLOYEE PANEL - NHÂN VIÊN ==========
    Route::prefix('employee')->name('employee.')->group(function () {
        
        // Dashboard
        Route::get('dashboard', function () {
            return view('employees.dashboard');
        })->name('dashboard');

        // Tiếp nhận bệnh nhân
        Route::get('reception', function () {
            return view('employees.reception');
        })->name('reception');

        // ========== UC2.1 + UC2.2: LỊCH LÀM VIỆC & NGÀY NGHỈ ==========
        Route::prefix('schedule')->name('schedule.')->group(function () {
            // Trang đăng ký ca làm việc + ngày nghỉ
            Route::get('/', [EmployeeScheduleController::class, 'create'])->name('create');
            
            // Đăng ký ca làm việc (UC2.2) - Nhân viên chỉ có Sáng/Chiều
            Route::post('/', [EmployeeScheduleController::class, 'store'])->name('store');
            
            // Hủy đơn đăng ký ca làm việc (UC2.2)
            Route::delete('{scheduleRequest}', [EmployeeScheduleController::class, 'cancel'])->name('cancel');
            
            // Xem lịch đã duyệt (UC2.2)
            Route::get('approved', [EmployeeScheduleController::class, 'myApprovedSchedules'])->name('approved');
            
            // ========== UC2.1: NGÀY NGHỈ ==========
            // Đăng ký ngày nghỉ
            Route::post('off-day', [EmployeeScheduleController::class, 'requestOffDay'])->name('off-day.store');
            
            // Xem danh sách ngày nghỉ
            Route::get('off-days', [EmployeeScheduleController::class, 'myOffDays'])->name('off-days');
            
            // Hủy đơn xin nghỉ
            Route::delete('off-day/{offDay}', [EmployeeScheduleController::class, 'cancelOffDay'])->name('off-day.cancel');
        });

        // Đặt lịch khám
        Route::get('appointment', function () {
            return view('employees.appointment');
        })->name('appointment');

        // Thanh toán
        Route::get('payment', function () {
            return view('employees.payment');
        })->name('payment');

        // Hóa đơn
        Route::get('invoice', function () {
            return view('employees.invoice');
        })->name('invoice');

        // Bảng giá dịch vụ
        Route::get('services', function () {
            $services = \App\Models\Service::with('currentPrice')->get();
            return view('employees.services', compact('services'));
        })->name('services');

        // Cài đặt cá nhân
        Route::get('settings', function () {
            return view('employees.settings');
        })->name('settings');
    });
});