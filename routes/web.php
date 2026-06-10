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
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceSpecializationController;
use App\Http\Controllers\DoctorAppointmentController;
use App\Http\Controllers\RoomController;

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

        // Quản lý phòng khám
        Route::resource('rooms', RoomController::class)->except(['show']);

        // Quản lý dịch vụ
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Gán chuyên khoa dịch vụ
        Route::get('services/specializations', [ServiceSpecializationController::class, 'index'])->name('service-specialization.index');
        Route::put('services/{id}/specialization', [ServiceSpecializationController::class, 'update'])->name('service-specialization.update');

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

        // LỊCH KHÁM ONLINE - Xác nhận lịch bệnh nhân đặt
        Route::get('appointments/online', [DoctorAppointmentController::class, 'onlineAppointments'])
            ->name('appointments.online');

        Route::post('appointments/online/{appointment}/confirm', [DoctorAppointmentController::class, 'confirmOnlineAppointment'])
            ->name('appointments.online.confirm');

        Route::post('appointments/online/{appointment}/cancel', [DoctorAppointmentController::class, 'cancelOnlineAppointment'])
            ->name('appointments.online.cancel');

        // LỊCH KHÁM CŨ
        Route::get('appointments', function () {
            return view('doctor.appointments');
        })->name('appointments');

        Route::get('appointments/create', function () {
            return view('doctor.appointments-create');
        })->name('appointments.create');

        Route::get('appointments/{id}', function ($id) {
            return view('doctor.appointments-view');
        })->name('appointments.view');

        // BỆNH NHÂN
        Route::get('patients', function () {
            return view('doctor.patients');
        })->name('patients');

        Route::get('patients/create', function () {
            return view('doctor.patients-create');
        })->name('patients.create');

        Route::get('patients/{id}', function ($id) {
            return view('doctor.patients-view');
        })->name('patients.view');

        // HỒ SƠ KHÁM
        Route::get('medical-records', function () {
            return view('doctor.medical-records');
        })->name('medical-records');

        // CÀI ĐẶT CÁ NHÂN
        Route::get('settings', function () {
            return view('doctor.settings');
        })->name('settings');

        // LỊCH LÀM VIỆC & NGÀY NGHỈ
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [DoctorScheduleController::class, 'create'])->name('create');
            Route::get('official', [DoctorScheduleController::class, 'officialSchedule'])->name('official');

            Route::post('/', [DoctorScheduleController::class, 'store'])->name('store');
            Route::put('{scheduleRequest}', [DoctorScheduleController::class, 'updateSchedule'])->name('update');
            Route::delete('{scheduleRequest}', [DoctorScheduleController::class, 'cancel'])->name('cancel');

            Route::post('request-off-day', [DoctorScheduleController::class, 'requestOffDay'])->name('request-off-day');
            Route::put('off-day/{offDay}', [DoctorScheduleController::class, 'updateOffDay'])->name('off-day.update');
            Route::delete('off-day/{offDay}', [DoctorScheduleController::class, 'destroyOffDay'])->name('off-day.destroy');

            Route::get('get-week-data', [DoctorScheduleController::class, 'getWeekData'])->name('get-week-data');
            Route::get('work-schedules', [DoctorScheduleController::class, 'getDoctorWorkSchedules'])->name('work-schedules');
            Route::get('official-schedule/get-week-data', [DoctorScheduleController::class, 'getOfficialWeekData'])->name('official-schedule.get-week-data');

            Route::get('approved', function () {
                return redirect(route('doctor.schedule.create'));
            })->name('approved');

            Route::get('off-days', function () {
                return redirect(route('doctor.schedule.create'));
            })->name('off-days');
        });

        // LỊCH TRỰC
        Route::prefix('duty')->name('duty.')->group(function () {
            Route::get('/', function () {
                return view('doctor.duty');
            })->name('index');

            Route::get('duties', [DoctorScheduleController::class, 'getDoctorDuties'])->name('get-duties');
        });
    });

    // ========== PROFILE SETTINGS ==========
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

        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [EmployeeScheduleController::class, 'create'])->name('create');
            Route::get('official', [EmployeeScheduleController::class, 'officialSchedule'])->name('official');

            Route::get('get-week-data', [EmployeeScheduleController::class, 'getWeekData'])->name('get-week-data');
            Route::get('official-schedule/get-week-data', [EmployeeScheduleController::class, 'getOfficialWeekData'])->name('official-schedule.get-week-data');

            Route::post('/', [EmployeeScheduleController::class, 'store'])->name('store');
            Route::put('{scheduleRequest}', [EmployeeScheduleController::class, 'updateSchedule'])->name('update');
            Route::delete('{scheduleRequest}', [EmployeeScheduleController::class, 'cancel'])->name('cancel');

            Route::post('off-day', [EmployeeScheduleController::class, 'requestOffDay'])->name('off-day.store');
            Route::put('off-day/{offDay}', [EmployeeScheduleController::class, 'updateOffDay'])->name('off-day.update');
            Route::delete('off-day/{offDay}', [EmployeeScheduleController::class, 'cancelOffDay'])->name('off-day.cancel');

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

        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointment.list');
        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointment.create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('appointment.store');
        Route::get('appointments/{id}', [AppointmentController::class, 'show'])->name('appointment.show');
        Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel');

        Route::get('appointment', function () {
            return redirect()->route('patient.appointment.list');
        });

        Route::get('appointment/create', function () {
            return redirect()->route('patient.appointment.create');
        });

        Route::get('appointment/{id}', function ($id) {
            return redirect()->route('patient.appointment.show', $id);
        });

        Route::get('api/service-categories', [AppointmentController::class, 'getServiceCategories'])
            ->name('api.service-categories');

        Route::get('api/services-by-category', [AppointmentController::class, 'getServicesByCategory'])
            ->name('api.services-by-category');

        Route::get('api/doctors-by-service', [AppointmentController::class, 'getDoctorsByService'])
            ->name('api.doctors-by-service');

        Route::get('api/available-slots', [AppointmentController::class, 'getAvailableSlots'])
            ->name('api.available-slots');

        Route::get('api/available-times', [AppointmentController::class, 'getAvailableTimes'])
            ->name('api.available-times');

        Route::get('api/doctors-by-time', [AppointmentController::class, 'getDoctorsByTime'])
            ->name('api.doctors-by-time');

        Route::get('medical-records', function () {
            return view('patient.medical-records');
        })->name('medical-records');

        Route::get('health-profile', function () {
            return view('patient.health-profile');
        })->name('health-profile');

        Route::get('invoices', function () {
            return view('patient.invoices');
        })->name('invoices');

        Route::get('payments', function () {
            return view('patient.payments');
        })->name('payments');

        Route::get('services', function () {
            return view('patient.services');
        })->name('services');

        Route::get('settings', function () {
            return view('patient.settings');
        })->name('settings');
    });
});