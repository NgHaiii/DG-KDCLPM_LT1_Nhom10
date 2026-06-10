<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorAppointmentController extends Controller
{
    private const BLOCKING_ROOM_STATUSES = [
        'confirmed',
        'checked_in',
        'waiting',
        'in_progress',
    ];

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
            ->with(['patient', 'service.room', 'room'])
            ->orderBy('appointment_date', 'asc')
            ->get();

        $confirmedAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'confirmed')
            ->where('appointment_date', '>=', now())
            ->with(['patient', 'service.room', 'room'])
            ->orderBy('appointment_date', 'asc')
            ->get();

        $recentAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where(function ($query) {
                $query->where('appointment_date', '<', now())
                    ->orWhereIn('status', ['cancelled', 'completed']);
            })
            ->with(['patient', 'service.room', 'room'])
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

        $appointment->loadMissing(['service.room', 'room']);

        if ($appointment->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể xác nhận lịch đang chờ xác nhận.');
        }

        if ($appointment->appointment_date->isPast()) {
            return back()->with('error', 'Không thể xác nhận lịch hẹn đã qua.');
        }

        if (!$appointment->service) {
            return back()->with('error', 'Lịch hẹn chưa có dịch vụ hợp lệ.');
        }

        if (!$appointment->service->room_id) {
            return back()->with('error', 'Dịch vụ này chưa được gán phòng khám. Vui lòng gán phòng cho dịch vụ trước khi xác nhận.');
        }

        $room = $this->findAvailableRoomForAppointment($appointment);

        if (!$room) {
            return back()->with('error', 'Không còn phòng khám phù hợp trong khung giờ này. Vui lòng đổi phòng dịch vụ hoặc hẹn bệnh nhân sang khung giờ khác.');
        }

        $appointment->update([
            'status' => 'confirmed',
            'room_id' => $room->id,
            'confirmed_at' => now(),
        ]);

        Log::info('Doctor confirmed online appointment', [
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'room_id' => $room->id,
        ]);

        return back()->with('success', 'Đã xác nhận lịch hẹn và xếp phòng khám ' . $room->name . ' thành công.');
    }

    public function cancelOnlineAppointment(Request $request, Appointment $appointment)
    {
        $doctor = $this->getCurrentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if (in_array($appointment->status, ['cancelled', 'completed', 'checked_in', 'waiting', 'in_progress'], true)) {
            return back()->with('error', 'Không thể hủy lịch hẹn này.');
        }

        $reason = trim((string) $request->input('cancel_reason', ''));
        $oldNotes = trim((string) $appointment->notes);
        $cancelNote = 'Bác sĩ hủy lịch' . ($reason !== '' ? ': ' . $reason : '.');

        $appointment->update([
            'status' => 'cancelled',
            'notes' => trim($oldNotes . "\n" . $cancelNote),
        ]);

        Log::info('Doctor cancelled online appointment', [
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
        ]);

        return back()->with('success', 'Đã hủy lịch hẹn thành công.');
    }

    private function findAvailableRoomForAppointment(Appointment $appointment): ?Room
    {
        $appointment->loadMissing('service');

        $service = $appointment->service;

        if (!$service) {
            return null;
        }

        $durationMinutes = (int) ($appointment->duration_minutes ?? $service->actual_duration ?? 30);
        $appointmentStart = Carbon::parse($appointment->appointment_date);

        $candidateRooms = Room::query()
            ->where('is_active', true)
            ->where('base_status', 'available')
            ->where('type', $service->type)
            ->orderByRaw('id = ? DESC', [$service->room_id])
            ->orderBy('name', 'asc')
            ->get();

        foreach ($candidateRooms as $room) {
            if ($this->isRoomAvailable(
                $room->id,
                $appointmentStart,
                $durationMinutes,
                $appointment->id
            )) {
                return $room;
            }
        }

        return null;
    }

    private function isRoomAvailable($roomId, Carbon $startTime, int $durationMinutes, $ignoreAppointmentId = null): bool
    {
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        $query = Appointment::where('room_id', $roomId)
            ->whereDate('appointment_date', $startTime->toDateString())
            ->whereIn('status', self::BLOCKING_ROOM_STATUSES);

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        $appointments = $query->get();

        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->appointment_date);
            $appointmentDuration = (int) ($appointment->duration_minutes ?? 30);
            $appointmentEnd = $appointmentStart->copy()->addMinutes($appointmentDuration);

            if ($appointmentStart < $endTime && $appointmentEnd > $startTime) {
                return false;
            }
        }

        return true;
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