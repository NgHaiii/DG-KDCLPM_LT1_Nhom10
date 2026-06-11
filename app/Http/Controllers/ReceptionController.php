<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceptionController extends Controller
{
    private const ACTIVE_STATUSES = ['confirmed', 'checked_in', 'waiting', 'in_progress'];
    private const QUEUE_STATUSES = ['checked_in', 'waiting', 'in_progress'];
    private const BUFFER_MINUTES = 10;

    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $todayAppointments = Appointment::with(['patient', 'doctor', 'service', 'room'])
            ->whereDate('appointment_date', $date)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->orderByRaw("FIELD(status, 'confirmed', 'waiting', 'checked_in', 'in_progress')")
            ->orderBy('appointment_date')
            ->get();

        $completedAppointments = Appointment::with(['patient', 'doctor', 'service', 'room'])
            ->where('status', 'completed')
            ->where(function ($query) use ($date) {
                $query->whereDate('completed_at', $date)
                    ->orWhereDate('appointment_date', $date);
            })
            ->orderByDesc('completed_at')
            ->get();

        $services = Service::with('room')
            ->where('is_active', 1)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $patients = User::orderBy('name')->get();

        $doctors = Employee::where('is_doctor', 1)
            ->whereIn('status', ['active', 'Hoạt động'])
            ->orderBy('name')
            ->get();

        return view('employees.reception.index', compact(
            'date',
            'todayAppointments',
            'completedAppointments',
            'services',
            'patients',
            'doctors'
        ));
    }

    public function queue(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $todayAppointments = Appointment::with(['patient', 'doctor', 'service', 'room'])
            ->where(function ($query) use ($date) {
                $query->whereDate('appointment_date', $date)
                    ->orWhereDate('checked_in_at', $date);
            })
            ->whereIn('status', self::QUEUE_STATUSES)
            ->orderByRaw("FIELD(status, 'in_progress', 'waiting', 'checked_in')")
            ->orderByRaw('CASE WHEN queue_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('queue_number')
            ->orderBy('appointment_date')
            ->get();

        $completedAppointments = Appointment::with(['patient', 'doctor', 'service', 'room'])
            ->where('status', 'completed')
            ->where(function ($query) use ($date) {
                $query->whereDate('completed_at', $date)
                    ->orWhereDate('appointment_date', $date);
            })
            ->orderByDesc('completed_at')
            ->get();

        return view('employees.reception.queue', compact(
            'date',
            'todayAppointments',
            'completedAppointments'
        ));
    }

    public function checkIn(Appointment $appointment)
    {
        if ($appointment->status !== 'confirmed') {
            return back()->with('error', 'Chỉ có thể tiếp nhận lịch đã được xác nhận.');
        }

        if (!$appointment->appointment_date->isSameDay(now())) {
            return back()->with('error', 'Chỉ được tiếp nhận lịch khám trong ngày hôm nay.');
        }

        $appointment->update([
            'status' => 'waiting',
            'checked_in_at' => now(),
            'queue_number' => $appointment->queue_number ?: $this->nextQueueNumber(),
        ]);

        return redirect()
            ->route('employees.reception.ticket', $appointment->id)
            ->with('success', 'Đã tiếp nhận bệnh nhân. Vui lòng in phiếu số thứ tự.');
    }

    public function createWalkIn(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $service = Service::with('room')->findOrFail($validated['service_id']);
        $doctor = Employee::findOrFail($validated['doctor_id']);

        if ((int) $doctor->is_doctor !== 1) {
            return back()->withInput()->with('error', 'Nhân sự được chọn không phải bác sĩ.');
        }

        if ($service->required_specialization && trim((string) $doctor->specialization) !== trim((string) $service->required_specialization)) {
            return back()->withInput()->with('error', 'Bác sĩ không đúng chuyên khoa của dịch vụ.');
        }

        $duration = $this->serviceDuration($service);
        $now = now()->seconds(0);

        $acceptance = $this->canAcceptWalkIn($doctor->id, $now, $duration);

        if (!$acceptance['allowed']) {
            return back()->withInput()->with('error', $acceptance['message']);
        }

        $estimatedStart = $acceptance['estimated_start'] ?? $now;
        $estimatedEnd = $acceptance['estimated_end'] ?? $estimatedStart->copy()->addMinutes($duration + self::BUFFER_MINUTES);

        $appointment = DB::transaction(function () use ($validated, $service, $duration, $estimatedStart, $estimatedEnd) {
            return Appointment::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'room_id' => $service->room_id,
                'source' => 'offline',
                'appointment_date' => $estimatedStart,
                'slots_used' => (int) ceil($duration / 30),
                'duration_minutes' => $duration,
                'status' => 'waiting',
                'checked_in_at' => now(),
                'estimated_end_at' => $estimatedEnd,
                'queue_number' => $this->nextQueueNumber(),
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('employees.reception.ticket', $appointment->id)
            ->with('success', 'Đã tiếp nhận bệnh nhân offline. Vui lòng in phiếu số thứ tự.');
    }

    public function printTicket(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'service', 'room']);

        if (!in_array($appointment->status, ['checked_in', 'waiting', 'in_progress', 'completed'], true)) {
            return redirect()
                ->route('employees.reception')
                ->with('error', 'Lịch khám này chưa được tiếp nhận nên chưa thể in phiếu số thứ tự.');
        }

        if (!$appointment->queue_number) {
            $appointment->update([
                'queue_number' => $this->nextQueueNumber(),
            ]);

            $appointment->refresh();
            $appointment->load(['patient', 'doctor', 'service', 'room']);
        }

        return view('employees.reception.ticket', compact('appointment'));
    }

    private function nextQueueNumber(): int
    {
        return ((int) Appointment::whereDate('checked_in_at', now()->toDateString())->max('queue_number')) + 1;
    }

    private function serviceDuration(Service $service): int
    {
        if ((int) $service->actual_duration > 0) {
            return (int) $service->actual_duration;
        }

        if ((int) $service->duration_minutes > 0) {
            return (int) $service->duration_minutes;
        }

        return max((int) $service->slots_required, 1) * 30;
    }

    private function canAcceptWalkIn($doctorId, Carbon $requestTime, int $newDuration): array
    {
        $workPeriod = $this->getDoctorCurrentWorkPeriod($doctorId, $requestTime);

        if (!$workPeriod) {
            return [
                'allowed' => false,
                'message' => 'Bác sĩ không có ca làm việc tại thời điểm hiện tại.',
            ];
        }

        $estimatedStart = $this->estimateOfflineStartTime($doctorId, $requestTime);
        $estimatedEnd = $estimatedStart->copy()->addMinutes($newDuration + self::BUFFER_MINUTES);

        if ($estimatedEnd->greaterThan($workPeriod['end'])) {
            return [
                'allowed' => false,
                'message' => 'Không thể tiếp nhận vì dự kiến hoàn thành lúc '
                    . $estimatedEnd->format('H:i')
                    . ', vượt quá ca làm việc của bác sĩ lúc '
                    . $workPeriod['end']->format('H:i')
                    . '.',
            ];
        }

        $nextOnlineAppointment = $this->getNextOnlineAppointment($doctorId, $requestTime);

        if ($nextOnlineAppointment && $estimatedEnd->greaterThan($nextOnlineAppointment->appointment_date)) {
            return [
                'allowed' => false,
                'message' => 'Không thể tiếp nhận vì dự kiến hoàn thành lúc '
                    . $estimatedEnd->format('H:i')
                    . ', có nguy cơ lấn lịch online lúc '
                    . $nextOnlineAppointment->appointment_date->format('H:i')
                    . '.',
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Có thể tiếp nhận bệnh nhân.',
            'estimated_start' => $estimatedStart,
            'estimated_end' => $estimatedEnd,
        ];
    }

    private function estimateOfflineStartTime($doctorId, Carbon $requestTime): Carbon
    {
        $estimatedStart = $requestTime->copy();

        $inProgress = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $requestTime->toDateString())
            ->where('status', 'in_progress')
            ->orderBy('started_at')
            ->first();

        if ($inProgress) {
            $startedAt = $inProgress->started_at ?? $inProgress->appointment_date;
            $inProgressEstimatedEnd = $startedAt->copy()->addMinutes((int) ($inProgress->duration_minutes ?? 30));

            if ($inProgressEstimatedEnd->greaterThan($estimatedStart)) {
                $estimatedStart = $inProgressEstimatedEnd;
            }
        }

        $waitingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $requestTime->toDateString())
            ->where('status', 'waiting')
            ->orderByRaw('CASE WHEN queue_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('queue_number')
            ->orderBy('checked_in_at')
            ->get();

        foreach ($waitingAppointments as $waitingAppointment) {
            $estimatedStart = $estimatedStart->copy()->addMinutes((int) ($waitingAppointment->duration_minutes ?? 30));
        }

        return $estimatedStart;
    }

    private function getNextOnlineAppointment($doctorId, Carbon $requestTime): ?Appointment
    {
        return Appointment::where('doctor_id', $doctorId)
            ->where('source', 'online')
            ->whereDate('appointment_date', $requestTime->toDateString())
            ->whereIn('status', ['confirmed', 'checked_in', 'waiting', 'in_progress'])
            ->where('appointment_date', '>=', $requestTime)
            ->orderBy('appointment_date')
            ->first();
    }

    private function getDoctorCurrentWorkPeriod($doctorId, Carbon $time): ?array
    {
        $minute = ($time->hour * 60) + $time->minute;

        $workSchedule = DB::table('shift_assignments')
            ->where('employee_id', $doctorId)
            ->whereDate('work_date', $time->toDateString())
            ->where('assignment_type', 'work')
            ->where('status', 'approved')
            ->whereRaw('((start_hour * 60 + start_minute) <= ?)', [$minute])
            ->whereRaw('((end_hour * 60 + end_minute) >= ?)', [$minute])
            ->orderByDesc('end_hour')
            ->orderByDesc('end_minute')
            ->first();

        if (!$workSchedule) {
            return null;
        }

        return [
            'start' => $time->copy()->setTime(
                (int) $workSchedule->start_hour,
                (int) ($workSchedule->start_minute ?? 0),
                0
            ),
            'end' => $time->copy()->setTime(
                (int) $workSchedule->end_hour,
                (int) ($workSchedule->end_minute ?? 0),
                0
            ),
        ];
    }
}