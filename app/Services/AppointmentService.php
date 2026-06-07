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
    private const ACTIVE_EMPLOYEE_STATUSES = ['active', 'Hoạt động'];

    public function getAvailableDoctorsByService($serviceId, $date)
    {
        try {
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
                ];
            }

            return collect($availableDoctors);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableDoctorsByService: ' . $e->getMessage());
            return collect();
        }
    }

    public function getAvailableTimesByService($serviceId, $date): array
    {
        try {
            $service = Service::findOrFail($serviceId);

            if (empty($service->required_specialization)) {
                return [];
            }

            $durationMinutes = $this->getServiceDurationMinutes($service);
            $availableTimes = [];

            foreach ($this->getEligibleDoctorsForService($service) as $doctor) {
                $shifts = ShiftAssignment::where('employee_id', $doctor->id)
                    ->whereDate('work_date', $date)
                    ->where('status', 'approved')
                    ->orderBy('start_hour')
                    ->orderBy('start_minute')
                    ->get();

                foreach ($shifts as $shift) {
                    $shiftStart = ((int) $shift->start_hour * 60) + (int) $shift->start_minute;
                    $shiftEnd = ((int) $shift->end_hour * 60) + (int) $shift->end_minute;

                    if ($shiftEnd <= $shiftStart) {
                        continue;
                    }

                    for ($minute = $shiftStart; $minute + $durationMinutes <= $shiftEnd; $minute += self::SLOT_DURATION) {
                        $startTime = $this->minutesToTime($minute);
                        $endTime = $this->minutesToTime($minute + $durationMinutes);

                        if (!$this->isDoctorAvailableForPeriod($doctor->id, $date, $startTime, $durationMinutes)) {
                            continue;
                        }

                        if (!isset($availableTimes[$startTime])) {
                            $availableTimes[$startTime] = [
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'doctor_count' => 0,
                            ];
                        }

                        $availableTimes[$startTime]['doctor_count']++;
                    }
                }
            }

            return collect($availableTimes)
                ->sortBy('start_time')
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
            $service = Service::findOrFail($serviceId);

            if (empty($service->required_specialization)) {
                return [];
            }

            $durationMinutes = $this->getServiceDurationMinutes($service);

            return $this->getEligibleDoctorsForService($service)
                ->filter(function ($doctor) use ($date, $startTime, $durationMinutes) {
                    return $this->isDoctorAvailableForPeriod($doctor->id, $date, $startTime, $durationMinutes);
                })
                ->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialization' => $doctor->specialization ?? 'N/A',
                        'phone' => $doctor->phone ?? '',
                        'email' => $doctor->email ?? '',
                    ];
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            Log::error('Error in getAvailableDoctorsByServiceAndTime: ' . $e->getMessage());
            return [];
        }
    }

    public function hasDoctorShiftOnDate($doctorId, $date): bool
    {
        return ShiftAssignment::where('employee_id', $doctorId)
            ->whereDate('work_date', $date)
            ->where('status', 'approved')
            ->exists();
    }

    public function getAvailableSlotsForDoctor($doctorId, $date): array
    {
        $shifts = ShiftAssignment::where('employee_id', $doctorId)
            ->whereDate('work_date', $date)
            ->where('status', 'approved')
            ->orderBy('start_hour')
            ->orderBy('start_minute')
            ->get();

        if ($shifts->isEmpty()) {
            return [];
        }

        $allSlots = [];

        foreach ($shifts as $shift) {
            $workStart = ((int) $shift->start_hour * 60) + (int) $shift->start_minute;
            $workEnd = ((int) $shift->end_hour * 60) + (int) $shift->end_minute;

            if ($workEnd <= $workStart) {
                continue;
            }

            for ($time = $workStart; $time + self::SLOT_DURATION <= $workEnd; $time += self::SLOT_DURATION) {
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

            $date = Carbon::parse($data['appointment_date'])->format('Y-m-d');
            $time = Carbon::parse($data['appointment_date'])->format('H:i');
            $durationMinutes = $this->getServiceDurationMinutes($service);
            $slotsNeeded = $this->getSlotsNeeded($durationMinutes);

            if (!$this->hasDoctorShiftOnDate($data['doctor_id'], $date)) {
                throw new \Exception('Bác sĩ không có ca trực vào ngày này');
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
            ->with('doctor', 'service')
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
        $shifts = ShiftAssignment::where('employee_id', $doctorId)
            ->whereDate('work_date', $date)
            ->where('status', 'approved')
            ->orderBy('start_hour')
            ->orderBy('start_minute')
            ->get();

        foreach ($shifts as $shift) {
            $shiftStart = ((int) $shift->start_hour * 60) + (int) $shift->start_minute;
            $shiftEnd = ((int) $shift->end_hour * 60) + (int) $shift->end_minute;

            if ($shiftEnd <= $shiftStart) {
                continue;
            }

            for ($minute = $shiftStart; $minute + $durationMinutes <= $shiftEnd; $minute += self::SLOT_DURATION) {
                if ($this->isDoctorAvailableForPeriod($doctorId, $date, $this->minutesToTime($minute), $durationMinutes)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isDoctorAvailableForPeriod($doctorId, $date, string $startTime, int $durationMinutes): bool
    {
        $startMinutes = $this->timeToMinutes($startTime);
        $endMinutes = $startMinutes + $durationMinutes;

        if (!$this->isInsideApprovedShift($doctorId, $date, $startMinutes, $endMinutes)) {
            return false;
        }

        return !$this->hasAppointmentOverlap($doctorId, $date, $startMinutes, $endMinutes);
    }

    private function isInsideApprovedShift($doctorId, $date, int $startMinutes, int $endMinutes): bool
    {
        $shifts = ShiftAssignment::where('employee_id', $doctorId)
            ->whereDate('work_date', $date)
            ->where('status', 'approved')
            ->get();

        foreach ($shifts as $shift) {
            $shiftStart = ((int) $shift->start_hour * 60) + (int) $shift->start_minute;
            $shiftEnd = ((int) $shift->end_hour * 60) + (int) $shift->end_minute;

            if ($shiftStart <= $startMinutes && $shiftEnd >= $endMinutes) {
                return true;
            }
        }

        return false;
    }

    private function hasAppointmentOverlap($doctorId, $date, int $startMinutes, int $endMinutes): bool
    {
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
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