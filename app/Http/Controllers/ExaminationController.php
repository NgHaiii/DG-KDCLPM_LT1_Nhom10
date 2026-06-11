<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExaminationController extends Controller
{
    public function index()
    {
        $doctor = $this->currentDoctor();
        $today = now()->toDateString();

        $inProgress = Appointment::with([
                'patient',
                'patientProfile',
                'service',
                'room',
                'medicalRecord',
            ])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'in_progress')
            ->orderBy('started_at')
            ->get();

        $waitingAppointments = Appointment::with([
                'patient',
                'patientProfile',
                'service',
                'room',
            ])
            ->where('doctor_id', $doctor->id)
            ->where(function ($query) use ($today) {
                $query->whereDate('checked_in_at', $today)
                    ->orWhereDate('appointment_date', $today);
            })
            ->whereIn('status', ['checked_in', 'waiting'])
            ->orderByRaw("CASE WHEN source = 'online' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN queue_number IS NULL THEN 1 ELSE 0 END")
            ->orderBy('queue_number')
            ->orderBy('appointment_date')
            ->get();

        $completedToday = Appointment::with([
                'patient',
                'patientProfile',
                'service',
                'room',
                'medicalRecord',
            ])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->where(function ($query) use ($today) {
                $query->whereDate('completed_at', $today)
                    ->orWhereDate('appointment_date', $today);
            })
            ->orderByDesc('completed_at')
            ->get();

        return view('doctor.examinations.index', compact(
            'inProgress',
            'waitingAppointments',
            'completedToday'
        ));
    }

    public function start(Appointment $appointment)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if (!in_array($appointment->status, ['checked_in', 'waiting'], true)) {
            return back()->with('error', 'Chỉ có thể bắt đầu khám khi bệnh nhân đã được tiếp nhận.');
        }

        if (!$appointment->checked_in_at) {
            return back()->with('error', 'Bệnh nhân chưa được tiếp nhận tại quầy.');
        }

        $hasAnotherInProgress = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'in_progress')
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($hasAnotherInProgress) {
            return back()->with('error', 'Bạn đang có một ca khám chưa hoàn thành.');
        }

        $appointment->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'estimated_end_at' => now()->addMinutes((int) ($appointment->duration_minutes ?? 30)),
        ]);

        return redirect()
            ->route('doctor.examinations.show', $appointment->id)
            ->with('success', 'Đã bắt đầu ca khám.');
    }

    public function show(Appointment $appointment)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        $appointment->load([
            'patient',
            'patientProfile',
            'service',
            'room',
            'medicalRecord',
        ]);

        return view('doctor.examinations.show', compact('appointment'));
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if ($appointment->status !== 'in_progress') {
            return back()->with('error', 'Chỉ có thể hoàn thành ca đang khám.');
        }

        $appointment->loadMissing(['patientProfile']);

        if (!$appointment->patient_id && !$appointment->patient_profile_id) {
            return back()->with('error', 'Ca khám này chưa có hồ sơ bệnh nhân, không thể tạo bệnh án.');
        }

        $validated = $request->validate([
            'chief_complaint' => 'nullable|string|max:1000',
            'diagnosis' => 'required|string|max:2000',
            'treatment_plan' => 'nullable|string|max:2000',
            'prescription' => 'nullable|string|max:2000',
            'doctor_notes' => 'nullable|string|max:2000',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
        ], [
            'diagnosis.required' => 'Vui lòng nhập chẩn đoán.',
        ]);

        DB::transaction(function () use ($appointment, $validated) {
            MedicalRecord::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id' => $appointment->patient_id,
                    'patient_profile_id' => $appointment->patient_profile_id,
                    'doctor_id' => $appointment->doctor_id,
                    'service_id' => $appointment->service_id,
                    'chief_complaint' => $validated['chief_complaint'] ?? null,
                    'diagnosis' => $validated['diagnosis'],
                    'treatment_plan' => $validated['treatment_plan'] ?? null,
                    'prescription' => $validated['prescription'] ?? null,
                    'doctor_notes' => $validated['doctor_notes'] ?? null,
                    'follow_up_date' => $validated['follow_up_date'] ?? null,
                ]
            );

            $actualMinutes = $appointment->started_at
                ? max(1, $appointment->started_at->diffInMinutes(now()))
                : null;

            $appointment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'actual_used_minutes' => $actualMinutes,
            ]);

            if ($appointment->patientProfile) {
                $appointment->patientProfile->update([
                    'last_visit_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('doctor.examinations.index')
            ->with('success', 'Đã hoàn thành ca khám và cập nhật hồ sơ bệnh án.');
    }

    private function currentDoctor(): Employee
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
            abort(403, 'Bạn không có quyền xử lý ca khám này.');
        }
    }
}