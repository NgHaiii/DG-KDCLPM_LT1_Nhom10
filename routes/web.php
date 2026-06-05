<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ShiftManagementController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\EmployeeScheduleController;
use App\Http\Controllers\ScheduleApprovalController;
use App\Http\Controllers\AdminDutyController;

// ==================== ROUTE CHÍNH ====================
Route::get('/', function () {
    return redirect()->route('login');
});

// ==================== AUTH ROUTES ====================
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// ==================== QUÊN MẬT KHẨU ====================
Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.handle');
Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::get('forgot-password/otp', [AuthController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('forgot-password/send-otp', [AuthController::class, 'sendOtp'])->name('password.send.otp');
Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify.otp');

// ==================== PROTECTED ROUTES ====================
Route::middleware('auth')->group(function () {
    // ========== DASHBOARD ==========
    Route::get('admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('doctor/dashboard', function () {
        return view('doctor.dashboard');
    })->name('doctor.dashboard');

    Route::get('patient/dashboard', function () {
        return view('patient.dashboard');
    })->name('patient.dashboard');

    Route::get('employees/dashboard', function () {
        return view('employees.dashboard');
    })->name('employees.dashboard');

    // ========== ADMIN PANEL ==========
    Route::prefix('admin')->name('admin.')->group(function () {
        // Quản lý bác sĩ & nhân viên
        Route::get('doctors', [EmployeeController::class, 'listDoctors'])->name('doctors');
        Route::get('employees', [EmployeeController::class, 'listEmployees'])->name('employees');
        Route::post('employee/store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::put('employee/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::patch('employee/{employee}', [EmployeeController::class, 'update']);
        Route::delete('employee/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');

        // Quản lý ca làm việc
        Route::resource('shifts', ShiftManagementController::class)->names('shifts');

        // Quản lý dịch vụ
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Quản lý giá
        Route::get('prices', [PriceController::class, 'index'])->name('prices.index');
        Route::post('prices', [PriceController::class, 'store'])->name('prices.store');
        Route::patch('prices/{price}', [PriceController::class, 'update'])->name('prices.update');
        Route::delete('prices/{price}', [PriceController::class, 'destroy'])->name('prices.destroy');

        // Quản lý lịch trình
        Route::prefix('schedule-approval')->name('schedule-approval.')->group(function () {
            Route::get('/', [ScheduleApprovalController::class, 'index'])->name('index');
            Route::get('employee/{employeeId}/requests', [ScheduleApprovalController::class, 'getEmployeeRequests'])->name('employee.requests');
            Route::get('employee/{employeeId}/approved', [ScheduleApprovalController::class, 'getApprovedRequests'])->name('employee.approved');
            Route::get('request/{scheduleRequest}', [ScheduleApprovalController::class, 'show'])->name('show');
            Route::post('request/{scheduleRequest}/approve', [ScheduleApprovalController::class, 'approve'])->name('approve');
            Route::post('request/{scheduleRequest}/reject', [ScheduleApprovalController::class, 'reject'])->name('reject');
            Route::post('off-day/{offDay}/approve', [ScheduleApprovalController::class, 'approveOffDay'])->name('off-day.approve');
            Route::post('off-day/{offDay}/reject', [ScheduleApprovalController::class, 'rejectOffDay'])->name('off-day.reject');
            Route::get('employee/{employee}', [ScheduleApprovalController::class, 'employeeSchedules'])->name('employee-schedules');
            Route::get('doctors', [ScheduleApprovalController::class, 'doctors'])->name('doctors');
            Route::get('employees', [ScheduleApprovalController::class, 'employees'])->name('employees');
        });

        // Quản lý ca trực bác sĩ
        Route::prefix('duty')->name('duty.')->group(function () {
            Route::get('/', [AdminDutyController::class, 'index'])->name('index');
            Route::post('store', [AdminDutyController::class, 'store'])->name('store');
            Route::put('{shiftAssignment}', [AdminDutyController::class, 'update'])->name('update');
            Route::delete('{shiftAssignment}', [AdminDutyController::class, 'destroy'])->name('destroy');
            
            // API AJAX
            Route::get('api/calendar', [AdminDutyController::class, 'getCalendarBySpecialty'])->name('api.calendar');
            Route::get('api/doctors-by-date', [AdminDutyController::class, 'getDoctorsByDate'])->name('api.doctors-by-date');
        });
    });

    // ========== DOCTOR PANEL ==========
    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::get('dashboard', function () {
            return view('doctor.dashboard');
        })->name('dashboard');

        // LỊCH KHÁM (APPOINTMENTS)
        Route::get('appointments', function () {
            return view('doctor.appointments');
        })->name('appointments');
        Route::get('appointments/create', function () {
            return view('doctor.appointments-create');
        })->name('appointments.create');
        Route::get('appointments/{id}', function ($id) {
            return view('doctor.appointments-view');
        })->name('appointments.view');

        // BỆNH NHÂN (PATIENTS)
        Route::get('patients', function () {
            return view('doctor.patients');
        })->name('patients');
        Route::get('patients/create', function () {
            return view('doctor.patients-create');
        })->name('patients.create');
        Route::get('patients/{id}', function ($id) {
            return view('doctor.patients-view');
        })->name('patients.view');

        // HỒ SƠ KHÁM (MEDICAL RECORDS)
        Route::get('medical-records', function () {
            return view('doctor.medical-records');
        })->name('medical-records');

        // CÀI ĐẶT CÁ NHÂN (SETTINGS)
        Route::get('settings', function () {
            return view('doctor.settings');
        })->name('settings');

        // ✅ LỊCH LÀM VIỆC & NGÀY NGHỈ
        Route::prefix('schedule')->name('schedule.')->group(function () {
            // ✅ PAGE VIEW
            Route::get('/', [DoctorScheduleController::class, 'create'])->name('create');
            Route::get('official', [DoctorScheduleController::class, 'officialSchedule'])->name('official');
            
            // ✅ SCHEDULE REQUEST - CRUD (AJAX)
            Route::post('/', [DoctorScheduleController::class, 'store'])->name('store');
            Route::put('{scheduleRequest}', [DoctorScheduleController::class, 'updateSchedule'])->name('update');
            Route::delete('{scheduleRequest}', [DoctorScheduleController::class, 'cancel'])->name('cancel');
            
            // ✅ OFF-DAY REQUEST - CRUD (AJAX)
            Route::post('request-off-day', [DoctorScheduleController::class, 'requestOffDay'])->name('request-off-day');
            Route::put('off-day/{offDay}', [DoctorScheduleController::class, 'updateOffDay'])->name('off-day.update');
            Route::delete('off-day/{offDay}', [DoctorScheduleController::class, 'destroyOffDay'])->name('off-day.destroy');
            
            // ✅ API ROUTES - AJAX DATA FETCHING
            Route::get('get-week-data', [DoctorScheduleController::class, 'getWeekData'])->name('get-week-data');
            Route::get('work-schedules', [DoctorScheduleController::class, 'getDoctorWorkSchedules'])->name('work-schedules');
            
            // ✅ OFFICIAL SCHEDULE API - Lấy dữ liệu lịch chính thức
            Route::get('official-schedule/get-week-data', [DoctorScheduleController::class, 'getOfficialWeekData'])->name('official-schedule.get-week-data');
            
            // ✅ LEGACY REDIRECTS
            Route::get('approved', function () {
                return redirect(route('doctor.schedule.create'));
            })->name('approved');
            Route::get('off-days', function () {
                return redirect(route('doctor.schedule.create'));
            })->name('off-days');
        });

        // ✅ LỊCH TRỰC (DUTY SCHEDULE) - Xem ca trực được giao
        Route::prefix('duty')->name('duty.')->group(function () {
            Route::get('/', function () {
                return view('doctor.duty');
            })->name('index');
            
            // ✅ API ROUTE - AJAX DATA FETCHING
            Route::get('duties', [DoctorScheduleController::class, 'getDoctorDuties'])->name('get-duties');
        });
    });

    // ========== PROFILE SETTINGS (CÀI ĐẶT HỒ SƠ) ==========
    Route::get('profile/edit', function () {
        return view('profile.edit');
    })->name('profile.edit');

    // ========== EMPLOYEE PANEL ==========
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('dashboard', function () {
            return view('employees.dashboard');
        })->name('dashboard');
        Route::get('reception', function () {
            return view('employees.reception');
        })->name('reception');

        // LỊCH LÀM VIỆC & NGÀY NGHỈ
        Route::prefix('schedule')->name('schedule.')->group(function () {
            // ✅ PAGE VIEW
            Route::get('/', [EmployeeScheduleController::class, 'create'])->name('create');
            Route::get('official', [EmployeeScheduleController::class, 'officialSchedule'])->name('official');
            
            // ✅ API ROUTES - AJAX DATA FETCHING
            Route::get('get-week-data', [EmployeeScheduleController::class, 'getWeekData'])->name('get-week-data');
            
            // ✅ SCHEDULE REQUEST - CRUD (AJAX)
            Route::post('/', [EmployeeScheduleController::class, 'store'])->name('store');
            Route::put('{scheduleRequest}', [EmployeeScheduleController::class, 'updateSchedule'])->name('update');
            Route::delete('{scheduleRequest}', [EmployeeScheduleController::class, 'cancel'])->name('cancel');
            
            // ✅ OFF-DAY ROUTES (Xin ngày nghỉ)
            Route::post('off-day', [EmployeeScheduleController::class, 'requestOffDay'])->name('off-day.store');
            Route::put('off-day/{offDay}', [EmployeeScheduleController::class, 'updateOffDay'])->name('off-day.update');
            Route::delete('off-day/{offDay}', [EmployeeScheduleController::class, 'cancelOffDay'])->name('off-day.cancel');
            
            // ✅ LEGACY REDIRECTS
            Route::get('approved', function () {
                return redirect(route('employees.schedule.create'));
            })->name('approved');
            Route::get('off-days', function () {
                return redirect(route('employees.schedule.create'));
            })->name('off-days');
        });

        Route::get('appointment', function () {
            return view('employees.appointment');
        })->name('appointment');
        Route::get('payment', function () {
            return view('employees.payment');
        })->name('payment');
        Route::get('invoice', function () {
            return view('employees.invoice');
        })->name('invoice');
        Route::get('services', function () {
            $services = \App\Models\Service::with('currentPrice')->get();
            return view('employees.services', compact('services'));
        })->name('services');
        Route::get('settings', function () {
            return view('employees.settings');
        })->name('settings');
    });

    // ========== PATIENT PANEL ==========
    Route::prefix('patient')->name('patient.')->group(function () {
        Route::get('dashboard', function () {
            return view('patient.dashboard');
        })->name('dashboard');

        // LỊCH KHÁM
        Route::prefix('appointment')->name('appointment.')->group(function () {
            Route::get('list', function () {
                return view('patient.appointments');
            })->name('list');
            Route::get('create', function () {
                return view('patient.appointments-create');
            })->name('create');
            Route::get('{id}', function ($id) {
                return view('patient.appointments-view');
            })->name('show');
        });

        // HỒ SƠ BỆNH NHÂN
        Route::get('medical-records', function () {
            return view('patient.medical-records');
        })->name('medical-records');

        Route::get('health-profile', function () {
            return view('patient.health-profile');
        })->name('health-profile');

        // TÀI CHÍNH
        Route::get('invoices', function () {
            return view('patient.invoices');
        })->name('invoices');

        Route::get('payments', function () {
            return view('patient.payments');
        })->name('payments');

        // DỊCH VỤ
        Route::get('services', function () {
            $services = \App\Models\Service::with('currentPrice')->get();
            return view('patient.services', compact('services'));
        })->name('services');

        // CÀI ĐẶT
        Route::get('settings', function () {
            return view('patient.settings');
        })->name('settings');
    });
});