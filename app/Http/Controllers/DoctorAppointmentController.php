<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorAppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function onlineAppointments()
    {
        $doctor = $this->getCurrentDoctor();

        $pendingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'pending')
            ->where('appointment_date', '>=', now())
            ->with(['patient', 'service'])
            ->orderBy('appointment_date', 'asc')
            ->get();

        $confirmedAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'confirmed')
            ->where('appointment_date', '>=', now())
            ->with(['patient', 'service'])
            ->orderBy('appointment_date', 'asc')
            ->get();

        $recentAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where(function ($query) {
                $query->where('appointment_date', '<', now())
                    ->orWhereIn('status', ['cancelled', 'completed']);
            })
            ->with(['patient', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->limit(20)
            ->get();

        return view('doctor.appointments.online', compact(
            'pendingAppointments',
            'confirmedAppointments',
            'recentAppointments'
        ));
    }

    public function confirmOnlineAppointment(Appointment $appointment)
    {
        $doctor = $this->getCurrentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể xác nhận lịch đang chờ xác nhận.');
        }

        if ($appointment->appointment_date->isPast()) {
            return back()->with('error', 'Không thể xác nhận lịch hẹn đã qua.');
        }

        $appointment->update([
            'status' => 'confirmed',
        ]);

        Log::info("Doctor {$doctor->id} confirmed appointment {$appointment->id}");

        return back()->with('success', 'Đã xác nhận lịch hẹn thành công.');
    }

    public function cancelOnlineAppointment(Request $request, Appointment $appointment)
    {
        $doctor = $this->getCurrentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if (in_array($appointment->status, ['cancelled', 'completed'])) {
            return back()->with('error', 'Không thể hủy lịch hẹn này.');
        }

        $reason = trim((string) $request->input('cancel_reason', ''));
        $oldNotes = trim((string) $appointment->notes);
        $cancelNote = 'Bác sĩ hủy lịch' . ($reason !== '' ? ': ' . $reason : '.');

        $appointment->update([
            'status' => 'cancelled',
            'notes' => trim($oldNotes . "\n" . $cancelNote),
        ]);

        Log::info("Doctor {$doctor->id} cancelled appointment {$appointment->id}");

        return back()->with('success', 'Đã hủy lịch hẹn thành công.');
    }

    private function getCurrentDoctor(): Employee
    {
        $doctor = Employee::where('user_id', Auth::id())
            ->where('is_doctor', 1)
            ->first();

        if (!$doctor) {
            abort(403, 'Tài khoản hiện tại không phải bác sĩ.');
        }

        return $doctor;
    }

    private function authorizeDoctorAppointment(Appointment $appointment, Employee $doctor): void
    {
        if ((int) $appointment->doctor_id !== (int) $doctor->id) {
            abort(403, 'Bạn không có quyền xử lý lịch hẹn này.');
        }
    }
}