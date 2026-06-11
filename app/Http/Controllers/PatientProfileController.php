<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalImage;
use App\Models\Employee;
use App\Models\MedicalRecord;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PatientProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));

        $profiles = PatientProfile::query()
            ->search($keyword)
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('employees.patient-profiles.index', compact('profiles', 'keyword'));
    }

    public function doctorIndex(Request $request)
    {
        $doctor = $this->currentDoctor();
        $keyword = trim((string) $request->input('keyword'));

        $profiles = PatientProfile::query()
            ->search($keyword)
            ->whereHas('appointments', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            })
            ->with([
                'appointments' => function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id)
                        ->with(['service', 'room', 'medicalRecord', 'clinicalImages'])
                        ->latest('appointment_date');
                },
            ])
            ->withCount([
                'appointments as total_visits_count' => function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id);
                },
                'appointments as completed_visits_count' => function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id)
                        ->where('status', 'completed');
                },
            ])
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $totalProfiles = PatientProfile::whereHas('appointments', function ($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id);
        })->count();

        $completedAppointmentsCount = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->count();

        return view('doctor.patient-profiles.index', compact(
            'doctor',
            'profiles',
            'keyword',
            'totalProfiles',
            'completedAppointmentsCount'
        ));
    }

    public function doctorShow(PatientProfile $patientProfile)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorPatientProfile($patientProfile, $doctor);

        $patientProfile->load([
            'appointments' => function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id)
                    ->with(['service', 'room', 'medicalRecord', 'doctor', 'clinicalImages'])
                    ->latest('appointment_date');
            },
        ]);

        return view('doctor.patient-profiles.show', compact('patientProfile', 'doctor'));
    }

    public function doctorUpdate(Request $request, PatientProfile $patientProfile)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorPatientProfile($patientProfile, $doctor);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:5000'],
            'medical_history' => ['nullable', 'string', 'max:5000'],
            'current_medications' => ['nullable', 'string', 'max:5000'],
            'dental_history' => ['nullable', 'string', 'max:5000'],
        ]);

        $allowedColumns = [
            'full_name',
            'phone',
            'email',
            'dob',
            'gender',
            'address',
            'identity_number',
            'emergency_contact_name',
            'emergency_contact_phone',
            'blood_type',
            'occupation',
            'allergies',
            'medical_history',
            'current_medications',
            'dental_history',
        ];

        $updateData = [];

        foreach ($allowedColumns as $column) {
            if (array_key_exists($column, $validated) && Schema::hasColumn('patient_profiles', $column)) {
                $updateData[$column] = $validated[$column];
            }
        }

        $patientProfile->update($updateData);

        return redirect()
            ->route('doctor.patient-profiles.show', $patientProfile->id)
            ->with('success', 'Đã cập nhật hồ sơ bệnh nhân.');
    }

    public function doctorUpdateMedicalRecord(Request $request, Appointment $appointment)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if (!$appointment->patient_profile_id && !$appointment->patient_id) {
            return back()->with('error', 'Lượt khám này chưa có hồ sơ bệnh nhân.');
        }

        $validated = $request->validate([
            'chief_complaint' => ['nullable', 'string', 'max:5000'],
            'clinical_findings' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'prescription' => ['nullable', 'string', 'max:5000'],
            'doctor_notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ], [
            'diagnosis.required' => 'Vui lòng nhập chẩn đoán.',
            'follow_up_date.after_or_equal' => 'Ngày tái khám không được nhỏ hơn ngày hiện tại.',
        ]);

        $recordData = [
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'service_id' => $appointment->service_id,
            'chief_complaint' => $validated['chief_complaint'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'treatment_plan' => $validated['treatment_plan'] ?? null,
            'prescription' => $validated['prescription'] ?? null,
            'doctor_notes' => $validated['doctor_notes'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ];

        if (Schema::hasColumn('medical_records', 'patient_profile_id')) {
            $recordData['patient_profile_id'] = $appointment->patient_profile_id;
        }

        if (Schema::hasColumn('medical_records', 'clinical_findings')) {
            $recordData['clinical_findings'] = $validated['clinical_findings'] ?? null;
        }

        MedicalRecord::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $recordData
        );

        return redirect()
            ->route('doctor.patient-profiles.show', $appointment->patient_profile_id)
            ->with('success', 'Đã cập nhật hồ sơ bệnh án.');
    }

    public function doctorStoreClinicalImage(Request $request, Appointment $appointment)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        if (!$appointment->patient_profile_id) {
            return back()->with('error', 'Lượt khám này chưa gắn hồ sơ bệnh nhân.');
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_type' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'taken_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'image.required' => 'Vui lòng chọn ảnh cần tải lên.',
            'image.image' => 'File tải lên phải là ảnh.',
            'image.mimes' => 'Ảnh phải có định dạng jpg, jpeg, png hoặc webp.',
            'image.max' => 'Ảnh không được vượt quá 10MB.',
            'taken_date.required' => 'Vui lòng chọn ngày chụp.',
        ]);

        try {
            $file = $request->file('image');
            $path = $file->store('clinical-images', 'public');

            ClinicalImage::create([
                'appointment_id' => $appointment->id,
                'patient_profile_id' => $appointment->patient_profile_id,
                'doctor_id' => $doctor->id,
                'image_type' => $validated['image_type'],
                'title' => $validated['title'] ?? null,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'taken_date' => $validated['taken_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()
                ->route('doctor.patient-profiles.show', $appointment->patient_profile_id)
                ->with('success', 'Đã tải ảnh X-quang/cận lâm sàng lên hồ sơ.');
        } catch (\Exception $e) {
            Log::error('Upload clinical image error: ' . $e->getMessage());

            return back()->with('error', 'Không thể tải ảnh lên. Vui lòng kiểm tra lại file ảnh.');
        }
    }

    public function doctorDestroyClinicalImage(ClinicalImage $clinicalImage)
    {
        $doctor = $this->currentDoctor();

        $clinicalImage->loadMissing('appointment');

        if (
            (int) $clinicalImage->doctor_id !== (int) $doctor->id
            && (int) optional($clinicalImage->appointment)->doctor_id !== (int) $doctor->id
        ) {
            abort(403, 'Bạn không có quyền xóa ảnh này.');
        }

        $patientProfileId = $clinicalImage->patient_profile_id;

        try {
            if ($clinicalImage->file_path && Storage::disk('public')->exists($clinicalImage->file_path)) {
                Storage::disk('public')->delete($clinicalImage->file_path);
            }

            $clinicalImage->delete();

            return redirect()
                ->route('doctor.patient-profiles.show', $patientProfileId)
                ->with('success', 'Đã xóa ảnh khỏi hồ sơ.');
        } catch (\Exception $e) {
            Log::error('Delete clinical image error: ' . $e->getMessage());

            return back()->with('error', 'Không thể xóa ảnh này.');
        }
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $profiles = PatientProfile::query()
            ->search($keyword)
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'full_name' => $profile->full_name,
                    'phone' => $profile->phone,
                    'email' => $profile->email,
                    'dob' => optional($profile->dob)->format('Y-m-d'),
                    'gender' => $profile->gender,
                    'gender_label' => $profile->gender_label,
                    'address' => $profile->address,
                    'source_label' => $profile->source_label,
                ];
            });

        return response()->json($profiles);
    }

    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên bệnh nhân.',
            'phone.required' => 'Vui lòng nhập số điện thoại bệnh nhân.',
            'email.email' => 'Email không đúng định dạng.',
        ]);

        try {
            $profile = PatientProfile::updateOrCreate(
                [
                    'phone' => $validated['phone'],
                ],
                [
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'] ?? null,
                    'dob' => $validated['dob'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'source' => 'offline',
                    'is_temporary' => false,
                    'last_visit_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu hồ sơ bệnh nhân.',
                'profile' => [
                    'id' => $profile->id,
                    'full_name' => $profile->full_name,
                    'phone' => $profile->phone,
                    'email' => $profile->email,
                    'dob' => optional($profile->dob)->format('Y-m-d'),
                    'gender' => $profile->gender,
                    'address' => $profile->address,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Store quick patient profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể lưu hồ sơ bệnh nhân.',
            ], 500);
        }
    }

    public function update(Request $request, PatientProfile $patientProfile)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $patientProfile->update($validated);

        return back()->with('success', 'Đã cập nhật hồ sơ bệnh nhân.');
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

    private function authorizeDoctorPatientProfile(PatientProfile $patientProfile, Employee $doctor): void
    {
        $hasAccess = $patientProfile->appointments()
            ->where('doctor_id', $doctor->id)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Bạn không có quyền xem hoặc chỉnh sửa hồ sơ bệnh nhân này.');
        }
    }

    private function authorizeDoctorAppointment(Appointment $appointment, Employee $doctor): void
    {
        if ((int) $appointment->doctor_id !== (int) $doctor->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa hồ sơ bệnh án của lượt khám này.');
        }
    }
}