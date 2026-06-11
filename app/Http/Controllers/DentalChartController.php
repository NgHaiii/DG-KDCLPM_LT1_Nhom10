<?php

namespace App\Http\Controllers;

use App\Models\DentalChart;
use App\Models\DentalChartHistory;
use App\Models\Employee;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DentalChartController extends Controller
{
    private array $allowedTeeth = [
        '18', '17', '16', '15', '14', '13', '12', '11',
        '21', '22', '23', '24', '25', '26', '27', '28',
        '48', '47', '46', '45', '44', '43', '42', '41',
        '31', '32', '33', '34', '35', '36', '37', '38',
    ];

    private array $allowedStatuses = [
        'healthy',
        'caries',
        'filled',
        'crown',
        'root_canal',
        'missing',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(PatientProfile $patientProfile)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeProfile($patientProfile, $doctor);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Đã tải sơ đồ răng.',
        ], $this->buildChartResponse($patientProfile)));
    }

    public function store(Request $request, PatientProfile $patientProfile)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeProfile($patientProfile, $doctor);

        $request->merge([
            'type' => $request->input('type', 'tooth'),
            'tooth_number' => $request->filled('tooth_number')
                ? (string) $request->input('tooth_number')
                : null,
        ]);

        $request->validate([
            'type' => ['required', 'string', Rule::in(['tooth', 'quick_note'])],
        ]);

        if ($request->input('type') === 'quick_note') {
            return $this->storeQuickNote($request, $patientProfile, $doctor);
        }

        return $this->storeToothStatus($request, $patientProfile, $doctor);
    }

    private function storeToothStatus(Request $request, PatientProfile $patientProfile, Employee $doctor)
    {
        $validated = $request->validate([
            'tooth_number' => ['required', 'string', 'max:5', Rule::in($this->allowedTeeth)],
            'status' => ['required', 'string', Rule::in($this->allowedStatuses)],
            'note' => ['nullable', 'string', 'max:3000'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ], [
            'tooth_number.required' => 'Vui lòng chọn răng.',
            'tooth_number.in' => 'Số răng không hợp lệ theo chuẩn FDI.',
            'status.required' => 'Vui lòng chọn tình trạng răng.',
            'status.in' => 'Tình trạng răng không hợp lệ.',
        ]);

        $appointmentId = $this->resolveAppointmentId(
            $patientProfile,
            $doctor,
            $validated['appointment_id'] ?? null
        );

        DB::transaction(function () use ($validated, $patientProfile, $doctor, $appointmentId) {
            $existing = DentalChart::where('patient_profile_id', $patientProfile->id)
                ->where('tooth_number', $validated['tooth_number'])
                ->lockForUpdate()
                ->first();

            $oldStatus = $existing?->status;
            $oldNote = $existing?->note;

            $chart = DentalChart::updateOrCreate(
                [
                    'patient_profile_id' => $patientProfile->id,
                    'tooth_number' => $validated['tooth_number'],
                ],
                [
                    'appointment_id' => $appointmentId,
                    'doctor_id' => $doctor->id,
                    'status' => $validated['status'],
                    'note' => $validated['note'] ?? null,
                ]
            );

            DentalChartHistory::create([
                'patient_profile_id' => $patientProfile->id,
                'appointment_id' => $appointmentId,
                'doctor_id' => $doctor->id,
                'dental_chart_id' => $chart->id,
                'action_type' => 'update_tooth',
                'tooth_number' => $validated['tooth_number'],
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'old_note' => $oldNote,
                'new_note' => $validated['note'] ?? null,
            ]);
        });

        return response()->json(array_merge([
            'success' => true,
            'saved_to_database' => true,
            'message' => 'Đã lưu tình trạng răng vào hồ sơ.',
        ], $this->buildChartResponse($patientProfile)));
    }

    private function storeQuickNote(Request $request, PatientProfile $patientProfile, Employee $doctor)
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:3000'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ], [
            'note.required' => 'Vui lòng nhập ghi chú nhanh.',
        ]);

        $appointmentId = $this->resolveAppointmentId(
            $patientProfile,
            $doctor,
            $validated['appointment_id'] ?? null
        );

        DentalChartHistory::create([
            'patient_profile_id' => $patientProfile->id,
            'appointment_id' => $appointmentId,
            'doctor_id' => $doctor->id,
            'dental_chart_id' => null,
            'action_type' => 'quick_note',
            'tooth_number' => null,
            'old_status' => null,
            'new_status' => null,
            'old_note' => null,
            'new_note' => $validated['note'],
        ]);

        return response()->json(array_merge([
            'success' => true,
            'saved_to_database' => true,
            'message' => 'Đã lưu ghi chú nhanh vào hồ sơ.',
        ], $this->buildChartResponse($patientProfile)));
    }

    private function buildChartResponse(PatientProfile $patientProfile): array
    {
        $charts = DentalChart::where('patient_profile_id', $patientProfile->id)
            ->orderBy('tooth_number')
            ->get();

        $teeth = [];

        foreach ($charts as $chart) {
            $teeth[$chart->tooth_number] = [
                'id' => $chart->id,
                'tooth_number' => $chart->tooth_number,
                'status' => $chart->status,
                'status_label' => $chart->status_label,
                'note' => $chart->note,
                'updated_at' => optional($chart->updated_at)->toDateTimeString(),
            ];
        }

        $history = DentalChartHistory::where('patient_profile_id', $patientProfile->id)
            ->with('doctor')
            ->latest()
            ->limit(30)
            ->get()
            ->map(function (DentalChartHistory $item) {
                return [
                    'id' => $item->id,
                    'type' => $item->action_type,
                    'action_label' => $item->action_label,
                    'tooth' => $item->tooth_number ?: 'Ghi chú chung',
                    'status' => $item->new_status,
                    'status_label' => $item->new_status_label,
                    'note' => $item->new_note,
                    'doctor_name' => $item->doctor?->name,
                    'time' => optional($item->created_at)->format('d/m/Y H:i'),
                ];
            })
            ->values();

        return [
            'teeth' => $teeth,
            'history' => $history,
        ];
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

    private function authorizeProfile(PatientProfile $patientProfile, Employee $doctor): void
    {
        $hasAppointment = $patientProfile->appointments()
            ->where('doctor_id', $doctor->id)
            ->exists();

        if (!$hasAppointment) {
            abort(403, 'Bạn không có quyền cập nhật hồ sơ bệnh nhân này.');
        }
    }

    private function resolveAppointmentId(PatientProfile $patientProfile, Employee $doctor, ?int $appointmentId): ?int
    {
        if ($appointmentId) {
            $exists = $patientProfile->appointments()
                ->where('doctor_id', $doctor->id)
                ->where('id', $appointmentId)
                ->exists();

            return $exists ? $appointmentId : null;
        }

        return $patientProfile->appointments()
            ->where('doctor_id', $doctor->id)
            ->latest('appointment_date')
            ->value('id');
    }
}