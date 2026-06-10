<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    private const SLOT_DURATION = 30;
    private const LUNCH_START_MINUTES = 690; // 11:30
    private const LUNCH_END_MINUTES = 750;   // 12:30

    private const ACTIVE_EMPLOYEE_STATUSES = ['active', 'Hoạt động'];

    private const BLOCKING_APPOINTMENT_STATUSES = [
        'pending',
        'confirmed',
        'checked_in',
        'waiting',
        'in_progress',
    ];

    public function getAvailableDoctorsByService($serviceId, $date)
    {
        try {
            if (!$this->isOnlineBookingDateAllowed($date)) {
                return collect();
            }

            $service = Service::findOrFail($serviceId);

            if (empty($service->required_specialization)) {
                Log::warning("Service {$serviceId} has no required_specialization assigned");
                return collect();
            }

            $durationMinutes = $this->getServiceDurationMinutes($service);
            $slotsNeeded = $this->getSlotsNeeded($durationMinutes);
            $availableDoctors = [];

            foreach ($this->getEligibleDoctorsForService($service) as $doctor) {
                if (!$this->hasAnyAvailablePeriodForDoctor($doctor->id, $date, $durationMinutes)) {
                    continue;
                }

                $availableDoctors[] = [
                    'doctor' => $doctor,
                    'available_slots' => $this->getAvailableSlotsForDoctor($doctor->id, $date),
                    'slots_needed' => $slotsNeeded,
                    'load_minutes' => $this->getDoctorOnlineLoadMinutesForDate($doctor->id, $date),
                    'appointment_count' => $this->getDoctorOnlineAppointmentCountForDate($doctor->id, $date),
                ];
            }

            return collect($availableDoctors)
                ->sortBy([
                    ['load_minutes', 'asc'],
                    ['appointment_count', 'asc'],
                    fn ($a, $b) => strcmp($a['doctor']->name ?? '', $b['doctor']->name ?? ''),
                ])
                ->values();
        } catch (\Exception $e) {
            Log::error('Error in getAvailableDoctorsByService: ' . $e->getMessage());
            return collect();
        }
    }

    public function getAvailableTimesByService($serviceId, $date): array
    {
        try {
            if (!$this->isOnlineBookingDateAllowed($date)) {
                return [];
            }

            $service = Service::findOrFail($serviceId);

            if (empty($service->required_specialization)) {
                return [];
            }

            $durationMinutes = $this->getServiceDurationMinutes($service);
            $availableTimes = [];

            foreach ($this->getEligibleDoctorsForService($service) as $doctor) {
                $workSchedules = $this->getApprovedWorkSchedulesForDoctor($doctor->id, $date);

                foreach ($workSchedules as $schedule) {
                    [$shiftStart, $shiftEnd] = $this->getScheduleStartEndMinutes($schedule);

                    if ($shiftEnd <= $shiftStart) {
                        continue;
                    }

                    for ($minute = $shiftStart; $minute + $durationMinutes <= $shiftEnd; $minute += self::SLOT_DURATION) {
                        $startTime = $this->minutesToTime($minute);
                        $endTime = $this->minutesToTime($minute + $durationMinutes);

                        if ($this->overlapsLunchBreak($minute, $minute + $durationMinutes)) {
                            continue;
                        }

                        if (!$this->isDoctorAvailableForPeriod($doctor->id, $date, $startTime, $durationMinutes)) {
                            continue;
                        }

                        if (!isset($availableTimes[$startTime])) {
                            $availableTimes[$startTime] = [
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'doctor_count' => 0,
                                'doctor_ids' => [],
                            ];
                        }

                        if (!in_array($doctor->id, $availableTimes[$startTime]['doctor_ids'], true)) {
                            $availableTimes[$startTime]['doctor_ids'][] = $doctor->id;
                            $availableTimes[$startTime]['doctor_count']++;
                        }
                    }
                }
            }

            return collect($availableTimes)
                ->sortBy('start_time')
                ->map(function ($slot) {
                    unset($slot['doctor_ids']);
                    return $slot;
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            Log::error('Error in getAvailableTimesByService: ' . $e->getMessage());
            return [];
        }
    }

    public function getAvailableDoctorsByServiceAndTime($serviceId, $date, $startTime): array
    {
        try {
            if (!$this->isOnlineBookingDateAllowed($date)) {
                return [];
            }

            $service = Service::findOrFail($serviceId);

            if (empty($service->required_specialization)) {
                return [];
            }

            $durationMinutes = $this->getServiceDurationMinutes($service);

            return $this->getEligibleDoctorsForService($service)
                ->filter(function ($doctor) use ($date, $startTime, $durationMinutes) {
                    return $this->isDoctorAvailableForPeriod($doctor->id, $date, $startTime, $durationMinutes);
                })
                ->map(function ($doctor) use ($date) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialization' => $doctor->specialization ?? 'N/A',
                        'phone' => $doctor->phone ?? '',
                        'email' => $doctor->email ?? '',
                        'load_minutes' => $this->getDoctorOnlineLoadMinutesForDate($doctor->id, $date),
                        'appointment_count' => $this->getDoctorOnlineAppointmentCountForDate($doctor->id, $date),
                    ];
                })
                ->sortBy([
                    ['load_minutes', 'asc'],
                    ['appointment_count', 'asc'],
                    ['name', 'asc'],
                ])
                ->values()
                ->all();
        } catch (\Exception $e) {
            Log::error('Error in getAvailableDoctorsByServiceAndTime: ' . $e->getMessage());
            return [];
        }
    }

    public function hasDoctorShiftOnDate($doctorId, $date): bool
    {
        return $this->getApprovedWorkSchedulesForDoctor($doctorId, $date)->isNotEmpty();
    }

    public function getAvailableSlotsForDoctor($doctorId, $date): array
    {
        if (!$this->isOnlineBookingDateAllowed($date)) {
            return [];
        }

        $workSchedules = $this->getApprovedWorkSchedulesForDoctor($doctorId, $date);

        if ($workSchedules->isEmpty()) {
            return [];
        }

        $allSlots = [];

        foreach ($workSchedules as $schedule) {
            [$workStart, $workEnd] = $this->getScheduleStartEndMinutes($schedule);

            if ($workEnd <= $workStart) {
                continue;
            }

            for ($time = $workStart; $time + self::SLOT_DURATION <= $workEnd; $time += self::SLOT_DURATION) {
                if ($this->overlapsLunchBreak($time, $time + self::SLOT_DURATION)) {
                    continue;
                }

                $allSlots[] = $time;
            }
        }

        $allSlots = collect($allSlots)->unique()->sort()->values()->all();

        $availableSlots = [];

        foreach ($allSlots as $index => $slotStart) {
            $slotEnd = $slotStart + self::SLOT_DURATION;

            if ($this->hasAppointmentOverlap($doctorId, $date, $slotStart, $slotEnd)) {
                continue;
            }

            $availableSlots[] = [
                'start_time' => $this->minutesToTime($slotStart),
                'end_time' => $this->minutesToTime($slotEnd),
                'slot_index' => $index,
            ];
        }

        return $availableSlots;
    }

    public function checkDoctorAvailability($doctorId, $date, $startTime, $slotsNeeded): bool
    {
        $durationMinutes = max((int) $slotsNeeded, 1) * self::SLOT_DURATION;

        return $this->isDoctorAvailableForPeriod($doctorId, $date, $startTime, $durationMinutes);
    }

    public function createAppointment($data)
    {
        try {
            $service = Service::find($data['service_id']);

            if (!$service) {
                throw new \Exception('Dịch vụ không tồn tại');
            }

            if (empty($service->required_specialization)) {
                throw new \Exception('Dịch vụ chưa được gán chuyên khoa. Vui lòng liên hệ quản trị viên.');
            }

            $doctor = Employee::where('id', $data['doctor_id'])
                ->where('is_doctor', 1)
                ->whereIn('status', self::ACTIVE_EMPLOYEE_STATUSES)
                ->first();

            if (!$doctor) {
                throw new \Exception('Bác sĩ không tồn tại hoặc không còn hoạt động');
            }

            if (trim((string) $doctor->specialization) !== trim((string) $service->required_specialization)) {
                throw new \Exception('Bác sĩ không đúng chuyên khoa của dịch vụ đã chọn');
            }

            $appointmentDate = Carbon::parse($data['appointment_date']);
            $date = $appointmentDate->format('Y-m-d');
            $time = $appointmentDate->format('H:i');
            $durationMinutes = $this->getServiceDurationMinutes($service);
            $slotsNeeded = $this->getSlotsNeeded($durationMinutes);

            if (!$this->isOnlineBookingDateAllowed($date)) {
                throw new \Exception('Lịch online cần được đặt trước ít nhất 1 ngày. Vui lòng chọn ngày từ ngày mai trở đi.');
            }

            $startMinutes = $this->timeToMinutes($time);
            $endMinutes = $startMinutes + $durationMinutes;

            if ($this->overlapsLunchBreak($startMinutes, $endMinutes)) {
                throw new \Exception('Phòng khám không nhận lịch online trong khoảng 11:30 - 12:30. Vui lòng chọn khung giờ khác.');
            }

            if (!$this->hasDoctorShiftOnDate($data['doctor_id'], $date)) {
                throw new \Exception('Bác sĩ không có lịch làm việc vào ngày này');
            }

            if (!$this->isDoctorAvailableForPeriod($data['doctor_id'], $date, $time, $durationMinutes)) {
                throw new \Exception('Bác sĩ không rảnh hoặc không đủ thời lượng liên tục trong khoảng thời gian này');
            }

            $appointment = Appointment::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'service_id' => $data['service_id'],
                'appointment_date' => $data['appointment_date'],
                'slots_used' => $slotsNeeded,
                'duration_minutes' => $durationMinutes,
                'source' => 'online',
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            Log::info("Appointment created: ID={$appointment->id}, Patient={$data['patient_id']}, Doctor={$data['doctor_id']}");

            return $appointment;
        } catch (\Exception $e) {
            Log::error('Error creating appointment: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getUpcomingAppointments($patientId)
    {
        return Appointment::where('patient_id', $patientId)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', '>=', now())
            ->orderBy('appointment_date', 'asc')
            ->with(['doctor', 'service', 'room'])
            ->get();
    }

    public function cancelAppointment($appointmentId)
    {
        try {
            $appointment = Appointment::findOrFail($appointmentId);

            if ($appointment->appointment_date->diffInHours(now()) < 24) {
                throw new \Exception('Không thể hủy lịch hẹn ít hơn 24 tiếng trước');
            }

            $appointment->update(['status' => 'cancelled']);

            Log::info("Appointment {$appointmentId} cancelled by patient {$appointment->patient_id}");

            return true;
        } catch (\Exception $e) {
            Log::error('Error cancelling appointment: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getEligibleDoctorsForService(Service $service)
    {
        $requiredSpecialization = trim((string) $service->required_specialization);

        return Employee::where('is_doctor', 1)
            ->whereIn('status', self::ACTIVE_EMPLOYEE_STATUSES)
            ->whereNotNull('specialization')
            ->orderBy('name', 'asc')
            ->get()
            ->filter(function ($doctor) use ($requiredSpecialization) {
                return trim((string) $doctor->specialization) === $requiredSpecialization;
            })
            ->values();
    }

    private function getServiceDurationMinutes(Service $service): int
    {
        $duration = (int) ($service->actual_duration ?? 0);

        if ($duration <= 0) {
            $duration = (int) ($service->duration_minutes ?? 0);
        }

        if ($duration <= 0) {
            $duration = max((int) ($service->slots_required ?? 1), 1) * self::SLOT_DURATION;
        }

        return max($duration, self::SLOT_DURATION);
    }

    private function getSlotsNeeded(int $durationMinutes): int
    {
        return (int) ceil($durationMinutes / self::SLOT_DURATION);
    }

    private function hasAnyAvailablePeriodForDoctor($doctorId, $date, int $durationMinutes): bool
    {
        $workSchedules = $this->getApprovedWorkSchedulesForDoctor($doctorId, $date);

        foreach ($workSchedules as $schedule) {
            [$shiftStart, $shiftEnd] = $this->getScheduleStartEndMinutes($schedule);

            if ($shiftEnd <= $shiftStart) {
                continue;
            }

            for ($minute = $shiftStart; $minute + $durationMinutes <= $shiftEnd; $minute += self::SLOT_DURATION) {
                if ($this->overlapsLunchBreak($minute, $minute + $durationMinutes)) {
                    continue;
                }

                if ($this->isDoctorAvailableForPeriod($doctorId, $date, $this->minutesToTime($minute), $durationMinutes)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isDoctorAvailableForPeriod($doctorId, $date, string $startTime, int $durationMinutes): bool
    {
        if (!$this->isOnlineBookingDateAllowed($date)) {
            return false;
        }

        $startMinutes = $this->timeToMinutes($startTime);
        $endMinutes = $startMinutes + $durationMinutes;

        if ($this->overlapsLunchBreak($startMinutes, $endMinutes)) {
            return false;
        }

        if (!$this->isInsideApprovedWorkSchedule($doctorId, $date, $startMinutes, $endMinutes)) {
            return false;
        }

        return !$this->hasAppointmentOverlap($doctorId, $date, $startMinutes, $endMinutes);
    }

    private function isInsideApprovedWorkSchedule($doctorId, $date, int $startMinutes, int $endMinutes): bool
    {
        $workSchedules = $this->getApprovedWorkSchedulesForDoctor($doctorId, $date);

        foreach ($workSchedules as $schedule) {
            [$shiftStart, $shiftEnd] = $this->getScheduleStartEndMinutes($schedule);

            if ($shiftStart <= $startMinutes && $shiftEnd >= $endMinutes) {
                return true;
            }
        }

        return false;
    }

    private function getApprovedWorkSchedulesForDoctor($doctorId, $date)
    {
        return ShiftAssignment::with('shift')
            ->where('employee_id', $doctorId)
            ->whereDate('work_date', $date)
            ->where('assignment_type', 'work')
            ->where('status', 'approved')
            ->orderBy('start_hour')
            ->orderBy('start_minute')
            ->get();
    }

    private function getScheduleStartEndMinutes($schedule): array
    {
        $startHour = $schedule->start_hour;
        $startMinute = $schedule->start_minute;
        $endHour = $schedule->end_hour;
        $endMinute = $schedule->end_minute;

        if ($startHour === null && $schedule->shift) {
            $startHour = $schedule->shift->start_hour;
            $startMinute = $schedule->shift->start_minute ?? 0;
        }

        if ($endHour === null && $schedule->shift) {
            $endHour = $schedule->shift->end_hour;
            $endMinute = $schedule->shift->end_minute ?? 0;
        }

        $start = ((int) $startHour * 60) + (int) ($startMinute ?? 0);
        $end = ((int) $endHour * 60) + (int) ($endMinute ?? 0);

        return [$start, $end];
    }

    private function hasAppointmentOverlap($doctorId, $date, int $startMinutes, int $endMinutes): bool
    {
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', self::BLOCKING_APPOINTMENT_STATUSES)
            ->get();

        foreach ($appointments as $appointment) {
            $appointmentStart = ($appointment->appointment_date->hour * 60) + $appointment->appointment_date->minute;
            $appointmentDuration = (int) ($appointment->duration_minutes ?? self::SLOT_DURATION);
            $appointmentEnd = $appointmentStart + $appointmentDuration;

            if ($appointmentStart < $endMinutes && $appointmentEnd > $startMinutes) {
                return true;
            }
        }

        return false;
    }

    private function getDoctorOnlineLoadMinutesForDate($doctorId, $date): int
    {
        return (int) Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('source', 'online')
            ->whereIn('status', self::BLOCKING_APPOINTMENT_STATUSES)
            ->get()
            ->sum(function ($appointment) {
                return (int) ($appointment->duration_minutes ?? self::SLOT_DURATION);
            });
    }

    private function getDoctorOnlineAppointmentCountForDate($doctorId, $date): int
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('source', 'online')
            ->whereIn('status', self::BLOCKING_APPOINTMENT_STATUSES)
            ->count();
    }

    private function isOnlineBookingDateAllowed($date): bool
    {
        $bookingDate = Carbon::parse($date)->startOfDay();
        $minimumDate = now()->copy()->addDay()->startOfDay();

        return $bookingDate->greaterThanOrEqualTo($minimumDate);
    }

    private function overlapsLunchBreak(int $startMinutes, int $endMinutes): bool
    {
        return $startMinutes < self::LUNCH_END_MINUTES
            && $endMinutes > self::LUNCH_START_MINUTES;
    }

    private function timeToMinutes(string $time): int
    {
        [$hour, $minute] = explode(':', $time);

        return ((int) $hour * 60) + (int) $minute;
    }

    private function minutesToTime(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return str_pad($hours, 2, '0', STR_PAD_LEFT)
            . ':'
            . str_pad($mins, 2, '0', STR_PAD_LEFT);
    }
}