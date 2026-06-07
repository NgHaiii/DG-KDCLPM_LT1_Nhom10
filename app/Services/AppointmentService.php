<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentService
{
    /**
     * ✅ Lấy danh sách bác sĩ có chuyên khoa + dịch vụ cần thiết
     * 
     * @param int $serviceId - ID dịch vụ
     * @param string $date - Ngày cần khám (Y-m-d)
     * @return Collection - Danh sách bác sĩ
     */
    public function getAvailableDoctorsByService($serviceId, $date)
    {
        $service = Service::findOrFail($serviceId);
        $serviceType = $service->type; // ví dụ: "Khám tổng quát", "Khám định kỳ"

        // Lấy bác sĩ có chuyên khoa phù hợp
        $doctors = Employee::where('is_doctor', 1)
            ->where('status', 'active')
            ->where('specialization', 'LIKE', "%{$serviceType}%")
            ->get();

        $availableDoctors = [];

        foreach ($doctors as $doctor) {
            // Kiểm tra bác sĩ có ca trực vào ngày này không
            if ($this->hasDoctorShiftOnDate($doctor->id, $date)) {
                // Kiểm tra slot trống
                $availableSlots = $this->getAvailableSlotsForDoctor($doctor->id, $date);
                
                if (count($availableSlots) >= $service->slots_required) {
                    $availableDoctors[] = [
                        'doctor' => $doctor,
                        'available_slots' => $availableSlots,
                        'slots_needed' => $service->slots_required,
                    ];
                }
            }
        }

        return collect($availableDoctors);
    }

    /**
     * ✅ Kiểm tra bác sĩ có ca trực vào ngày cụ thể không
     * 
     * @param int $doctorId - ID bác sĩ
     * @param string $date - Ngày cần kiểm tra (Y-m-d)
     * @return bool
     */
    public function hasDoctorShiftOnDate($doctorId, $date)
    {
        return ShiftAssignment::where('employee_id', $doctorId)
            ->where('work_date', $date)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * ✅ Lấy tất cả slot trống của bác sĩ trong ngày
     * Mỗi slot = 30 phút
     * 
     * @param int $doctorId - ID bác sĩ
     * @param string $date - Ngày cần kiểm tra (Y-m-d)
     * @return array - Mảng các slot trống [[start_time, end_time], ...]
     */
    public function getAvailableSlotsForDoctor($doctorId, $date)
    {
        $SLOT_DURATION = 30; // 30 phút/slot

        // Lấy ca trực của bác sĩ
        $shift = ShiftAssignment::where('employee_id', $doctorId)
            ->where('work_date', $date)
            ->where('status', 'approved')
            ->first();

        if (!$shift) {
            return [];
        }

        // Chuyển đổi giờ làm việc thành phút
        $workStart = $shift->start_hour * 60 + $shift->start_minute;
        $workEnd = $shift->end_hour * 60 + $shift->end_minute;

        // Tạo danh sách tất cả slot trong ngày làm việc
        $allSlots = [];
        for ($time = $workStart; $time < $workEnd; $time += $SLOT_DURATION) {
            $allSlots[] = $time;
        }

        // Lấy tất cả lịch hẹn đã xác nhận của bác sĩ trong ngày
        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'confirmed')
            ->whereDate('appointment_date', $date)
            ->get();

        // Tạo bộ slot bị chiếm
        $bookedSlots = new \SplFixedArray(count($allSlots));
        foreach ($bookedSlots as $key => $value) {
            $bookedSlots[$key] = false;
        }

        foreach ($bookedAppointments as $appointment) {
            $appointmentStart = $appointment->appointment_date->hour * 60 + $appointment->appointment_date->minute;
            $duration = $appointment->duration_minutes;

            // Đánh dấu các slot bị chiếm
            for ($i = 0; $i < count($allSlots); $i++) {
                $slotTime = $allSlots[$i];
                if ($slotTime >= $appointmentStart && $slotTime < $appointmentStart + $duration) {
                    $bookedSlots[$i] = true;
                }
            }
        }

        // Lấy danh sách slot trống
        $availableSlots = [];
        for ($i = 0; $i < count($allSlots); $i++) {
            if (!$bookedSlots[$i]) {
                $minutes = $allSlots[$i];
                $hours = intdiv($minutes, 60);
                $mins = $minutes % 60;
                $startTime = str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
                
                $endMinutes = $minutes + $SLOT_DURATION;
                $endHours = intdiv($endMinutes, 60);
                $endMins = $endMinutes % 60;
                $endTime = str_pad($endHours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($endMins, 2, '0', STR_PAD_LEFT);
                
                $availableSlots[] = [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'slot_index' => $i,
                ];
            }
        }

        return $availableSlots;
    }

    /**
     * ✅ Kiểm tra bác sĩ có đủ slot liên tục không
     * 
     * @param int $doctorId - ID bác sĩ
     * @param string $date - Ngày khám (Y-m-d)
     * @param string $startTime - Giờ bắt đầu (H:i)
     * @param int $slotsNeeded - Số slot cần (ví dụ: 2)
     * @return bool
     */
    public function checkDoctorAvailability($doctorId, $date, $startTime, $slotsNeeded)
    {
        $availableSlots = $this->getAvailableSlotsForDoctor($doctorId, $date);

        // Tìm slot bắt đầu
        $startSlotIndex = null;
        foreach ($availableSlots as $index => $slot) {
            if ($slot['start_time'] === $startTime) {
                $startSlotIndex = $index;
                break;
            }
        }

        if ($startSlotIndex === null) {
            return false;
        }

        // Kiểm tra có đủ slot liên tục không
        if ($startSlotIndex + $slotsNeeded > count($availableSlots)) {
            return false;
        }

        // Kiểm tra các slot liên tiếp có liên tục không
        for ($i = $startSlotIndex; $i < $startSlotIndex + $slotsNeeded; $i++) {
            if ($i >= count($availableSlots)) {
                return false;
            }
        }

        return true;
    }

    /**
     * ✅ Tạo lịch hẹn mới
     * 
     * @param array $data - ['patient_id', 'doctor_id', 'service_id', 'appointment_date', 'notes']
     * @return Appointment|null
     */
    public function createAppointment($data)
    {
        $service = Service::find($data['service_id']);
        if (!$service) {
            throw new \Exception('Dịch vụ không tồn tại');
        }

        // Kiểm tra bác sĩ có slot trống không
        $date = Carbon::parse($data['appointment_date'])->format('Y-m-d');
        $time = Carbon::parse($data['appointment_date'])->format('H:i');

        if (!$this->checkDoctorAvailability($data['doctor_id'], $date, $time, $service->slots_required)) {
            throw new \Exception('Bác sĩ không rảnh trong khoảng thời gian này');
        }

        // Tạo lịch hẹn
        $appointment = Appointment::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'service_id' => $data['service_id'],
            'appointment_date' => $data['appointment_date'],
            'slots_used' => $service->slots_required,
            'duration_minutes' => $service->actual_duration ?? ($service->slots_required * 30),
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        return $appointment;
    }

    /**
     * ✅ Lấy lịch hẹn sắp tới của bệnh nhân
     * 
     * @param int $patientId - ID bệnh nhân
     * @return Collection
     */
    public function getUpcomingAppointments($patientId)
    {
        return Appointment::where('patient_id', $patientId)
            ->where('status', '!=', 'cancelled')
            ->where('appointment_date', '>=', now())
            ->orderBy('appointment_date', 'asc')
            ->with('doctor', 'service')
            ->get();
    }

    /**
     * ✅ Hủy lịch hẹn
     * 
     * @param int $appointmentId - ID lịch hẹn
     * @return bool
     */
    public function cancelAppointment($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        
        // Chỉ có thể hủy nếu chưa quá 24 tiếng trước lịch
        if ($appointment->appointment_date->diffInHours(now()) < 24) {
            throw new \Exception('Không thể hủy lịch hẹn ít hơn 24 tiếng trước');
        }

        $appointment->update(['status' => 'cancelled']);
        return true;
    }
}