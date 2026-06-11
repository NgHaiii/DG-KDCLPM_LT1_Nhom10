<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\Service;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
        $this->middleware('auth');
    }

    public function index()
    {
        $patientId = Auth::id();

        $appointments = Appointment::where('patient_id', $patientId)
            ->where('appointment_date', '>=', now())
            ->whereNotIn('status', ['cancelled', 'missed'])
            ->orderBy('appointment_date', 'asc')
            ->with(['doctor', 'service', 'room', 'patientProfile'])
            ->get();

        $pastAppointments = Appointment::where('patient_id', $patientId)
            ->where(function ($query) {
                $query->where('appointment_date', '<', now())
                    ->orWhereIn('status', ['completed', 'cancelled', 'missed']);
            })
            ->orderBy('appointment_date', 'desc')
            ->with(['doctor', 'service', 'room', 'patientProfile'])
            ->get();

        return view('patient.appointments.index', compact('appointments', 'pastAppointments'));
    }

    public function create()
    {
        try {
            $services = Service::where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();

            if ($services->isEmpty()) {
                return view('patient.appointments.create', [
                    'services' => $services,
                    'warning' => 'Hiện chưa có dịch vụ khám nào khả dụng',
                ]);
            }

            return view('patient.appointments.create', compact('services'));
        } catch (\Exception $e) {
            Log::error('Error in create appointment page: ' . $e->getMessage());

            return back()->with('error', 'Lỗi khi tải trang đặt lịch.');
        }
    }

    public function getServiceCategories()
    {
        try {
            $categories = Service::where('is_active', 1)
                ->whereNotNull('type')
                ->pluck('type')
                ->map(fn ($type) => trim($type))
                ->filter()
                ->unique()
                ->sort()
                ->values();

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            Log::error('Error in getServiceCategories: ' . $e->getMessage());

            return response()->json([
                'message' => 'Không thể tải loại dịch vụ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getServicesByCategory(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|string|max:255',
            ], [
                'category.required' => 'Vui lòng chọn loại dịch vụ',
            ]);

            $services = Service::where('type', $validated['category'])
                ->where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get([
                    'id',
                    'name',
                    'type',
                    'required_specialization',
                    'slots_required',
                    'duration_minutes',
                    'actual_duration',
                ]);

            return response()->json($services, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getServicesByCategory: ' . json_encode($e->errors()));

            return response()->json([
                'message' => 'Dữ liệu loại dịch vụ không hợp lệ.',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getServicesByCategory: ' . $e->getMessage());

            return response()->json([
                'message' => 'Không thể tải danh sách dịch vụ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDoctorsByService(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date_format:Y-m-d|after:today',
            ], [
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'service_id.exists' => 'Dịch vụ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after' => 'Lịch online cần được đặt trước ít nhất 1 ngày',
            ]);

            $doctors = $this->appointmentService->getAvailableDoctorsByService(
                $validated['service_id'],
                $validated['date']
            );

            $response = $doctors->map(function ($item) {
                return [
                    'id' => $item['doctor']->id,
                    'name' => $item['doctor']->name,
                    'specialization' => $item['doctor']->specialization ?? 'N/A',
                    'phone' => $item['doctor']->phone ?? '',
                    'email' => $item['doctor']->email ?? '',
                    'available_slots_count' => count($item['available_slots'] ?? []),
                    'slots_needed' => $item['slots_needed'] ?? 1,
                    'load_minutes' => $item['load_minutes'] ?? 0,
                    'appointment_count' => $item['appointment_count'] ?? 0,
                ];
            });

            return response()->json($response->values()->all(), 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getDoctorsByService: ' . json_encode($e->errors()));

            return response()->json([
                'message' => 'Dữ liệu tìm bác sĩ không hợp lệ.',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDoctorsByService: ' . $e->getMessage());

            return response()->json([
                'message' => 'Không thể tải danh sách bác sĩ theo dịch vụ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableSlots(Request $request)
    {
        try {
            $validated = $request->validate([
                'doctor_id' => 'required|exists:employees,id',
                'date' => 'required|date_format:Y-m-d|after:today',
            ], [
                'doctor_id.required' => 'Vui lòng chọn bác sĩ',
                'doctor_id.exists' => 'Bác sĩ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after' => 'Lịch online cần được đặt trước ít nhất 1 ngày',
            ]);

            $slots = $this->appointmentService->getAvailableSlotsForDoctor(
                $validated['doctor_id'],
                $validated['date']
            );

            return response()->json($slots, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getAvailableSlots: ' . json_encode($e->errors()));

            return response()->json([
                'message' => 'Dữ liệu tải khung giờ không hợp lệ.',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableSlots: ' . $e->getMessage());

            return response()->json([
                'message' => 'Không thể tải khung giờ của bác sĩ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableTimes(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date_format:Y-m-d|after:today',
            ], [
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'service_id.exists' => 'Dịch vụ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after' => 'Lịch online cần được đặt trước ít nhất 1 ngày',
            ]);

            $service = Service::findOrFail($validated['service_id']);

            if (!$service->required_specialization) {
                return response()->json([
                    'message' => 'Dịch vụ này chưa được gán chuyên khoa nên chưa thể tìm khung giờ.',
                    'error' => 'missing_required_specialization',
                ], 422);
            }

            $times = $this->appointmentService->getAvailableTimesByService(
                $validated['service_id'],
                $validated['date']
            );

            return response()->json($times, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getAvailableTimes: ' . json_encode($e->errors()));

            return response()->json([
                'message' => 'Dữ liệu tải khung giờ không hợp lệ.',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableTimes: ' . $e->getMessage(), [
                'service_id' => $request->input('service_id'),
                'date' => $request->input('date'),
            ]);

            return response()->json([
                'message' => 'Không thể tải khung giờ. Vui lòng kiểm tra lịch làm việc bác sĩ, chuyên khoa dịch vụ và dữ liệu ca làm.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDoctorsByTime(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date_format:Y-m-d|after:today',
                'start_time' => 'required|date_format:H:i',
            ], [
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'service_id.exists' => 'Dịch vụ không tồn tại',
                'date.required' => 'Vui lòng chọn ngày',
                'date.date_format' => 'Định dạng ngày không hợp lệ',
                'date.after' => 'Lịch online cần được đặt trước ít nhất 1 ngày',
                'start_time.required' => 'Vui lòng chọn thời gian',
                'start_time.date_format' => 'Định dạng thời gian không hợp lệ',
            ]);

            $service = Service::findOrFail($validated['service_id']);

            if (!$service->required_specialization) {
                return response()->json([
                    'message' => 'Dịch vụ này chưa được gán chuyên khoa nên chưa thể tìm bác sĩ.',
                    'error' => 'missing_required_specialization',
                ], 422);
            }

            $doctors = $this->appointmentService->getAvailableDoctorsByServiceAndTime(
                $validated['service_id'],
                $validated['date'],
                $validated['start_time']
            );

            return response()->json($doctors, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in getDoctorsByTime: ' . json_encode($e->errors()));

            return response()->json([
                'message' => 'Dữ liệu tìm bác sĩ theo giờ không hợp lệ.',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getDoctorsByTime: ' . $e->getMessage(), [
                'service_id' => $request->input('service_id'),
                'date' => $request->input('date'),
                'start_time' => $request->input('start_time'),
            ]);

            return response()->json([
                'message' => 'Không thể tải bác sĩ theo khung giờ. Vui lòng kiểm tra lịch làm việc và lịch hẹn hiện có.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_name' => 'required|string|max:100',
                'patient_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
                'patient_email' => 'nullable|email|max:255',
                'patient_dob' => 'nullable|date|before_or_equal:today',
                'patient_gender' => 'nullable|in:Nam,Nữ,Khác,male,female,other',
                'patient_address' => 'nullable|string|max:255',
                'identity_number' => 'nullable|string|max:50',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],

                'doctor_id' => 'required|exists:employees,id',
                'service_id' => 'required|exists:services,id',
                'appointment_date' => 'required|date_format:Y-m-d H:i|after:today',
                'notes' => 'nullable|string|max:500',
            ], [
                'patient_name.required' => 'Vui lòng nhập họ và tên',
                'patient_phone.required' => 'Vui lòng nhập số điện thoại',
                'patient_phone.regex' => 'Số điện thoại không hợp lệ',
                'patient_email.email' => 'Email không hợp lệ',
                'patient_dob.before_or_equal' => 'Ngày sinh không hợp lệ',
                'patient_gender.in' => 'Giới tính không hợp lệ',
                'emergency_phone.regex' => 'Số điện thoại người thân không hợp lệ',

                'doctor_id.required' => 'Vui lòng chọn bác sĩ',
                'doctor_id.exists' => 'Bác sĩ không tồn tại',
                'service_id.required' => 'Vui lòng chọn dịch vụ',
                'service_id.exists' => 'Dịch vụ không tồn tại',
                'appointment_date.required' => 'Vui lòng chọn ngày giờ',
                'appointment_date.date_format' => 'Định dạng ngày giờ không hợp lệ',
                'appointment_date.after' => 'Lịch online cần được đặt trước ít nhất 1 ngày',
            ]);

            $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $validated['appointment_date']);

            if ($appointmentDateTime->lessThanOrEqualTo(now()->endOfDay())) {
                return back()
                    ->withInput()
                    ->with('error', 'Lịch online cần được đặt trước ít nhất 1 ngày. Vui lòng chọn ngày từ ngày mai trở đi.');
            }

            $patientProfile = $this->createOrUpdatePatientProfile($validated);
            $notes = $this->buildAppointmentNotes($validated);

            $appointment = $this->appointmentService->createAppointment([
                'patient_id' => Auth::id(),
                'patient_profile_id' => $patientProfile->id,
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'appointment_date' => $validated['appointment_date'],
                'source' => 'online',
                'patient_snapshot' => $patientProfile->toAppointmentSnapshot(),
                'notes' => $notes,
            ]);

            $patientProfile->markVisited($appointmentDateTime);

            Log::info('Appointment created successfully', [
                'appointment_id' => $appointment->id,
                'patient_id' => Auth::id(),
                'patient_profile_id' => $patientProfile->id,
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'appointment_date' => $validated['appointment_date'],
            ]);

            return redirect()->route('patient.appointment.list')
                ->with('success', 'Lịch hẹn đã được tạo thành công. Vui lòng chờ bác sĩ xác nhận và xếp phòng khám.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error in appointment store: ' . json_encode($e->errors()));

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Dữ liệu không hợp lệ');
        } catch (\Exception $e) {
            Log::error('Error in appointment store: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $appointment = Appointment::with(['doctor', 'service', 'room', 'patientProfile'])->findOrFail($id);

            if ((int) $appointment->patient_id !== (int) Auth::id()) {
                Log::warning('Unauthorized access attempt for appointment ' . $id . ' by user ' . Auth::id());
                abort(403, 'Không có quyền xem lịch hẹn này');
            }

            return view('patient.appointments.show', compact('appointment'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Appointment not found: ' . $id);

            return back()->with('error', 'Lịch hẹn không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error in appointment show: ' . $e->getMessage());

            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if ((int) $appointment->patient_id !== (int) Auth::id()) {
                Log::warning('Unauthorized cancel attempt for appointment ' . $id . ' by user ' . Auth::id());
                abort(403, 'Không có quyền hủy lịch hẹn này');
            }

            if ($appointment->status === 'cancelled') {
                return back()->with('warning', 'Lịch hẹn này đã được hủy trước đó');
            }

            if (in_array($appointment->status, ['checked_in', 'waiting', 'in_progress', 'completed'], true)) {
                return back()->with('error', 'Không thể hủy lịch hẹn đã tiếp nhận hoặc đã hoàn thành');
            }

            $this->appointmentService->cancelAppointment($id);

            Log::info('Appointment cancelled by patient', [
                'appointment_id' => $id,
                'patient_id' => Auth::id(),
            ]);

            return back()->with('success', 'Lịch hẹn đã được hủy thành công.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Appointment not found for cancel: ' . $id);

            return back()->with('error', 'Lịch hẹn không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error in appointment cancel: ' . $e->getMessage());

            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    private function createOrUpdatePatientProfile(array $validated): PatientProfile
    {
        $gender = $this->normalizeGender($validated['patient_gender'] ?? null);

        return PatientProfile::updateOrCreate(
            [
                'phone' => trim($validated['patient_phone']),
            ],
            [
                'user_id' => Auth::id(),
                'full_name' => trim($validated['patient_name']),
                'email' => $validated['patient_email'] ?? Auth::user()?->email,
                'dob' => $validated['patient_dob'] ?? null,
                'gender' => $gender,
                'address' => $validated['patient_address'] ?? null,
                'identity_number' => $validated['identity_number'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_phone'] ?? null,
                'source' => 'online',
                'is_temporary' => false,
                'last_visit_at' => now(),
            ]
        );
    }

    private function normalizeGender(?string $gender): ?string
    {
        return match ($gender) {
            'Nam', 'male' => 'male',
            'Nữ', 'female' => 'female',
            'Khác', 'other' => 'other',
            default => null,
        };
    }

    private function buildAppointmentNotes(array $validated): string
    {
        $lines = [
            'THÔNG TIN BỆNH NHÂN',
            'Họ tên: ' . $validated['patient_name'],
            'SĐT: ' . $validated['patient_phone'],
        ];

        if (!empty($validated['patient_email'])) {
            $lines[] = 'Email: ' . $validated['patient_email'];
        }

        if (!empty($validated['patient_dob'])) {
            $lines[] = 'Ngày sinh: ' . $validated['patient_dob'];
        }

        if (!empty($validated['patient_gender'])) {
            $lines[] = 'Giới tính: ' . $validated['patient_gender'];
        }

        if (!empty($validated['identity_number'])) {
            $lines[] = 'CCCD/Mã định danh: ' . $validated['identity_number'];
        }

        if (!empty($validated['patient_address'])) {
            $lines[] = 'Địa chỉ: ' . $validated['patient_address'];
        }

        if (!empty($validated['emergency_contact_name'])) {
            $lines[] = 'Người liên hệ khẩn cấp: ' . $validated['emergency_contact_name'];
        }

        if (!empty($validated['emergency_phone'])) {
            $lines[] = 'SĐT người thân: ' . $validated['emergency_phone'];
        }

        if (!empty($validated['notes'])) {
            $lines[] = '';
            $lines[] = 'TRIỆU CHỨNG / GHI CHÚ';
            $lines[] = $validated['notes'];
        }

        return trim(implode("\n", $lines));
    }
}