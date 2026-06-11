<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PriceController;
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
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\DentalChartController;

// ==================== ROUTE CHÍNH ====================
Route::get('/', function () {
    return redirect()->route('login');
});

// ==================== AUTH ROUTES ====================
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.store');

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.handle');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// ==================== QUÊN MẬT KHẨU ====================
Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.handle');

Route::get('forgot-password/otp', [AuthController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('forgot-password/send-otp', [AuthController::class, 'sendOtp'])->name('password.send.otp');
Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.verify.otp');

Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// ==================== PROTECTED ROUTES ====================
Route::middleware('auth')->group(function () {
    // ==================== DASHBOARD ====================
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

    Route::get('profile/edit', function () {
        return view('profile.edit');
    })->name('profile.edit');

    // ==================== ADMIN PANEL ====================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('doctors', [EmployeeController::class, 'listDoctors'])->name('doctors');
        Route::get('employees', [EmployeeController::class, 'listEmployees'])->name('employees');

        Route::post('employee/store', [EmployeeController::class, 'store'])->name('employee.store');
        Route::put('employee/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::patch('employee/{employee}', [EmployeeController::class, 'update'])->name('employee.patch');
        Route::delete('employee/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');

        Route::resource('shifts', ShiftManagementController::class)->names('shifts');

        Route::resource('rooms', RoomController::class)
            ->except(['show'])
            ->names('rooms');

        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('services/specializations', [ServiceSpecializationController::class, 'index'])
            ->name('service-specialization.index');

        Route::put('services/{id}/specialization', [ServiceSpecializationController::class, 'update'])
            ->name('service-specialization.update');

        Route::get('prices', [PriceController::class, 'index'])->name('prices.index');
        Route::post('prices', [PriceController::class, 'store'])->name('prices.store');
        Route::patch('prices/{price}', [PriceController::class, 'update'])->name('prices.update');
        Route::delete('prices/{price}', [PriceController::class, 'destroy'])->name('prices.destroy');

        Route::prefix('schedule-approval')->name('schedule-approval.')->group(function () {
            Route::get('/', [ScheduleApprovalController::class, 'index'])->name('index');

            Route::get('employee/{employeeId}/requests', [ScheduleApprovalController::class, 'getEmployeeRequests'])
                ->name('employee.requests');

            Route::get('employee/{employeeId}/approved', [ScheduleApprovalController::class, 'getApprovedRequests'])
                ->name('employee.approved');

            Route::get('request/{scheduleRequest}', [ScheduleApprovalController::class, 'show'])
                ->name('show');

            Route::post('request/{scheduleRequest}/approve', [ScheduleApprovalController::class, 'approve'])
                ->name('approve');

            Route::post('request/{scheduleRequest}/reject', [ScheduleApprovalController::class, 'reject'])
                ->name('reject');

            Route::post('off-day/{offDay}/approve', [ScheduleApprovalController::class, 'approveOffDay'])
                ->name('off-day.approve');

            Route::post('off-day/{offDay}/reject', [ScheduleApprovalController::class, 'rejectOffDay'])
                ->name('off-day.reject');

            Route::get('employee/{employee}', [ScheduleApprovalController::class, 'employeeSchedules'])
                ->name('employee-schedules');

            Route::get('doctors', [ScheduleApprovalController::class, 'doctors'])->name('doctors');
            Route::get('employees', [ScheduleApprovalController::class, 'employees'])->name('employees');
        });

        Route::prefix('duty')->name('duty.')->group(function () {
            Route::get('/', [AdminDutyController::class, 'index'])->name('index');
            Route::post('store', [AdminDutyController::class, 'store'])->name('store');
            Route::put('{shiftAssignment}', [AdminDutyController::class, 'update'])->name('update');
            Route::delete('{shiftAssignment}', [AdminDutyController::class, 'destroy'])->name('destroy');

            Route::get('api/calendar', [AdminDutyController::class, 'getCalendarBySpecialty'])
                ->name('api.calendar');

            Route::get('api/doctors-by-date', [AdminDutyController::class, 'getDoctorsByDate'])
                ->name('api.doctors-by-date');
        });
    });

    // ==================== DOCTOR PANEL ====================
    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::get('dashboard', function () {
            return view('doctor.dashboard');
        })->name('dashboard');

        // ----- Xác nhận lịch online -----
        Route::get('appointments/online', [DoctorAppointmentController::class, 'onlineAppointments'])
            ->name('appointments.online');

        Route::post('appointments/online/{appointment}/confirm', [DoctorAppointmentController::class, 'confirmOnlineAppointment'])
            ->whereNumber('appointment')
            ->name('appointments.online.confirm');

        Route::post('appointments/online/{appointment}/cancel', [DoctorAppointmentController::class, 'cancelOnlineAppointment'])
            ->whereNumber('appointment')
            ->name('appointments.online.cancel');

        // ----- Hồ sơ bệnh nhân / hồ sơ bệnh án cho bác sĩ -----
        Route::prefix('patient-profiles')->name('patient-profiles.')->group(function () {
            Route::get('/', [PatientProfileController::class, 'doctorIndex'])
                ->name('index');

            Route::get('{patientProfile}/dental-chart', [DentalChartController::class, 'show'])
                ->whereNumber('patientProfile')
                ->name('dental-chart.show');

            Route::post('{patientProfile}/dental-chart', [DentalChartController::class, 'store'])
                ->whereNumber('patientProfile')
                ->name('dental-chart.store');

            Route::get('{patientProfile}', [PatientProfileController::class, 'doctorShow'])
                ->whereNumber('patientProfile')
                ->name('show');

            Route::put('{patientProfile}', [PatientProfileController::class, 'doctorUpdate'])
                ->whereNumber('patientProfile')
                ->name('update');

            Route::put('medical-records/{appointment}', [PatientProfileController::class, 'doctorUpdateMedicalRecord'])
                ->whereNumber('appointment')
                ->name('medical-records.update');

            Route::post('clinical-images/{appointment}', [PatientProfileController::class, 'doctorStoreClinicalImage'])
                ->whereNumber('appointment')
                ->name('clinical-images.store');

            Route::delete('clinical-images/{clinicalImage}', [PatientProfileController::class, 'doctorDestroyClinicalImage'])
                ->whereNumber('clinicalImage')
                ->name('clinical-images.destroy');
        });

        // ----- UC3.2: Khám bệnh và cập nhật hồ sơ -----
        Route::prefix('examinations')->name('examinations.')->group(function () {
            Route::get('/', [ExaminationController::class, 'index'])->name('index');

            Route::post('{appointment}/start', [ExaminationController::class, 'start'])
                ->whereNumber('appointment')
                ->name('start');

            Route::get('{appointment}', [ExaminationController::class, 'show'])
                ->whereNumber('appointment')
                ->name('show');

            Route::post('{appointment}/complete', [ExaminationController::class, 'complete'])
                ->whereNumber('appointment')
                ->name('complete');
        });

        // ----- Các route view cũ nếu vẫn còn dùng -----
        Route::get('appointments', function () {
            return view('doctor.appointments');
        })->name('appointments');

        Route::get('appointments/create', function () {
            return view('doctor.appointments-create');
        })->name('appointments.create');

        Route::get('appointments/{id}', function ($id) {
            return view('doctor.appointments-view', compact('id'));
        })->whereNumber('id')->name('appointments.view');

        Route::get('patients', function () {
            return redirect()->route('doctor.patient-profiles.index');
        })->name('patients');

        Route::get('patients/create', function () {
            return view('doctor.patients-create');
        })->name('patients.create');

        Route::get('patients/{id}', function ($id) {
            return redirect()->route('doctor.patient-profiles.show', $id);
        })->whereNumber('id')->name('patients.view');

        Route::get('medical-records', function () {
            return view('doctor.medical-records');
        })->name('medical-records');

        Route::get('settings', function () {
            return view('doctor.settings');
        })->name('settings');

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

            Route::get('official-schedule/get-week-data', [DoctorScheduleController::class, 'getOfficialWeekData'])
                ->name('official-schedule.get-week-data');

            Route::get('approved', function () {
                return redirect()->route('doctor.schedule.create');
            })->name('approved');

            Route::get('off-days', function () {
                return redirect()->route('doctor.schedule.create');
            })->name('off-days');
        });

        Route::prefix('duty')->name('duty.')->group(function () {
            Route::get('/', function () {
                return view('doctor.duty');
            })->name('index');

            Route::get('duties', [DoctorScheduleController::class, 'getDoctorDuties'])->name('get-duties');
        });
    });

    // ==================== EMPLOYEE PANEL ====================
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('dashboard', function () {
            return view('employees.dashboard');
        })->name('dashboard');

        // ----- UC3.1: Tiếp nhận bệnh nhân -----
        Route::get('reception', [ReceptionController::class, 'index'])->name('reception');

        Route::get('reception/index', function () {
            return redirect()->route('employees.reception');
        })->name('reception.index');

        Route::get('reception/queue', [ReceptionController::class, 'queue'])->name('reception.queue');

        Route::get('reception/{appointment}/ticket', [ReceptionController::class, 'printTicket'])
            ->whereNumber('appointment')
            ->name('reception.ticket');

        Route::post('reception/{appointment}/check-in', [ReceptionController::class, 'checkIn'])
            ->whereNumber('appointment')
            ->name('reception.check-in');

        Route::post('reception/walk-in', [ReceptionController::class, 'createWalkIn'])
            ->name('reception.walk-in');

        // ----- Hồ sơ bệnh nhân cho nhân viên/lễ tân -----
        Route::prefix('patient-profiles')->name('patient-profiles.')->group(function () {
            Route::get('/', [PatientProfileController::class, 'index'])->name('index');
            Route::get('search', [PatientProfileController::class, 'search'])->name('search');
            Route::post('quick-store', [PatientProfileController::class, 'storeQuick'])->name('quick-store');

            Route::put('{patientProfile}', [PatientProfileController::class, 'update'])
                ->whereNumber('patientProfile')
                ->name('update');
        });

        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [EmployeeScheduleController::class, 'create'])->name('create');
            Route::get('official', [EmployeeScheduleController::class, 'officialSchedule'])->name('official');

            Route::get('get-week-data', [EmployeeScheduleController::class, 'getWeekData'])->name('get-week-data');

            Route::get('official-schedule/get-week-data', [EmployeeScheduleController::class, 'getOfficialWeekData'])
                ->name('official-schedule.get-week-data');

            Route::post('/', [EmployeeScheduleController::class, 'store'])->name('store');
            Route::put('{scheduleRequest}', [EmployeeScheduleController::class, 'updateSchedule'])->name('update');
            Route::delete('{scheduleRequest}', [EmployeeScheduleController::class, 'cancel'])->name('cancel');

            Route::post('off-day', [EmployeeScheduleController::class, 'requestOffDay'])->name('off-day.store');
            Route::put('off-day/{offDay}', [EmployeeScheduleController::class, 'updateOffDay'])->name('off-day.update');
            Route::delete('off-day/{offDay}', [EmployeeScheduleController::class, 'cancelOffDay'])->name('off-day.cancel');

            Route::get('approved', function () {
                return redirect()->route('employees.schedule.create');
            })->name('approved');

            Route::get('off-days', function () {
                return redirect()->route('employees.schedule.create');
            })->name('off-days');
        });

        Route::get('appointment', function () {
            return redirect()->route('employees.reception.queue');
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

    // ==================== PATIENT PANEL ====================
    Route::prefix('patient')->name('patient.')->group(function () {
        Route::get('dashboard', function () {
            return view('patient.dashboard');
        })->name('dashboard');

        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointment.list');
        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointment.create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('appointment.store');

        Route::get('appointments/{id}', [AppointmentController::class, 'show'])
            ->whereNumber('id')
            ->name('appointment.show');

        Route::post('appointments/{id}/cancel', [AppointmentController::class, 'cancel'])
            ->whereNumber('id')
            ->name('appointment.cancel');

        Route::get('appointment', function () {
            return redirect()->route('patient.appointment.list');
        });

        Route::get('appointment/create', function () {
            return redirect()->route('patient.appointment.create');
        });

        Route::get('appointment/{id}', function ($id) {
            return redirect()->route('patient.appointment.show', $id);
        })->whereNumber('id');

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