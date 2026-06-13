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
    private static array $tableColumnsCache = [];

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

        $this->syncDoctorAppointmentsToPatientProfiles($doctor);

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
        $this->syncDoctorAppointmentsToPatientProfiles($doctor);
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
            if (array_key_exists($column, $validated) && $this->hasColumn('patient_profiles', $column)) {
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

        $patientProfile = $this->ensureAppointmentPatientProfile($appointment);

        if (!$patientProfile) {
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

        if ($this->hasColumn('medical_records', 'patient_profile_id')) {
            $recordData['patient_profile_id'] = $patientProfile->id;
        }

        $record = MedicalRecord::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $recordData
        );

        if ($this->hasColumn('medical_records', 'clinical_findings')) {
            $record->forceFill([
                'clinical_findings' => $validated['clinical_findings'] ?? null,
            ])->save();
        }

        $patientProfile->markVisited($appointment->appointment_date ?: now());

        return redirect()
            ->route('doctor.patient-profiles.show', $patientProfile->id)
            ->with('success', 'Đã cập nhật hồ sơ bệnh án.');
    }

    public function doctorStoreClinicalImage(Request $request, Appointment $appointment)
    {
        $doctor = $this->currentDoctor();
        $this->authorizeDoctorAppointment($appointment, $doctor);

        $patientProfile = $this->ensureAppointmentPatientProfile($appointment);

        if (!$patientProfile) {
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
                'patient_profile_id' => $patientProfile->id,
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
                ->route('doctor.patient-profiles.show', $patientProfile->id)
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
                ['phone' => $validated['phone']],
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

    private function syncDoctorAppointmentsToPatientProfiles(Employee $doctor): void
    {
        Appointment::with(['patient', 'patientProfile', 'medicalRecord'])
            ->where('doctor_id', $doctor->id)
            ->where(function ($query) {
                $query->whereNull('patient_profile_id')
                    ->orWhereNotNull('patient_id')
                    ->orWhereNull('patient_snapshot')
                    ->orWhereHas('medicalRecord', function ($recordQuery) {
                        $recordQuery->whereNull('patient_profile_id');
                    });
            })
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->each(function (Appointment $appointment) {
                $this->ensureAppointmentPatientProfile($appointment);
            });
    }

    private function ensureAppointmentPatientProfile(Appointment $appointment): ?PatientProfile
    {
        $appointment->loadMissing(['patient', 'patientProfile', 'medicalRecord']);

        $profile = $this->findBestPatientProfileForAppointment($appointment);

        if (!$profile) {
            $profile = $this->createPatientProfileFromAppointment($appointment);
        } else {
            $this->fillPatientProfileFromAppointment($profile, $appointment);
        }

        if (!$profile) {
            return null;
        }

        $needsAppointmentUpdate = false;

        if ((int) $appointment->patient_profile_id !== (int) $profile->id) {
            $appointment->patient_profile_id = $profile->id;
            $needsAppointmentUpdate = true;
        }

        if (!$appointment->patient_snapshot) {
            $appointment->patient_snapshot = $profile->toAppointmentSnapshot();
            $needsAppointmentUpdate = true;
        }

        if ($needsAppointmentUpdate) {
            $appointment->save();
        }

        if (
            $this->hasColumn('medical_records', 'patient_profile_id')
            && $appointment->medicalRecord
            && (int) $appointment->medicalRecord->patient_profile_id !== (int) $profile->id
        ) {
            $appointment->medicalRecord->forceFill([
                'patient_profile_id' => $profile->id,
            ])->save();
        }

        return $profile;
    }

    private function findBestPatientProfileForAppointment(Appointment $appointment): ?PatientProfile
    {
        $snapshot = is_array($appointment->patient_snapshot) ? $appointment->patient_snapshot : [];

        if ($appointment->patient_id) {
            $profile = PatientProfile::where('user_id', $appointment->patient_id)->first();

            if ($profile) {
                return $profile;
            }
        }

        $identityNumber = $this->cleanValue(
            data_get($snapshot, 'identity_number')
            ?: $this->safeUserAttribute($appointment, 'identity_number')
            ?: $this->extractNoteValue($appointment->notes, 'CCCD')
        );

        if ($identityNumber) {
            $profile = PatientProfile::where('identity_number', $identityNumber)->first();

            if ($profile) {
                return $profile;
            }
        }

        $phone = $this->cleanPhone(
            data_get($snapshot, 'phone')
            ?: $this->safeUserAttribute($appointment, 'phone')
            ?: $this->safeUserAttribute($appointment, 'phone_number')
            ?: $this->safeUserAttribute($appointment, 'tel')
            ?: $this->extractNoteValue($appointment->notes, 'SĐT')
        );

        if ($phone) {
            $profile = PatientProfile::where('phone', $phone)
                ->where(function ($query) use ($appointment) {
                    if ($appointment->patient_id) {
                        $query->whereNull('user_id')
                            ->orWhere('user_id', $appointment->patient_id);
                    } else {
                        $query->whereNull('user_id');
                    }
                })
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        return $appointment->patientProfile;
    }

    private function createPatientProfileFromAppointment(Appointment $appointment): ?PatientProfile
    {
        $data = $this->buildPatientProfileDataFromAppointment($appointment);

        if (!$data['full_name'] && !$data['phone'] && !$data['user_id']) {
            return null;
        }

        return PatientProfile::create($this->filterPatientProfileColumns($data));
    }

    private function fillPatientProfileFromAppointment(PatientProfile $profile, Appointment $appointment): void
    {
        $data = $this->buildPatientProfileDataFromAppointment($appointment);
        $updateData = [];

        foreach ($data as $column => $value) {
            if (!$this->hasColumn('patient_profiles', $column)) {
                continue;
            }

            if ($column === 'user_id') {
                if (!$profile->user_id && $value) {
                    $updateData[$column] = $value;
                }

                continue;
            }

            if ($column === 'source') {
                if (!$profile->source && $value) {
                    $updateData[$column] = $value;
                }

                continue;
            }

            if ($column === 'is_temporary') {
                if ($profile->is_temporary && $value === false) {
                    $updateData[$column] = false;
                }

                continue;
            }

            if ($column === 'last_visit_at') {
                if (!$profile->last_visit_at || ($value && $value > $profile->last_visit_at)) {
                    $updateData[$column] = $value;
                }

                continue;
            }

            if (!$this->cleanValue($profile->{$column} ?? null) && $this->cleanValue($value)) {
                $updateData[$column] = $value;
            }
        }

        if ($updateData) {
            $profile->update($updateData);
        }
    }

    private function buildPatientProfileDataFromAppointment(Appointment $appointment): array
    {
        $appointment->loadMissing('patient');

        $snapshot = is_array($appointment->patient_snapshot) ? $appointment->patient_snapshot : [];
        $source = $appointment->source ?: data_get($snapshot, 'source') ?: 'online';

        $fullName = $this->cleanValue(
            data_get($snapshot, 'full_name')
            ?: $this->safeUserAttribute($appointment, 'name')
            ?: $this->extractNoteValue($appointment->notes, 'Họ tên')
        );

        $phone = $this->cleanPhone(
            data_get($snapshot, 'phone')
            ?: $this->safeUserAttribute($appointment, 'phone')
            ?: $this->safeUserAttribute($appointment, 'phone_number')
            ?: $this->safeUserAttribute($appointment, 'tel')
            ?: $this->extractNoteValue($appointment->notes, 'SĐT')
        );

        $email = $this->cleanValue(
            data_get($snapshot, 'email')
            ?: $this->safeUserAttribute($appointment, 'email')
        );

        $dob = $this->cleanValue(
            data_get($snapshot, 'dob')
            ?: $this->safeUserAttribute($appointment, 'dob')
            ?: $this->extractNoteValue($appointment->notes, 'Ngày sinh')
        );

        $gender = $this->normalizeGender(
            data_get($snapshot, 'gender')
            ?: $this->safeUserAttribute($appointment, 'gender')
            ?: $this->extractNoteValue($appointment->notes, 'Giới tính')
        );

        $address = $this->cleanValue(
            data_get($snapshot, 'address')
            ?: $this->safeUserAttribute($appointment, 'address')
            ?: $this->extractNoteValue($appointment->notes, 'Địa chỉ')
        );

        $identityNumber = $this->cleanValue(
            data_get($snapshot, 'identity_number')
            ?: $this->safeUserAttribute($appointment, 'identity_number')
            ?: $this->extractNoteValue($appointment->notes, 'CCCD')
        );

        if (!$fullName) {
            $fullName = 'Bệnh nhân #' . ($appointment->patient_id ?: $appointment->id);
        }

        return [
            'user_id' => $appointment->patient_id,
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'dob' => $dob,
            'gender' => $gender,
            'address' => $address,
            'identity_number' => $identityNumber,
            'emergency_contact_name' => $this->cleanValue(data_get($snapshot, 'emergency_contact_name')),
            'emergency_contact_phone' => $this->cleanPhone(data_get($snapshot, 'emergency_contact_phone')),
            'blood_type' => $this->cleanValue(data_get($snapshot, 'blood_type')),
            'occupation' => $this->cleanValue(data_get($snapshot, 'occupation')),
            'allergies' => $this->cleanValue(data_get($snapshot, 'allergies')),
            'medical_history' => $this->cleanValue(data_get($snapshot, 'medical_history')),
            'current_medications' => $this->cleanValue(data_get($snapshot, 'current_medications')),
            'dental_history' => $this->cleanValue(data_get($snapshot, 'dental_history')),
            'source' => $source,
            'is_temporary' => false,
            'last_visit_at' => $appointment->appointment_date ?: now(),
        ];
    }

    private function filterPatientProfileColumns(array $data): array
    {
        $filtered = [];

        foreach ($data as $column => $value) {
            if ($this->hasColumn('patient_profiles', $column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function safeUserAttribute(Appointment $appointment, string $attribute)
    {
        if (!$appointment->patient) {
            return null;
        }

        return $appointment->patient->getAttribute($attribute);
    }

    private function extractNoteValue(?string $notes, string $label): ?string
    {
        if (!$notes) {
            return null;
        }

        $pattern = '/'.preg_quote($label, '/').'\s*:\s*([^\n\r]+)/u';

        if (preg_match($pattern, $notes, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function cleanValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || in_array($value, ['Chưa có SĐT', 'Chưa cập nhật', 'Chưa có CCCD'], true)) {
            return null;
        }

        return $value;
    }

    private function cleanPhone($value): ?string
    {
        $value = $this->cleanValue($value);

        if (!$value) {
            return null;
        }

        return preg_replace('/\s+/', '', $value);
    }

    private function normalizeGender(?string $gender): ?string
    {
        $gender = trim((string) $gender);

        return match ($gender) {
            'Nam', 'nam', 'male' => 'male',
            'Nữ', 'nữ', 'nu', 'female' => 'female',
            'Khác', 'khác', 'khac', 'other' => 'other',
            default => null,
        };
    }

    private function hasColumn(string $table, string $column): bool
    {
        if (!array_key_exists($table, self::$tableColumnsCache)) {
            self::$tableColumnsCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($column, self::$tableColumnsCache[$table], true);
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